// ============================================
// MI-NES Payroll Rate Configuration
// ============================================
export const PAYROLL_RATES = {
  // Overtime Rates
  RATE_OT_NORMAL: 10.00,      // Per Hour (1.5x equivalent)
  RATE_OT_SUNDAY: 12.50,      // Per Hour (2.0x equivalent)
  RATE_OT_PUBLIC: 20.00,      // Per Hour (Estimated 2x Normal rate)

  // Allowances & Bonuses
  RATE_PROJECT: 15.00,        // Per Project Completed
  RATE_SHIFT: 10.00,          // Per Extra Shift
  RATE_ATTENDANCE: 5.00,      // Per Day (Good Attendance Bonus)

  // Deductions
  RATE_LATE: 1.00,            // Deduction Per Minute Late

  // Basic Salary
  BASIC_STAFF: 1700.00,       // Full-Time Basic Salary
  BASIC_INTERN: 800.00,       // Internship Allowance
} as const

// ============================================
// Allowance & Bonus Calculations
// ============================================
export interface AllowanceInput {
  otNormalHours?: number
  otSundayHours?: number
  otPublicHours?: number
  projectsCompleted?: number
  extraShifts?: number
  daysGoodAttendance?: number
  minutesLate?: number
}

export function calculateAllowances(input: AllowanceInput) {
  const {
    otNormalHours = 0,
    otSundayHours = 0,
    otPublicHours = 0,
    projectsCompleted = 0,
    extraShifts = 0,
    daysGoodAttendance = 0,
    minutesLate = 0,
  } = input

  const otNormal = otNormalHours * PAYROLL_RATES.RATE_OT_NORMAL
  const otSunday = otSundayHours * PAYROLL_RATES.RATE_OT_SUNDAY
  const otPublic = otPublicHours * PAYROLL_RATES.RATE_OT_PUBLIC
  const projectBonus = projectsCompleted * PAYROLL_RATES.RATE_PROJECT
  const shiftAllowance = extraShifts * PAYROLL_RATES.RATE_SHIFT
  const attendanceBonus = daysGoodAttendance * PAYROLL_RATES.RATE_ATTENDANCE
  const lateDeduction = minutesLate * PAYROLL_RATES.RATE_LATE

  const totalAllowances = otNormal + otSunday + otPublic + projectBonus + shiftAllowance + attendanceBonus
  const totalDeductions = lateDeduction

  return {
    otNormal: Number.parseFloat(otNormal.toFixed(2)),
    otSunday: Number.parseFloat(otSunday.toFixed(2)),
    otPublic: Number.parseFloat(otPublic.toFixed(2)),
    projectBonus: Number.parseFloat(projectBonus.toFixed(2)),
    shiftAllowance: Number.parseFloat(shiftAllowance.toFixed(2)),
    attendanceBonus: Number.parseFloat(attendanceBonus.toFixed(2)),
    lateDeduction: Number.parseFloat(lateDeduction.toFixed(2)),
    totalAllowances: Number.parseFloat(totalAllowances.toFixed(2)),
    totalDeductions: Number.parseFloat(totalDeductions.toFixed(2)),
    netAllowances: Number.parseFloat((totalAllowances - totalDeductions).toFixed(2)),
  }
}

// Get default basic salary based on employment type
export function getDefaultBasicSalary(employmentType: string): number {
  if (employmentType === 'intern') {
    return PAYROLL_RATES.BASIC_INTERN
  }
  return PAYROLL_RATES.BASIC_STAFF
}

// ============================================
// Malaysian Statutory Calculations
// ============================================

// Malaysian EPF contribution rates based on official schedules
export function calculateEPF(grossSalary: number, citizenshipStatus = "citizen") {
  // EPF only applies to Malaysian citizens and permanent residents
  if (citizenshipStatus === "foreigner") {
    return { employee: 0, employer: 0 }
  }

  // Employee contribution: 11%
  const employeeRate = 0.11
  const employee = grossSalary * employeeRate

  // Employer contribution: 13% for salary <= RM5000, 12% for salary > RM5000
  const employerRate = grossSalary <= 5000 ? 0.13 : 0.12
  const employer = grossSalary * employerRate

  return {
    employee: Number.parseFloat(employee.toFixed(2)),
    employer: Number.parseFloat(employer.toFixed(2)),
  }
}

// Malaysian SOCSO contribution rates (tiered system)
export function calculateSOCSO(grossSalary: number) {
  // SOCSO only applies to salaries up to RM5000
  if (grossSalary > 5000) {
    return { employee: 0, employer: 0 }
  }

  // Simplified tiered rates (actual rates are more complex)
  // This is a representative calculation
  let employee = 0
  let employer = 0

  if (grossSalary <= 1000) {
    employee = 5.0
    employer = 17.5
  } else if (grossSalary <= 1500) {
    employee = 7.5
    employer = 26.25
  } else if (grossSalary <= 2000) {
    employee = 10.0
    employer = 35.0
  } else if (grossSalary <= 2500) {
    employee = 12.5
    employer = 43.75
  } else if (grossSalary <= 3000) {
    employee = 15.0
    employer = 52.5
  } else if (grossSalary <= 3500) {
    employee = 17.5
    employer = 61.25
  } else if (grossSalary <= 4000) {
    employee = 20.0
    employer = 70.0
  } else if (grossSalary <= 4500) {
    employee = 22.5
    employer = 78.75
  } else {
    employee = 25.0
    employer = 87.5
  }

  return {
    employee: Number.parseFloat(employee.toFixed(2)),
    employer: Number.parseFloat(employer.toFixed(2)),
  }
}

