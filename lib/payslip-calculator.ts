interface PayslipCalculationInput {
  employmentType: "permanent" | "part-time" | "intern" | "contract"
  workDays: number
  overtimeHours: number
  hasProject: boolean
  deductions?: number
}

interface PayslipBreakdown {
  baseSalary: number
  overtimePay: number
  projectBonus: number
  grossSalary: number
  deductions: number
  netSalary: number
}

const DAILY_RATES = {
  permanent: 70.83,
  "part-time": 70.83,
  intern: 33.33,
  contract: 70.83,
}

const OVERTIME_RATE = 10 // per hour
const PROJECT_BONUS = 15 // fixed bonus

export function calculatePayslip(input: PayslipCalculationInput): PayslipBreakdown {
  const dailyRate = DAILY_RATES[input.employmentType]

  // Base salary (work days × daily rate)
  const baseSalary = input.workDays * dailyRate

  // Overtime pay (overtime hours × rate per hour)
  const overtimePay = input.overtimeHours * OVERTIME_RATE

  // Project bonus (if applicable)
  const projectBonus = input.hasProject ? PROJECT_BONUS : 0

  // Gross salary
  const grossSalary = baseSalary + overtimePay + projectBonus

  // Deductions (default to 0 if not provided)
  const deductions = input.deductions || 0

  // Net salary
  const netSalary = grossSalary - deductions

  return {
    baseSalary: parseFloat(baseSalary.toFixed(2)),
    overtimePay: parseFloat(overtimePay.toFixed(2)),
    projectBonus,
    grossSalary: parseFloat(grossSalary.toFixed(2)),
    deductions: parseFloat(deductions.toFixed(2)),
    netSalary: parseFloat(netSalary.toFixed(2)),
  }
}

export function formatCurrency(amount: number): string {
  return `RM ${amount.toFixed(2)}`
}
