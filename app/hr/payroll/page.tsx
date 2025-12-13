import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { GeneratePayrollButton } from "@/components/generate-payroll-button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { DollarSign } from "lucide-react"

export default async function PayrollPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase.from("profiles").select("*").eq("id", user.id).single()

  if (!profile || profile.role !== "hr") {
    redirect("/staff")
  }

  // Get current month/year
  const now = new Date()
  const currentMonth = now.getMonth() + 1
  const currentYear = now.getFullYear()

  // Get payroll records for current month
  const { data: payrolls } = await supabase
    .from("payroll")
    .select("*, profiles!inner(full_name, email, employment_type)")
    .eq("month", currentMonth)
    .eq("year", currentYear)
    .order("created_at", { ascending: false })

  const totalGrossPay = payrolls?.reduce((sum: number, p: any) => sum + p.gross_pay, 0) || 0
  const totalNetPay = payrolls?.reduce((sum: number, p: any) => sum + p.net_pay, 0) || 0
  const totalEPF = payrolls?.reduce((sum: number, p: any) => sum + p.epf_employee + p.epf_employer, 0) || 0
  const totalSOCSO = payrolls?.reduce((sum: number, p: any) => sum + p.socso_employee + p.socso_employer, 0) || 0
  const totalEIS = payrolls?.reduce((sum: number, p: any) => sum + p.eis_employee + p.eis_employer, 0) || 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <DollarSign className="h-8 w-8" />
            Payroll Management
          </h1>
          <p className="text-gray-600">
            Process payroll for{" "}
            {now.toLocaleDateString("en-MY", {
              month: "long",
              year: "numeric",
            })}
          </p>
        </div>

        <div className="grid gap-6 md:grid-cols-4 mb-6">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total Gross Pay</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">RM {totalGrossPay.toFixed(2)}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total Net Pay</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">RM {totalNetPay.toFixed(2)}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total EPF</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">RM {totalEPF.toFixed(2)}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">SOCSO + EIS</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-purple-600">RM {(totalSOCSO + totalEIS).toFixed(2)}</div>
            </CardContent>
          </Card>
        </div>

        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Generate Payroll</CardTitle>
            <CardDescription>Process payroll for all employees for the current month</CardDescription>
          </CardHeader>
          <CardContent>
            <GeneratePayrollButton month={currentMonth} year={currentYear} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Payroll Records</CardTitle>
            <CardDescription>Current month payroll summary</CardDescription>
          </CardHeader>
          <CardContent>
            {payrolls && payrolls.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Employee</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Hours</TableHead>
                      <TableHead>Gross Pay</TableHead>
                      <TableHead>Deductions</TableHead>
                      <TableHead>Net Pay</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {payrolls.map((payroll: any) => (
                      <TableRow key={payroll.id}>
                        <TableCell>
                          <div className="font-medium">{payroll.profiles.full_name}</div>
                          <div className="text-xs text-muted-foreground">{payroll.profiles.email}</div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="capitalize">
                            {(payroll.profiles.employment_type || "permanent").replace("-", " ")}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="text-sm">
                            <div>Reg: {payroll.regular_hours.toFixed(1)}h</div>
                            {payroll.overtime_hours > 0 && (
                              <div className="text-orange-600">OT: {payroll.overtime_hours.toFixed(1)}h</div>
                            )}
                          </div>
                        </TableCell>
                        <TableCell>RM {payroll.gross_pay.toFixed(2)}</TableCell>
                        <TableCell>
                          <div className="text-sm text-red-600">
                            {payroll.profiles.employment_type === "intern" ? (
                              <span className="text-muted-foreground">N/A</span>
                            ) : (
                              <span>RM {(payroll.epf_employee + payroll.socso_employee + payroll.eis_employee).toFixed(2)}</span>
                            )}
                          </div>
                        </TableCell>
                        <TableCell className="font-semibold text-green-600">RM {payroll.net_pay.toFixed(2)}</TableCell>
                        <TableCell>
                          <Badge
                            variant={
                              payroll.status === "paid"
                                ? "default"
                                : payroll.status === "finalized"
                                  ? "secondary"
                                  : "outline"
                            }
                          >
                            {payroll.status.toUpperCase()}
                          </Badge>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <p>No payroll records for this month</p>
                <p className="text-sm mt-2">Click the button above to generate payroll</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
