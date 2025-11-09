import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { FileText } from "lucide-react"

export default async function ReportsPage() {
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

  const now = new Date()
  const currentMonth = now.getMonth() + 1
  const currentYear = now.getFullYear()

  // Get payroll data for statutory reports
  const { data: payrolls } = await supabase
    .from("payroll")
    .select("*, profiles!inner(full_name, email, epf_number, socso_number)")
    .eq("month", currentMonth)
    .eq("year", currentYear)

  const totalEPFEmployee = payrolls?.reduce((sum, p) => sum + p.epf_employee, 0) || 0
  const totalEPFEmployer = payrolls?.reduce((sum, p) => sum + p.epf_employer, 0) || 0
  const totalSOCSO = payrolls?.reduce((sum, p) => sum + p.socso_employee + p.socso_employer, 0) || 0
  const totalEIS = payrolls?.reduce((sum, p) => sum + p.eis_employee + p.eis_employer, 0) || 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <FileText className="h-8 w-8" />
            Statutory Reports
          </h1>
          <p className="text-gray-600">
            Monthly contribution reports for{" "}
            {now.toLocaleDateString("en-MY", {
              month: "long",
              year: "numeric",
            })}
          </p>
        </div>

        <div className="grid gap-6 md:grid-cols-3 mb-6">
          <Card>
            <CardHeader>
              <CardTitle>Total EPF</CardTitle>
              <CardDescription>Employee Provident Fund</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-muted-foreground">Employee (11%)</span>
                  <span className="font-semibold">RM {totalEPFEmployee.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-muted-foreground">Employer (12-13%)</span>
                  <span className="font-semibold">RM {totalEPFEmployer.toFixed(2)}</span>
                </div>
                <div className="flex justify-between pt-2 border-t">
                  <span className="font-semibold">Total Payable</span>
                  <span className="text-lg font-bold text-blue-600">
                    RM {(totalEPFEmployee + totalEPFEmployer).toFixed(2)}
                  </span>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Total SOCSO</CardTitle>
              <CardDescription>Social Security Organization</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex justify-between pt-2">
                  <span className="font-semibold">Total Payable</span>
                  <span className="text-lg font-bold text-green-600">RM {totalSOCSO.toFixed(2)}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Total EIS</CardTitle>
              <CardDescription>Employment Insurance System</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="flex justify-between pt-2">
                  <span className="font-semibold">Total Payable</span>
                  <span className="text-lg font-bold text-purple-600">RM {totalEIS.toFixed(2)}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Detailed Contribution Report</CardTitle>
            <CardDescription>Employee-wise statutory contributions</CardDescription>
          </CardHeader>
          <CardContent>
            {payrolls && payrolls.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Employee</TableHead>
                      <TableHead>EPF Number</TableHead>
                      <TableHead>SOCSO Number</TableHead>
                      <TableHead>Gross Pay</TableHead>
                      <TableHead>EPF (Total)</TableHead>
                      <TableHead>SOCSO (Total)</TableHead>
                      <TableHead>EIS (Total)</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {payrolls.map((payroll: any) => (
                      <TableRow key={payroll.id}>
                        <TableCell className="font-medium">{payroll.profiles.full_name}</TableCell>
                        <TableCell>{payroll.profiles.epf_number || "-"}</TableCell>
                        <TableCell>{payroll.profiles.socso_number || "-"}</TableCell>
                        <TableCell>RM {payroll.gross_pay.toFixed(2)}</TableCell>
                        <TableCell>RM {(payroll.epf_employee + payroll.epf_employer).toFixed(2)}</TableCell>
                        <TableCell>RM {(payroll.socso_employee + payroll.socso_employer).toFixed(2)}</TableCell>
                        <TableCell>RM {(payroll.eis_employee + payroll.eis_employer).toFixed(2)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <p>No payroll data available for this month</p>
                <p className="text-sm mt-2">Generate payroll to see statutory reports</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
