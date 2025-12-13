import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Users, Clock, DollarSign, TrendingUp, Calendar } from "lucide-react"
import Link from "next/link"

export default async function EmployeePayrollPage() {
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

  // Get all employees with their latest attendance summary
  const { data: employees } = await supabase
    .from("profiles")
    .select("*")
    .order("full_name", { ascending: true })

  // Get current month attendance for all employees
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  const { data: attendanceRecords } = await supabase
    .from("attendance")
    .select("*")
    .gte("clock_in", firstDay.toISOString())
    .lte("clock_in", lastDay.toISOString())

  // Group attendance by employee
  const attendanceByEmployee = attendanceRecords?.reduce((acc: any, record: any) => {
    if (!acc[record.user_id]) {
      acc[record.user_id] = []
    }
    acc[record.user_id].push(record)
    return acc
  }, {})

  const employeeTypes = {
    permanent: employees?.filter((e: any) => e.employment_type === "permanent") || [],
    contract: employees?.filter((e: any) => e.employment_type === "contract") || [],
    "part-time": employees?.filter((e: any) => e.employment_type === "part-time") || [],
    intern: employees?.filter((e: any) => e.employment_type === "intern") || [],
  }

  const renderEmployeeTable = (employeeList: any[]) => {
    if (!employeeList || employeeList.length === 0) {
      return (
        <div className="text-center py-8 text-muted-foreground">
          <Users className="h-12 w-12 mx-auto mb-3 opacity-50" />
          <p>No employees in this category</p>
        </div>
      )
    }

    return (
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Employee</TableHead>
            <TableHead>Employment Type</TableHead>
            <TableHead>Basic Salary</TableHead>
            <TableHead>Hourly Rate</TableHead>
            <TableHead>This Month Hours</TableHead>
            <TableHead>Attendance Days</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {employeeList.map((employee: any) => {
            const attendance = attendanceByEmployee?.[employee.id] || []
            const totalHours = attendance.reduce(
              (sum: number, a: any) => sum + (a.total_hours || 0),
              0
            )
            const completedDays = attendance.filter((a: any) => a.status === "completed").length

            return (
              <TableRow key={employee.id}>
                <TableCell>
                  <div>
                    <div className="font-medium">{employee.full_name}</div>
                    <div className="text-xs text-muted-foreground">{employee.email}</div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge variant="outline" className="capitalize">
                    {(employee.employment_type || "permanent").replace("-", " ")}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div className="font-medium">RM {employee.basic_salary?.toFixed(2) || "0.00"}</div>
                </TableCell>
                <TableCell>
                  {employee.hourly_rate ? (
                    <div className="font-medium">RM {employee.hourly_rate.toFixed(2)}/hr</div>
                  ) : (
                    <span className="text-muted-foreground">-</span>
                  )}
                </TableCell>
                <TableCell>
                  <div className="flex items-center gap-1">
                    <Clock className="h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">{totalHours.toFixed(1)}h</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div className="flex items-center gap-1">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">{completedDays} days</span>
                  </div>
                </TableCell>
                <TableCell>
                  <Link
                    href={`/hr/employees?employee=${employee.id}`}
                    className="text-primary hover:underline text-sm"
                  >
                    View Details
                  </Link>
                </TableCell>
              </TableRow>
            )
          })}
        </TableBody>
      </Table>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <Users className="h-8 w-8" />
            Employee Payroll Overview
          </h1>
          <p className="text-gray-600">
            View employees by type and their attendance-based payroll information
          </p>
        </div>

        {/* Summary Cards */}
        <div className="grid gap-6 md:grid-cols-4 mb-6">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Permanent Staff</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{employeeTypes.permanent.length}</div>
              <p className="text-xs text-muted-foreground">Full-time employees</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Contract Staff</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{employeeTypes.contract.length}</div>
              <p className="text-xs text-muted-foreground">Contract employees</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Part-Time</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-orange-600">{employeeTypes["part-time"].length}</div>
              <p className="text-xs text-muted-foreground">Hourly workers</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Interns</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{employeeTypes.intern.length}</div>
              <p className="text-xs text-muted-foreground">No deductions</p>
            </CardContent>
          </Card>
        </div>

        {/* Employee Tables by Type */}
        <Card>
          <CardHeader>
            <CardTitle>Employees by Type</CardTitle>
            <CardDescription>
              View attendance and payroll information for{" "}
              {now.toLocaleDateString("en-MY", { month: "long", year: "numeric" })}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="all" className="w-full">
              <TabsList className="grid w-full grid-cols-5">
                <TabsTrigger value="all">All</TabsTrigger>
                <TabsTrigger value="permanent">Permanent</TabsTrigger>
                <TabsTrigger value="contract">Contract</TabsTrigger>
                <TabsTrigger value="part-time">Part-Time</TabsTrigger>
                <TabsTrigger value="intern">Intern</TabsTrigger>
              </TabsList>

              <TabsContent value="all" className="mt-6">
                <div className="overflow-x-auto">
                  {renderEmployeeTable(employees || [])}
                </div>
              </TabsContent>

              <TabsContent value="permanent" className="mt-6">
                <div className="overflow-x-auto">
                  {renderEmployeeTable(employeeTypes.permanent)}
                </div>
              </TabsContent>

              <TabsContent value="contract" className="mt-6">
                <div className="overflow-x-auto">
                  {renderEmployeeTable(employeeTypes.contract)}
                </div>
              </TabsContent>

              <TabsContent value="part-time" className="mt-6">
                <div className="overflow-x-auto">
                  {renderEmployeeTable(employeeTypes["part-time"])}
                </div>
              </TabsContent>

              <TabsContent value="intern" className="mt-6">
                <div className="overflow-x-auto">
                  {renderEmployeeTable(employeeTypes.intern)}
                </div>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>

        {/* Calculation Info */}
        <Card className="mt-6 bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800">
          <CardHeader>
            <CardTitle className="text-lg">Payroll Calculation Rules</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div>
              <strong className="text-blue-900 dark:text-blue-100">Permanent/Contract Staff:</strong>
              <p className="text-blue-700 dark:text-blue-300">
                Basic salary + overtime (1.5x rate). Full EPF (11% employee, 12-13% employer), SOCSO, and EIS deductions.
              </p>
            </div>
            <div>
              <strong className="text-blue-900 dark:text-blue-100">Part-Time Staff:</strong>
              <p className="text-blue-700 dark:text-blue-300">
                Hourly rate × hours worked + overtime (1.5x). EPF if monthly pay ≥ RM1000, SOCSO applicable, EIS if 70+ hours/month.
              </p>
            </div>
            <div>
              <strong className="text-blue-900 dark:text-blue-100">Interns:</strong>
              <p className="text-blue-700 dark:text-blue-300">
                Hourly rate or fixed allowance. NO EPF, SOCSO, or EIS deductions. Net pay = Gross pay.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
