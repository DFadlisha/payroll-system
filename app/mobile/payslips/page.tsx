import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { FileText, Download, DollarSign, Calendar } from "lucide-react"
import Link from "next/link"

export default async function MobilePayslipsPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()

  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase
    .from("profiles")
    .select("*")
    .eq("id", user.id)
    .single()

  // Get all payroll records
  const { data: payrolls } = await supabase
    .from("payroll")
    .select("*")
    .eq("user_id", user.id)
    .order("year", { ascending: false })
    .order("month", { ascending: false })

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "draft":
        return <Badge variant="outline" className="bg-gray-50 text-gray-700 border-gray-200">Draft</Badge>
      case "finalized":
        return <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">Finalized</Badge>
      case "paid":
        return <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">Paid</Badge>
      default:
        return <Badge variant="outline">{status}</Badge>
    }
  }

  const getMonthName = (month: number) => {
    const date = new Date(2000, month - 1, 1)
    return date.toLocaleDateString("en-MY", { month: "long" })
  }

  const downloadPayslip = async (payroll: any) => {
    // This would typically generate a PDF, but for now we'll use a simple approach
    const payslipData = `
PAYSLIP
-------
Employee: ${profile?.full_name}
Employment Type: ${profile?.employment_type}
Period: ${getMonthName(payroll.month)} ${payroll.year}

Earnings:
Gross Pay: RM ${payroll.gross_pay.toFixed(2)}

Deductions:
EPF (Employee): RM ${payroll.epf_employee.toFixed(2)}
SOCSO (Employee): RM ${payroll.socso_employee.toFixed(2)}
EIS (Employee): RM ${payroll.eis_employee.toFixed(2)}

Net Pay: RM ${payroll.net_pay.toFixed(2)}

Regular Hours: ${payroll.regular_hours.toFixed(2)}
Overtime Hours: ${payroll.overtime_hours.toFixed(2)}
    `

    const blob = new Blob([payslipData], { type: "text/plain" })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `payslip-${payroll.year}-${payroll.month.toString().padStart(2, "0")}.txt`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  }

  return (
    <div className="container max-w-2xl px-4 py-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Payslips</h1>
        <p className="text-muted-foreground">View and download your payslips</p>
      </div>

      {/* Employee Info Card */}
      <Card>
        <CardContent className="pt-6">
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Employee Name</span>
              <span className="font-medium">{profile?.full_name}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Employment Type</span>
              <Badge variant="outline" className="capitalize">
                {profile?.employment_type?.replace("-", " ")}
              </Badge>
            </div>
            {profile?.epf_number && (
              <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">EPF Number</span>
                <span className="font-medium">{profile.epf_number}</span>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Payslips List */}
      <div className="space-y-3">
        <h2 className="text-lg font-semibold">Payment History</h2>
        {payrolls && payrolls.length > 0 ? (
          <div className="space-y-3">
            {payrolls.map((payroll: any) => (
              <Card key={payroll.id}>
                <CardContent className="pt-6">
                  <div className="space-y-4">
                    {/* Header */}
                    <div className="flex items-start justify-between">
                      <div className="flex items-center gap-3">
                        <div className="p-2 rounded-lg bg-primary/10">
                          <FileText className="h-5 w-5 text-primary" />
                        </div>
                        <div>
                          <p className="font-medium">
                            {getMonthName(payroll.month)} {payroll.year}
                          </p>
                          <p className="text-sm text-muted-foreground">
                            {payroll.regular_hours.toFixed(1)} regular hrs
                            {payroll.overtime_hours > 0 &&
                              ` + ${payroll.overtime_hours.toFixed(1)} OT hrs`}
                          </p>
                        </div>
                      </div>
                      {getStatusBadge(payroll.status)}
                    </div>

                    {/* Amount Summary */}
                    <div className="space-y-2 p-3 bg-muted rounded-lg">
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">Gross Pay</span>
                        <span>RM {payroll.gross_pay.toFixed(2)}</span>
                      </div>
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">Total Deductions</span>
                        <span className="text-red-600">
                          - RM{" "}
                          {(
                            payroll.epf_employee +
                            payroll.socso_employee +
                            payroll.eis_employee
                          ).toFixed(2)}
                        </span>
                      </div>
                      <div className="pt-2 border-t flex items-center justify-between">
                        <span className="font-medium">Net Pay</span>
                        <span className="text-xl font-bold text-green-600">
                          RM {payroll.net_pay.toFixed(2)}
                        </span>
                      </div>
                    </div>

                    {/* Deduction Details */}
                    <div className="space-y-2 text-sm">
                      <p className="text-muted-foreground font-medium">Deductions:</p>
                      <div className="grid grid-cols-3 gap-2">
                        <div>
                          <p className="text-xs text-muted-foreground">EPF</p>
                          <p className="font-medium">RM {payroll.epf_employee.toFixed(2)}</p>
                        </div>
                        <div>
                          <p className="text-xs text-muted-foreground">SOCSO</p>
                          <p className="font-medium">RM {payroll.socso_employee.toFixed(2)}</p>
                        </div>
                        <div>
                          <p className="text-xs text-muted-foreground">EIS</p>
                          <p className="font-medium">RM {payroll.eis_employee.toFixed(2)}</p>
                        </div>
                      </div>
                    </div>

                    {/* Download Button */}
                    {(payroll.status === "finalized" || payroll.status === "paid") && (
                      <Link
                        href={`/staff/payslips?payrollId=${payroll.id}`}
                        target="_blank"
                        className="w-full"
                      >
                        <Button variant="outline" className="w-full" size="sm">
                          <Download className="mr-2 h-4 w-4" />
                          View Full Payslip
                        </Button>
                      </Link>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="pt-6">
              <div className="text-center py-8">
                <FileText className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                <p className="text-muted-foreground">No payslips available</p>
                <p className="text-sm text-muted-foreground mt-1">
                  Payslips will appear here once generated by HR
                </p>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Info Note */}
      <Card className="bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <DollarSign className="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
            <div className="space-y-1">
              <p className="text-sm font-medium text-blue-900 dark:text-blue-100">
                Payslip Information
              </p>
              <p className="text-xs text-blue-700 dark:text-blue-300">
                {profile?.employment_type === "intern" &&
                  "As an intern, you are not subject to EPF/SOCSO/EIS deductions."}
                {profile?.employment_type === "part-time" &&
                  "Part-time employees may have different deduction rates."}
                {profile?.employment_type === "permanent" &&
                  "Your payslip includes all statutory deductions (EPF, SOCSO, EIS)."}
                {profile?.employment_type === "contract" &&
                  "Contract employees may have different benefit structures."}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
