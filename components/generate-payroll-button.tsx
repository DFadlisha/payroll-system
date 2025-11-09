"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { calculatePayroll } from "@/lib/payroll-calculator"
import { Loader2 } from "lucide-react"

interface GeneratePayrollButtonProps {
  month: number
  year: number
}

export function GeneratePayrollButton({ month, year }: GeneratePayrollButtonProps) {
  const [isLoading, setIsLoading] = useState(false)
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null)
  const router = useRouter()
  const supabase = createClient()

  const handleGeneratePayroll = async () => {
    setIsLoading(true)
    setMessage(null)

    try {
      // Get all employees
      const { data: employees, error: employeesError } = await supabase.from("profiles").select("*")

      if (employeesError) throw employeesError

      if (!employees || employees.length === 0) {
        throw new Error("No employees found")
      }

      // Get attendance for the month
      const firstDay = new Date(year, month - 1, 1)
      const lastDay = new Date(year, month, 0)

      const { data: attendanceRecords, error: attendanceError } = await supabase
        .from("attendance")
        .select("*")
        .gte("clock_in", firstDay.toISOString())
        .lte("clock_in", lastDay.toISOString())
        .eq("status", "completed")

      if (attendanceError) throw attendanceError

      // Process payroll for each employee
      interface PayrollRecord {
        user_id: any
        month: number
        year: number
        regular_hours: number
        overtime_hours: number
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
        // Get employee's attendance
        const employeeAttendance = attendanceRecords?.filter((a) => a.user_id === employee.id) || []

        const regularHours = employeeAttendance.reduce(
          (sum, a) => sum + (a.total_hours || 0) - (a.overtime_hours || 0),
          0,
        )
        const overtimeHours = employeeAttendance.reduce((sum, a) => sum + (a.overtime_hours || 0), 0)

        // Calculate payroll
        const payroll = calculatePayroll(
          employee.basic_salary,
          regularHours,
          overtimeHours,
          employee.hourly_rate,
          employee.citizenship_status,
        )

        payrollRecords.push({
          user_id: employee.id,
          month,
          year,
          regular_hours: payroll.regularHours,
          overtime_hours: payroll.overtimeHours,
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
      <Button onClick={handleGeneratePayroll} disabled={isLoading} size="lg" className="w-full md:w-auto">
        {isLoading ? (
          <>
            <Loader2 className="mr-2 h-5 w-5 animate-spin" />
            Generating Payroll...
          </>
        ) : (
          "Generate Payroll for Current Month"
        )}
      </Button>

      {message && (
        <div
          className={`p-4 rounded-md text-sm ${
            message.type === "success" ? "bg-green-50 text-green-800" : "bg-red-50 text-red-800"
          }`}
        >
          {message.text}
        </div>
      )}
    </div>
  )
}