// Malaysian EIS (Employment Insurance System) contribution
export function calculateEIS(grossSalary: number) {
  // EIS applies to salaries up to RM4000
  const cappedSalary = Math.min(grossSalary, 4000)

  // Both employee and employer contribute 0.2%
  const rate = 0.002
  const employee = cappedSalary * rate
  const employer = cappedSalary * rate

  return {
    employee: Number.parseFloat(employee.toFixed(2)),
    employer: Number.parseFloat(employer.toFixed(2)),
  }
}

// Calculate complete payroll
export interface PayrollCalculation {
  basicSalary: number
  regularHours: number
  overtimeHours: number
  // Allowances breakdown
  otNormal: number
  otSunday: number
  otPublic: number
  projectBonus: number
  shiftAllowance: number
  attendanceBonus: number
  totalAllowances: number
  // Deductions
  lateDeduction: number
  grossPay: number
  epfEmployee: number
  epfEmployer: number
  socsoEmployee: number
  socsoEmployer: number
  eisEmployee: number
  eisEmployer: number
  totalStatutoryDeductions: number
  totalDeductions: number
  netPay: number
}

export interface PayrollInput {
  basicSalary?: number
  regularHours?: number
  citizenshipStatus?: string
  employmentType?: "permanent" | "contract" | "intern" | "part-time"
  // Allowance inputs
  otNormalHours?: number
  otSundayHours?: number
  otPublicHours?: number
  projectsCompleted?: number
  extraShifts?: number
  daysGoodAttendance?: number
  minutesLate?: number
}

export function calculatePayroll(input: PayrollInput): PayrollCalculation {
  const {
    basicSalary,
    regularHours = 0,
    citizenshipStatus = "citizen",
    employmentType = "permanent",
    otNormalHours = 0,
    otSundayHours = 0,
    otPublicHours = 0,
    projectsCompleted = 0,
    extraShifts = 0,
    daysGoodAttendance = 0,
    minutesLate = 0,
  } = input

  // Determine basic salary
  const effectiveBasicSalary = basicSalary ?? getDefaultBasicSalary(employmentType)

  // Calculate allowances using the new rates
  const allowances = calculateAllowances({
    otNormalHours,
    otSundayHours,
    otPublicHours,
    projectsCompleted,
    extraShifts,
    daysGoodAttendance,
    minutesLate,
  })

  // Calculate gross pay
  const grossPay = effectiveBasicSalary + allowances.netAllowances

  let epf = { employee: 0, employer: 0 }
  let socso = { employee: 0, employer: 0 }
  let eis = { employee: 0, employer: 0 }

  // Calculate statutory deductions based on employment type
  if (employmentType !== "intern") {
    // Interns are exempt from EPF/SOCSO/EIS
    if (employmentType === "permanent" || employmentType === "contract") {
      // Full deductions for permanent and contract staff
      epf = calculateEPF(grossPay, citizenshipStatus)
      socso = calculateSOCSO(grossPay)
      eis = calculateEIS(grossPay)
    } else if (employmentType === "part-time") {
      // Part-time: EPF if eligible, reduced SOCSO
      if (grossPay >= 1000) {
        epf = calculateEPF(grossPay, citizenshipStatus)
      }
      socso = calculateSOCSO(grossPay)
      // Part-time may not be eligible for EIS depending on hours worked
      if (regularHours >= 70) {
        eis = calculateEIS(grossPay)
      }
    }
  }

  // Total statutory deductions (employee portion only)
  const totalStatutoryDeductions = epf.employee + socso.employee + eis.employee

  // Total deductions including late penalty
  const totalDeductions = totalStatutoryDeductions + allowances.lateDeduction

  // Net pay
  const netPay = grossPay - totalStatutoryDeductions

  return {
    basicSalary: effectiveBasicSalary,
    regularHours,
    overtimeHours: otNormalHours + otSundayHours + otPublicHours,
    // Allowances breakdown
    otNormal: allowances.otNormal,
    otSunday: allowances.otSunday,
    otPublic: allowances.otPublic,
    projectBonus: allowances.projectBonus,
    shiftAllowance: allowances.shiftAllowance,
    attendanceBonus: allowances.attendanceBonus,
    totalAllowances: allowances.totalAllowances,
    // Deductions
    lateDeduction: allowances.lateDeduction,
    grossPay: Number.parseFloat(grossPay.toFixed(2)),
    epfEmployee: epf.employee,
    epfEmployer: epf.employer,
    socsoEmployee: socso.employee,
    socsoEmployer: socso.employer,
    eisEmployee: eis.employee,
    eisEmployer: eis.employer,
    totalStatutoryDeductions: Number.parseFloat(totalStatutoryDeductions.toFixed(2)),
    totalDeductions: Number.parseFloat(totalDeductions.toFixed(2)),
    netPay: Number.parseFloat(netPay.toFixed(2)),
  }
}

// Legacy function for backward compatibility
export function calculatePayrollLegacy(
  basicSalary: number,
  regularHours: number,
  overtimeHours: number,
  hourlyRate?: number,
  citizenshipStatus = "citizen",
  employmentType: "permanent" | "contract" | "intern" | "part-time" = "permanent",
): PayrollCalculation {
  return calculatePayroll({
    basicSalary,
    regularHours,
    citizenshipStatus,
    employmentType,
    otNormalHours: overtimeHours,
  })
}
