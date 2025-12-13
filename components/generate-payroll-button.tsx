"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { calculatePayroll, PAYROLL_RATES } from "@/lib/payroll-calculator"
import { Loader2, Calculator } from "lucide-react"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"

interface GeneratePayrollButtonProps {
  month: number
  year: number
  companyId?: string
}

interface EmployeeAllowances {
  [employeeId: string]: {
    otNormalHours: number
    otSundayHours: number
    otPublicHours: number
    projectsCompleted: number
    extraShifts: number
    daysGoodAttendance: number
    minutesLate: number
  }
}

export function GeneratePayrollButton({ month, year, companyId }: GeneratePayrollButtonProps) {
  const [isLoading, setIsLoading] = useState(false)
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null)
  const [showDialog, setShowDialog] = useState(false)
  const [employees, setEmployees] = useState<any[]>([])
  const [allowances, setAllowances] = useState<EmployeeAllowances>({})
  const router = useRouter()
  const supabase = createClient()

  const monthName = new Date(year, month - 1).toLocaleDateString("en-MY", { month: "long", year: "numeric" })

  const loadEmployees = async () => {
    setIsLoading(true)
    try {
      let query = supabase.from("profiles").select("*").order("full_name")
      
      if (companyId) {
        query = query.eq("company_id", companyId)
      }

      const { data, error } = await query
      if (error) throw error

      setEmployees(data || [])
      
      // Initialize allowances for each employee
      const initialAllowances: EmployeeAllowances = {}
      data?.forEach((emp: any) => {
        initialAllowances[emp.id] = {
          otNormalHours: 0,
          otSundayHours: 0,
          otPublicHours: 0,
          projectsCompleted: 0,
          extraShifts: 0,
          daysGoodAttendance: 0,
          minutesLate: 0,
        }
      })
      setAllowances(initialAllowances)
      setShowDialog(true)
    } catch (error) {
      setMessage({ type: "error", text: "Failed to load employees" })
    } finally {
      setIsLoading(false)
    }
  }

  const updateAllowance = (employeeId: string, field: keyof EmployeeAllowances[string], value: number) => {
    setAllowances(prev => ({
      ...prev,
      [employeeId]: {
        ...prev[employeeId],
        [field]: value,
      },
    }))
  }

  const handleGeneratePayroll = async () => {
    setIsLoading(true)
    setMessage(null)

    try {
      if (employees.length === 0) {
        throw new Error("No employees found")
      }

      // Process payroll for each employee with allowances
      interface PayrollRecord {
        user_id: string
        month: number
        year: number
        basic_salary: number
        regular_hours: number
        overtime_hours: number
        ot_normal_hours: number
        ot_sunday_hours: number
        ot_public_hours: number
        ot_normal: number
        ot_sunday: number
        ot_public: number
        projects_completed: number
        project_bonus: number
        extra_shifts: number
        shift_allowance: number
        days_good_attendance: number
        attendance_bonus: number
        minutes_late: number
        late_deduction: number
        total_allowances: number
        gross_pay: number
        epf_employee: number
        epf_employer: number
        socso_employee: number
        socso_employer: number
        eis_employee: number
        eis_employer: number
        net_pay: number
        status: string
      }

      const payrollRecords: PayrollRecord[] = []

      for (const employee of employees) {
        const empAllowances = allowances[employee.id] || {
          otNormalHours: 0,
          otSundayHours: 0,
          otPublicHours: 0,
          projectsCompleted: 0,
          extraShifts: 0,
          daysGoodAttendance: 0,
          minutesLate: 0,
        }

        // Calculate payroll with all allowances
        const payroll = calculatePayroll({
          basicSalary: employee.basic_salary,
          citizenshipStatus: employee.citizenship_status || "citizen",
          employmentType: employee.employment_type || "permanent",
          otNormalHours: empAllowances.otNormalHours,
          otSundayHours: empAllowances.otSundayHours,
          otPublicHours: empAllowances.otPublicHours,
          projectsCompleted: empAllowances.projectsCompleted,
          extraShifts: empAllowances.extraShifts,
          daysGoodAttendance: empAllowances.daysGoodAttendance,
          minutesLate: empAllowances.minutesLate,
        })

        payrollRecords.push({
          user_id: employee.id,
          month,
          year,
          basic_salary: payroll.basicSalary,
          regular_hours: payroll.regularHours,
          overtime_hours: payroll.overtimeHours,
          ot_normal_hours: empAllowances.otNormalHours,
          ot_sunday_hours: empAllowances.otSundayHours,
          ot_public_hours: empAllowances.otPublicHours,
          ot_normal: payroll.otNormal,
          ot_sunday: payroll.otSunday,
          ot_public: payroll.otPublic,
          projects_completed: empAllowances.projectsCompleted,
          project_bonus: payroll.projectBonus,
          extra_shifts: empAllowances.extraShifts,
          shift_allowance: payroll.shiftAllowance,
          days_good_attendance: empAllowances.daysGoodAttendance,
          attendance_bonus: payroll.attendanceBonus,
          minutes_late: empAllowances.minutesLate,
          late_deduction: payroll.lateDeduction,
          total_allowances: payroll.totalAllowances,
          gross_pay: payroll.grossPay,
          epf_employee: payroll.epfEmployee,
          epf_employer: payroll.epfEmployer,
          socso_employee: payroll.socsoEmployee,
          socso_employer: payroll.socsoEmployer,
          eis_employee: payroll.eisEmployee,
          eis_employer: payroll.eisEmployer,
          net_pay: payroll.netPay,
          status: "finalized",
        })
      }

      // Insert payroll records (upsert to handle duplicates)
      const { error: insertError } = await supabase.from("payroll").upsert(payrollRecords, {
        onConflict: "user_id,month,year",
      })

      if (insertError) throw insertError

      setMessage({
        type: "success",
        text: `Successfully generated payroll for ${payrollRecords.length} employees`,
      })
      setShowDialog(false)
      router.refresh()
    } catch (error) {
      console.error("[v0] Payroll generation error:", error)
      setMessage({
        type: "error",
        text: error instanceof Error ? error.message : "Failed to generate payroll",
      })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-4">
      <Button onClick={loadEmployees} disabled={isLoading} size="lg" className="w-full md:w-auto">
        {isLoading ? (
          <>
            <Loader2 className="mr-2 h-5 w-5 animate-spin" />
            Loading...
          </>
        ) : (
          <>
            <Calculator className="mr-2 h-5 w-5" />
            Generate Payroll for {monthName}
          </>
        )}
      </Button>

      {message && !showDialog && (
        <div
          className={`p-4 rounded-md text-sm ${
            message.type === "success" ? "bg-green-50 text-green-800" : "bg-red-50 text-red-800"
          }`}
        >
          {message.text}
        </div>
      )}

      <Dialog open={showDialog} onOpenChange={setShowDialog}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Generate Payroll - {monthName}</DialogTitle>
            <DialogDescription>
              Enter allowances, bonuses, and deductions for each employee
            </DialogDescription>
          </DialogHeader>

          {/* Rate Reference Card */}
          <Card className="bg-gray-50">
            <CardHeader className="py-3">
              <CardTitle className="text-sm">MI-NES Standard Rates</CardTitle>
            </CardHeader>
            <CardContent className="py-2">
              <div className="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                <div>OT Normal: <span className="font-semibold">RM{PAYROLL_RATES.RATE_OT_NORMAL}/hr</span></div>
                <div>OT Sunday: <span className="font-semibold">RM{PAYROLL_RATES.RATE_OT_SUNDAY}/hr</span></div>
                <div>OT Public: <span className="font-semibold">RM{PAYROLL_RATES.RATE_OT_PUBLIC}/hr</span></div>
                <div>Project: <span className="font-semibold">RM{PAYROLL_RATES.RATE_PROJECT}/each</span></div>
                <div>Shift: <span className="font-semibold">RM{PAYROLL_RATES.RATE_SHIFT}/shift</span></div>
                <div>Attendance: <span className="font-semibold">RM{PAYROLL_RATES.RATE_ATTENDANCE}/day</span></div>
                <div>Late: <span className="font-semibold text-red-600">-RM{PAYROLL_RATES.RATE_LATE}/min</span></div>
              </div>
            </CardContent>
          </Card>

          <div className="space-y-4">
            {employees.map((employee) => (
              <Card key={employee.id} className="border">
                <CardHeader className="py-3">
                  <div className="flex items-center justify-between">
                    <div>
                      <CardTitle className="text-base">{employee.full_name}</CardTitle>
                      <p className="text-xs text-muted-foreground">
                        Basic: RM{employee.basic_salary?.toFixed(2) || "0.00"}
                      </p>
                    </div>
                    <Badge 
                      variant="outline"
                      className={
                        employee.employment_type === "permanent" ? "bg-green-50 text-green-700" :
                        employee.employment_type === "part-time" ? "bg-blue-50 text-blue-700" :
                        "bg-orange-50 text-orange-700"
                      }
                    >
                      {employee.employment_type === "permanent" ? "Full-Time" : 
                       employee.employment_type === "part-time" ? "Part-Time" : "Intern"}
                    </Badge>
                  </div>
                </CardHeader>
                <CardContent className="py-3">
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                      <Label className="text-xs">OT Normal (hrs)</Label>
                      <Input
                        type="number"
                        min="0"
                        step="0.5"
                        value={allowances[employee.id]?.otNormalHours || 0}
                        onChange={(e) => updateAllowance(employee.id, "otNormalHours", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs">OT Sunday (hrs)</Label>
                      <Input
                        type="number"
                        min="0"
                        step="0.5"
                        value={allowances[employee.id]?.otSundayHours || 0}
                        onChange={(e) => updateAllowance(employee.id, "otSundayHours", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs">OT Public (hrs)</Label>
                      <Input
                        type="number"
                        min="0"
                        step="0.5"
                        value={allowances[employee.id]?.otPublicHours || 0}
                        onChange={(e) => updateAllowance(employee.id, "otPublicHours", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs">Projects Completed</Label>
                      <Input
                        type="number"
                        min="0"
                        value={allowances[employee.id]?.projectsCompleted || 0}
                        onChange={(e) => updateAllowance(employee.id, "projectsCompleted", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs">Extra Shifts</Label>
                      <Input
                        type="number"
                        min="0"
                        value={allowances[employee.id]?.extraShifts || 0}
                        onChange={(e) => updateAllowance(employee.id, "extraShifts", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs">Good Attendance (days)</Label>
                      <Input
                        type="number"
                        min="0"
                        max="31"
                        value={allowances[employee.id]?.daysGoodAttendance || 0}
                        onChange={(e) => updateAllowance(employee.id, "daysGoodAttendance", Number(e.target.value))}
                        className="h-8"
                      />
                    </div>
                    <div>
                      <Label className="text-xs text-red-600">Minutes Late</Label>
                      <Input
                        type="number"
                        min="0"
                        value={allowances[employee.id]?.minutesLate || 0}
                        onChange={(e) => updateAllowance(employee.id, "minutesLate", Number(e.target.value))}
                        className="h-8 border-red-200"
                      />
                    </div>
                  </div>
                  
                  {/* Preview calculation */}
                  {(allowances[employee.id]?.otNormalHours > 0 || 
                    allowances[employee.id]?.otSundayHours > 0 ||
                    allowances[employee.id]?.otPublicHours > 0 ||
                    allowances[employee.id]?.projectsCompleted > 0 ||
                    allowances[employee.id]?.extraShifts > 0 ||
                    allowances[employee.id]?.daysGoodAttendance > 0 ||
                    allowances[employee.id]?.minutesLate > 0) && (
                    <div className="mt-3 pt-3 border-t text-xs text-muted-foreground">
                      <span className="font-medium">Preview: </span>
                      {allowances[employee.id]?.otNormalHours > 0 && (
                        <span className="text-green-600">+RM{(allowances[employee.id].otNormalHours * PAYROLL_RATES.RATE_OT_NORMAL).toFixed(2)} (OT) </span>
                      )}
                      {allowances[employee.id]?.otSundayHours > 0 && (
                        <span className="text-green-600">+RM{(allowances[employee.id].otSundayHours * PAYROLL_RATES.RATE_OT_SUNDAY).toFixed(2)} (Sun) </span>
                      )}
                      {allowances[employee.id]?.otPublicHours > 0 && (
                        <span className="text-green-600">+RM{(allowances[employee.id].otPublicHours * PAYROLL_RATES.RATE_OT_PUBLIC).toFixed(2)} (PH) </span>
                      )}
                      {allowances[employee.id]?.projectsCompleted > 0 && (
                        <span className="text-blue-600">+RM{(allowances[employee.id].projectsCompleted * PAYROLL_RATES.RATE_PROJECT).toFixed(2)} (Proj) </span>
                      )}
                      {allowances[employee.id]?.extraShifts > 0 && (
                        <span className="text-blue-600">+RM{(allowances[employee.id].extraShifts * PAYROLL_RATES.RATE_SHIFT).toFixed(2)} (Shift) </span>
                      )}
                      {allowances[employee.id]?.daysGoodAttendance > 0 && (
                        <span className="text-purple-600">+RM{(allowances[employee.id].daysGoodAttendance * PAYROLL_RATES.RATE_ATTENDANCE).toFixed(2)} (Att) </span>
                      )}
                      {allowances[employee.id]?.minutesLate > 0 && (
                        <span className="text-red-600">-RM{(allowances[employee.id].minutesLate * PAYROLL_RATES.RATE_LATE).toFixed(2)} (Late)</span>
                      )}
                    </div>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t">
            <Button variant="outline" onClick={() => setShowDialog(false)}>
              Cancel
            </Button>
            <Button onClick={handleGeneratePayroll} disabled={isLoading}>
              {isLoading ? (
                <>
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  Generating...
                </>
              ) : (
                `Generate Payroll for ${employees.length} Employees`
              )}
            </Button>
          </div>

          {message && (
            <div
              className={`p-4 rounded-md text-sm ${
                message.type === "success" ? "bg-green-50 text-green-800" : "bg-red-50 text-red-800"
              }`}
            >
              {message.text}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  )
}
