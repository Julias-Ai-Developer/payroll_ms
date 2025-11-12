<?php
// Lightweight helper for computing and applying deductions.
// Encapsulates retrieval of deduction types, custom employee deductions, and computation logic.

class DeductionHelper {
    public static function getDeductionTypes(mysqli $conn, int $business_id): array {
        $types = [];
        $stmt = $conn->prepare("SELECT id, name, code, method, amount, percent, employer_percent, brackets, statutory, enabled FROM deduction_types WHERE business_id = ? AND enabled = 1");
        $stmt->bind_param("i", $business_id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $types[] = $row;
            }
        }
        $stmt->close();
        return $types;
    }

    public static function getEmployeeCustom(mysqli $conn, int $business_id, int $employee_id): array {
        $items = [];
        $stmt = $conn->prepare("SELECT id, deduction_type_id, custom_amount, custom_percent, balance, active, start_date, end_date FROM employee_deductions WHERE business_id = ? AND employee_id = ? AND active = 1");
        $stmt->bind_param("ii", $business_id, $employee_id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
        }
        $stmt->close();
        return $items;
    }

    // Progressive tax calculation given JSON brackets.
    // Expected format: [{"upto": 235000, "rate": 0}, {"upto": 335000, "rate": 0.10}, {"upto": 1000000000, "rate": 0.30}]
    public static function computeBracketTax(float $gross, string $bracketsJson): float {
        $tax = 0.0;
        $brackets = json_decode($bracketsJson, true);
        if (!is_array($brackets)) {
            return 0.0;
        }
        $prevCap = 0.0;
        foreach ($brackets as $b) {
            $cap = isset($b['upto']) ? floatval($b['upto']) : $prevCap;
            $rate = isset($b['rate']) ? floatval($b['rate']) : 0.0;
            if ($gross <= $prevCap) {
                break;
            }
            $taxable = min($gross, $cap) - $prevCap;
            if ($taxable > 0) {
                $tax += $taxable * $rate;
            }
            $prevCap = $cap;
        }
        return round($tax, 2);
    }

    public static function computeApplicable(
        mysqli $conn,
        int $business_id,
        int $employee_id,
        float $gross
    ): array {
        $types = self::getDeductionTypes($conn, $business_id);
        $customs = self::getEmployeeCustom($conn, $business_id, $employee_id);

        // Index customs by type id for quick lookup
        $customByType = [];
        foreach ($customs as $c) {
            $customByType[$c['deduction_type_id']] = $c;
        }

        $items = [];
        foreach ($types as $t) {
            $typeId = intval($t['id']);
            $code = strtoupper(trim($t['code']));
            $method = $t['method'];
            $employeeAmount = 0.0;
            $employerAmount = 0.0;
            $meta = [];

            $override = isset($customByType[$typeId]) ? $customByType[$typeId] : null;
            $useAmount = $override && $override['custom_amount'] !== null ? floatval($override['custom_amount']) : floatval($t['amount']);
            $usePercent = $override && $override['custom_percent'] !== null ? floatval($override['custom_percent']) : floatval($t['percent']);

            if ($method === 'fixed') {
                $employeeAmount = $useAmount;
                $meta['basis'] = 'fixed';
            } elseif ($method === 'percent') {
                $employeeAmount = ($usePercent / 100.0) * $gross;
                $meta['basis'] = 'percent';
                $meta['rate'] = $usePercent;
            } elseif ($method === 'bracket') {
                $employeeAmount = self::computeBracketTax($gross, (string)$t['brackets']);
                $meta['basis'] = 'bracket';
            }

            // Special handling for NSSF: split employee & employer portions
            if ($code === 'NSSF') {
                $employerPercent = floatval($t['employer_percent']);
                $employerAmount = ($employerPercent / 100.0) * $gross;
                $meta['employer_percent'] = $employerPercent;
            }

            // Loans: cap by remaining balance and update balance
            if ($code === 'LOAN' && $override && $override['balance'] !== null) {
                $remaining = floatval($override['balance']);
                $apply = min($employeeAmount, $remaining);
                $employeeAmount = $apply;
                $meta['remaining_before'] = $remaining;
                $meta['applied'] = $apply;
                $meta['remaining_after'] = max(0, $remaining - $apply);
            }

            // Round to 2 decimals
            $employeeAmount = round($employeeAmount, 2);
            $employerAmount = round($employerAmount, 2);

            if ($employeeAmount <= 0 && $employerAmount <= 0) {
                continue;
            }

            $items[] = [
                'deduction_type_id' => $typeId,
                'name' => $t['name'],
                'code' => $code,
                'method' => $method,
                'employee_amount' => $employeeAmount,
                'employer_amount' => $employerAmount,
                'meta' => $meta,
                'override' => $override,
            ];
        }

        return $items;
    }

    public static function record(
        mysqli $conn,
        int $business_id,
        int $payroll_id,
        int $employee_id,
        array $items
    ): void {
        $stmt = $conn->prepare("INSERT INTO payroll_deductions (business_id, payroll_id, employee_id, deduction_type_id, method, amount_applied, employer_amount, meta) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($items as $it) {
            $metaJson = json_encode($it['meta']);
            $typeId = intval($it['deduction_type_id']);
            $method = $it['method'];
            $empAmt = floatval($it['employee_amount']);
            $emprAmt = floatval($it['employer_amount']);
            $stmt->bind_param("iiiisdds", $business_id, $payroll_id, $employee_id, $typeId, $method, $empAmt, $emprAmt, $metaJson);
            $stmt->execute();

            // Update loan balance if applicable
            if ($it['code'] === 'LOAN' && isset($it['override']) && $it['override']) {
                $edId = intval($it['override']['id']);
                $newBal = max(0, floatval($it['override']['balance']) - $empAmt);
                $up = $conn->prepare("UPDATE employee_deductions SET balance = ? WHERE id = ?");
                $up->bind_param("di", $newBal, $edId);
                $up->execute();
                $up->close();
            }
        }
        $stmt->close();
    }
}

?>