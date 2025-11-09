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
  regularHours: number
  overtimeHours: number
  grossPay: number
  epfEmployee: number
  epfEmployer: number
  socsoEmployee: number
  socsoEmployer: number
  eisEmployee: number
  eisEmployer: number
  totalDeductions: number
  netPay: number
}

export function calculatePayroll(
  basicSalary: number,
  regularHours: number,
  overtimeHours: number,
  hourlyRate?: number,
  citizenshipStatus = "citizen",
  employmentType: "permanent" | "contract" | "intern" = "permanent",
): PayrollCalculation {
  // Calculate gross pay
  const regularPay = hourlyRate ? regularHours * hourlyRate : basicSalary
  const overtimePay = hourlyRate ? overtimeHours * hourlyRate * 1.5 : (basicSalary / 160) * overtimeHours * 1.5 // Assuming 160 hours/month
  const grossPay = regularPay + overtimePay

  let epf = { employee: 0, employer: 0 }
  let socso = { employee: 0, employer: 0 }
  let eis = { employee: 0, employer: 0 }

  if (employmentType !== "intern") {
    // Calculate statutory deductions only for non-interns
    epf = calculateEPF(grossPay, citizenshipStatus)
    socso = calculateSOCSO(grossPay)
    eis = calculateEIS(grossPay)
  }

  // Total employee deductions
  const totalDeductions = epf.employee + socso.employee + eis.employee

  // Net pay
  const netPay = grossPay - totalDeductions

  return {
    regularHours,
    overtimeHours,
    grossPay: Number.parseFloat(grossPay.toFixed(2)),
    epfEmployee: epf.employee,
    epfEmployer: epf.employer,
    socsoEmployee: socso.employee,
    socsoEmployer: socso.employer,
    eisEmployee: eis.employee,
    eisEmployer: eis.employer,
    totalDeductions: Number.parseFloat(totalDeductions.toFixed(2)),
    netPay: Number.parseFloat(netPay.toFixed(2)),
  }
}
