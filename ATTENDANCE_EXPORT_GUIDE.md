# Attendance Export to Excel Feature

## Overview
The HR attendance page now includes the ability to export attendance records to Excel (CSV format).

## Features

### 1. Export Current Date
- Exports all attendance records for the currently selected date
- Quick access via the "Export to Excel" dropdown menu
- Filename format: `attendance_YYYYMMDD.csv`

### 2. Export Date Range
- Exports attendance records for a custom date range
- Opens a modal dialog to select start and end dates
- Default range: First day of current month to today
- Filename format: `attendance_YYYYMMDD_to_YYYYMMDD.csv`

## Exported Data Columns

The exported CSV file includes the following columns:

1. **Employee Name** - Full name of the employee
2. **IC Number** - Identity card number
3. **Role** - Employee role (Staff, Leader, Intern, Part-time)
4. **Employment Type** - Type of employment
5. **Date** - Attendance date
6. **Clock In** - Clock in timestamp (YYYY-MM-DD HH:MM:SS)
7. **Clock Out** - Clock out timestamp (YYYY-MM-DD HH:MM:SS)
8. **Location** - Work location name (if assigned)
9. **GPS Location** - GPS coordinates (if available)
10. **Total Hours** - Total working hours
11. **OT Normal (Hours)** - Normal overtime hours
12. **OT Sunday (Hours)** - Sunday overtime hours
13. **OT Public Holiday (Hours)** - Public holiday overtime hours
14. **Project Hours** - Project hours (for interns)
15. **Extra Shifts** - Number of extra shifts
16. **Late Minutes** - Total late minutes
17. **Status** - Attendance status (Active, Completed, Absent, Late)

## How to Use

### Export Current Date:
1. Navigate to HR → Attendance
2. Select the desired date using the date picker
3. Click "Export to Excel" button
4. Select "Current Date" from the dropdown
5. The CSV file will download automatically

### Export Date Range:
1. Navigate to HR → Attendance
2. Click "Export to Excel" button
3. Select "Date Range..." from the dropdown
4. In the modal dialog:
   - Select the start date
   - Select the end date
   - Click "Export"
5. The CSV file will download automatically

## Excel Compatibility

- The exported file uses UTF-8 encoding with BOM for proper Excel compatibility
- CSV format ensures compatibility with Excel, Google Sheets, and other spreadsheet applications
- All special characters and non-English text are properly encoded

## File Location

- **Export Handler**: `/hr/export_attendance.php`
- **Attendance Page**: `/hr/attendance.php`

## Security

- Only HR users can access the export functionality
- Exports are limited to the user's company data only
- Authentication is required for all export operations

## Technical Notes

- The export uses PHP's native CSV functions for optimal performance
- Database queries are optimized with proper indexing on date columns
- Large exports are handled efficiently with streaming output
