# Merged: PCB_OT_INTEGRATION_REPORT.md

This documentation was consolidated into the project's `README.md`.

Please see `README.md` (top-level) for the merged PCB & OT integration report.

If you want this file permanently removed, confirm and I'll delete it.

---

If you want this file permanently removed, confirm and I'll delete it.

### 1. Helper Functions Added to `includes/functions.php`

Four new helper functions were appended to the functions file:

#### `isPublicHoliday($date)`
- **Purpose**: Check if a given date is a public holiday
- **Database**: Queries `public_holidays` table
- **Return**: Boolean (true if public holiday, false otherwise)
- **Usage**: Used by OT rate calculation to apply 3x rate

#### `isSunday($date)`
- **Purpose**: Check if a given date is Sunday (rest day)
- **Return**: Boolean (true if Sunday, false otherwise)
- **Usage**: Used by OT rate calculation to apply 2x rate

#### `getOvertimeRate($date, $hourlyRate)`
- **Purpose**: Calculate overtime rate based on Malaysian Labour Law
- **Rates**:
  - **Normal day**: 1.5x hourly rate
  - **Rest day (Sunday)**: 2.0x hourly rate
  - **Public holiday**: 3.0x hourly rate
- **Return**: Array with `rate`, `type`, and `hourly_rate`

#### `calculatePCB($monthlyIncome, $dependents = 0)`
- **Purpose**: Calculate monthly PCB (tax deduction) using LHDN 2024 rates
- **Tax Brackets** (Chargeable Income):
  - RM 0 - 5,000: 0%
  - RM 5,001 - 20,000: 1%
  - RM 20,001 - 35,000: 3%
  - RM 35,001 - 50,000: 6%
  - RM 50,001 - 70,000: 11%
  - RM 70,001 - 100,000: 19%
  - RM 100,001 - 150,000: 25%
  - RM 150,001 - 250,000: 26%
  - RM 250,001+: 28%
- **Tax Relief**:
  - Personal relief: RM9,000
  - Dependent relief: RM2,000 per dependent (max 6)
- **Return**: Float (monthly PCB amount)

---

### 2. Overtime Calculation Integration in `hr/payroll.php`

**Lines 85-110 (Approximate)**

**Before:**
```php
$otNormal = $otHours * $RATE_OT_NORMAL;
$otSunday = 0; // Will need to calculate based on day of week
$otPublic = 0; // Will need public holiday data
```

**After:**
```php
// Get detailed attendance records for OT breakdown
$stmtOT = $conn->prepare("
    SELECT clock_in, overtime_hours
    FROM attendance 
    WHERE user_id = ? 
    AND EXTRACT(MONTH FROM clock_in) = ? 
    AND EXTRACT(YEAR FROM clock_in) = ? 
    AND overtime_hours > 0
    AND status IN ('active', 'completed')
");
$stmtOT->execute([$emp['id'], $selectedMonth, $selectedYear]);
$otRecords = $stmtOT->fetchAll(PDO::FETCH_ASSOC);
This file was moved to the `docs/` folder during repository tidy-up.

See: [docs/PCB_OT_INTEGRATION_REPORT.md](docs/PCB_OT_INTEGRATION_REPORT.md)

The original content has been preserved in `docs/` to keep history.
foreach ($otRecords as $otRecord) {

    $otDate = date('Y-m-d', strtotime($otRecord['clock_in']));

    $otHrs = floatval($otRecord['overtime_hours']);
