"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import { calculatePayslip, formatCurrency } from "@/lib/payslip-calculator"
import { DollarSign, Clock, Briefcase } from "lucide-react"

interface PayslipBreakdownProps {
  employmentType: string
  workDays: number
  overtimeHours: number
  hasProject: boolean
  deductions?: number
  month: string
  year: string
}

export function PayslipBreakdown({
  employmentType,
  workDays,
  overtimeHours,
  hasProject,
  deductions = 0,
  month,
  year,
}: PayslipBreakdownProps) {
  const breakdown = calculatePayslip({
    employmentType: employmentType as "permanent" | "part-time" | "intern" | "contract",
    workDays,
    overtimeHours,
    hasProject,
    deductions,
  })

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div>
            <CardTitle>Payslip</CardTitle>
            <CardDescription>
              {month} {year}
            </CardDescription>
          </div>
          <Badge variant="outline" className="capitalize">
            {employmentType}
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Work Details */}
        <div className="grid grid-cols-3 gap-4">
          <div className="space-y-1">
            <p className="text-xs text-muted-foreground">Work Days</p>
            <p className="text-lg font-semibold">{workDays}</p>
          </div>
          <div className="space-y-1">
            <p className="text-xs text-muted-foreground">Overtime Hours</p>
            <p className="text-lg font-semibold">{overtimeHours}</p>
          </div>
          <div className="space-y-1">
            <p className="text-xs text-muted-foreground">Project</p>
            <p className="text-lg font-semibold">{hasProject ? "Yes" : "No"}</p>
          </div>
        </div>

        <Separator />

        {/* Earnings Breakdown */}
        <div className="space-y-3">
          <h3 className="font-semibold text-sm">Earnings</h3>

          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <DollarSign className="h-4 w-4 text-muted-foreground" />
                <span className="text-sm">Base Salary</span>
              </div>
              <span className="font-medium">{formatCurrency(breakdown.baseSalary)}</span>
            </div>

            {breakdown.overtimePay > 0 && (
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Clock className="h-4 w-4 text-orange-500" />
                  <span className="text-sm">Overtime Pay</span>
                </div>
                <span className="font-medium text-orange-600">
                  +{formatCurrency(breakdown.overtimePay)}
                </span>
              </div>
            )}

            {breakdown.projectBonus > 0 && (
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Briefcase className="h-4 w-4 text-blue-500" />
                  <span className="text-sm">Project Bonus</span>
                </div>
                <span className="font-medium text-blue-600">
                  +{formatCurrency(breakdown.projectBonus)}
                </span>
              </div>
            )}
          </div>
        </div>

        <Separator />

        {/* Gross Salary */}
        <div className="bg-blue-50 dark:bg-blue-950 p-3 rounded-lg">
          <div className="flex items-center justify-between">
            <span className="font-semibold">Gross Salary</span>
            <span className="text-lg font-bold text-blue-600">
              {formatCurrency(breakdown.grossSalary)}
            </span>
          </div>
        </div>

        <Separator />

        {/* Deductions */}
        {breakdown.deductions > 0 && (
          <div className="space-y-3">
            <h3 className="font-semibold text-sm">Deductions</h3>
            <div className="flex items-center justify-between">
              <span className="text-sm">Total Deductions</span>
              <span className="font-medium text-red-600">
                -{formatCurrency(breakdown.deductions)}
              </span>
            </div>
          </div>
        )}

        {breakdown.deductions > 0 && <Separator />}

        {/* Net Salary */}
        <div className="bg-green-50 dark:bg-green-950 p-4 rounded-lg">
          <div className="flex items-center justify-between">
            <span className="font-bold text-lg">Net Salary</span>
            <span className="text-2xl font-bold text-green-600">
              {formatCurrency(breakdown.netSalary)}
            </span>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}
