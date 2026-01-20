<?php
require_once 'statutory_tables.php';

class ContributionCalculator
{

    /**
     * Calculate EPF (KWSP) based on Third Schedule (Part A & Part C) - Oct 2025
     * * @param float $salary Monthly gross wage
     * @param string $citizenship 'citizen' or 'non_citizen'
     * @param int $age Employee age
     * @return array ['employee' => float, 'employer' => float]
     */
    public static function calculateEPF($salary, $citizenship = 'citizen', $age = 30)
    {
        $share_employee = 0;
        $share_employer = 0;

        // Round salary to nearest whole Ringgit for lookup (standard practice for "wages up to X") 
        // Note: EPF act usually specifies ranges. "Exceeding X but not exceeding Y".

        if ($citizenship === 'non_citizen') {
            // Part F: Non-Citizens (Registered)
            // Employer share is flat RM 5.00, Employee share is 11%
            $share_employer = 5.00;
            $share_employee = ceil($salary * 0.11);
            return ['employee' => $share_employee, 'employer' => $share_employer];
        }

        // Logic for Citizens (Part A < 60 years, Part C >= 60 years)
        // Note: This is a simplified logic. Strictly, Part A is "not attained 60 years".
        $is_senior = ($age >= 60);

        // If Salary > RM 20,000, use formula
        if ($salary > 20000.00) {
            if ($is_senior) {
                // Part E (Citizens >= 60) Formula
                // Employee 11%, Employer 12%
                $share_employee = ceil($salary * 0.11);
                $share_employer = ceil($salary * 0.12);
            } else {
                // Part A (Standard) Formula > 20k
                // Note: Employer rate is 12% for wages > RM 5,000.
                $share_employee = ceil($salary * 0.11);
                $share_employer = ceil($salary * 0.12);
            }
            return ['employee' => $share_employee, 'employer' => $share_employer];
        }

        // Lookup Table for Salaries <= RM 20,000
        // We iterate intervals based on Part A (Standard)
        // This is a condensed version of the lookup logic
        $rate = self::getEPFLookup($salary, $is_senior);

        return $rate;
    }

    /**
     * Calculate SOCSO (PERKESO) based on Act 4 (Employment Injury + Invalidity)
     * Uses official lookup table from statutory_tables.php
     * @param float $salary
     * @return array ['employee' => float, 'employer' => float]
     */
    public static function calculateSOCSO($salary)
    {
        // Capping: SOCSO table stops at RM 6,000
        // Wages exceeding RM 6,000 are treated as RM 6,000
        $effective_salary = $salary; // Logic handled by table iteration structure

        foreach (StatutoryTables::$SOCSO_TABLE as $row) {
            // [Threshold, Employer, Employee]
            if ($effective_salary <= $row[0]) {
                return ['employer' => $row[1], 'employee' => $row[2]];
            }
        }

        // If salary exceeds the last threshold (6000), use the last row (Max Cap)
        $last_row = end(StatutoryTables::$SOCSO_TABLE);
        return ['employer' => $last_row[1], 'employee' => $last_row[2]];
    }

    /**
     * Calculate EIS (SIP) based on Act 800
     * Uses official lookup table from statutory_tables.php
     * @param float $salary
     * @return array ['employee' => float, 'employer' => float]
     */
    public static function calculateEIS($salary)
    {
        // Capping: EIS logic is similar to SOCSO but caps at RM 6,000 (updated 2024/2025)

        foreach (StatutoryTables::$EIS_TABLE as $row) {
            // [Threshold, Employer, Employee]
            if ($salary <= $row[0]) {
                return ['employer' => $row[1], 'employee' => $row[2]];
            }
        }

        // If salary exceeds max threshold, use max cap
        $last_row = end(StatutoryTables::$EIS_TABLE);
        return ['employer' => $last_row[1], 'employee' => $last_row[2]];
    }

    private static function getEPFLookup($salary, $is_senior)
    {
        // For wages not exceeding RM 20,000, EPF must follow the lookup table.
        // The table uses intervals: 
        // - RM 20 intervals for wages up to RM 5,000
        // - RM 100 intervals for wages between RM 5,001 and RM 20,000.
        // We simulate the lookup table's "Wages up to X" logic.

        $threshold = 0;
        if ($salary <= 5000) {
            // RM 20 intervals
            $threshold = ceil($salary / 20) * 20;
            $emp_rate = 0.11;
            $employer_rate = 0.13;
        } else {
            // RM 100 intervals
            $threshold = ceil($salary / 100) * 100;
            $emp_rate = 0.11;
            $employer_rate = 0.12;
        }

        if ($is_senior) {
            // Part C/E (>= 60 years)
            // Employee Share: 0.00 (Standard) or 5.5% (voluntary) - Default 0%
            // Employer Share: 4% (wages > 5000 is 4%, <= 5000 is also approx 4/6.5% depending on Part)
            // Most common: Employer 4%, Employee 0% for those > 60 years.
            return [
                'employer' => ceil($threshold * 0.04),
                'employee' => 0
            ];
        }

        return [
            'employer' => ceil($threshold * $employer_rate),
            'employee' => ceil($threshold * $emp_rate)
        ];
    }
}
?>