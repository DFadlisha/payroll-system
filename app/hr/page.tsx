import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Users, DollarSign, Clock, TrendingUp, Calendar, FileText, UserCheck, UserMinus } from "lucide-react"
import { CompanyHeader } from "@/components/company-header"

export default async function HRDashboard() {
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

  // Get statistics
  const { data: allEmployees } = await supabase
    .from("profiles")
    .select("*")
    .eq("company_id", profile.company_id)

  const totalEmployees = allEmployees?.length || 0
  const fullTimeCount = allEmployees?.filter((e: any) => e.employment_type === "permanent").length || 0
  const partTimeCount = allEmployees?.filter((e: any) => e.employment_type === "part-time").length || 0
  const internCount = allEmployees?.filter((e: any) => e.employment_type === "intern").length || 0

  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const today = now.toISOString().split("T")[0]

  const companyUserIds = allEmployees?.map((p: any) => p.id) || []

  const { data: monthAttendance } = await supabase
    .from("attendance")
    .select("*")
    .in("user_id", companyUserIds)
    .gte("clock_in", firstDay.toISOString())

  const { data: todayAttendance } = await supabase
    .from("attendance")
    .select("*")
    .in("user_id", companyUserIds)
    .gte("clock_in", `${today}T00:00:00`)
    .lte("clock_in", `${today}T23:59:59`)

  const { data: pendingLeaves } = await supabase
    .from("leaves")
    .select("*, profiles!inner(full_name)")
    .in("user_id", companyUserIds)
    .eq("status", "pending")
    .order("created_at", { ascending: false })
    .limit(5)

  // Get this month's payroll
  const { data: monthPayroll } = await supabase
    .from("payroll")
    .select("*")
    .in("user_id", companyUserIds)
    .eq("month", now.getMonth() + 1)
    .eq("year", now.getFullYear())

  const totalHoursThisMonth = monthAttendance?.reduce((sum: number, record: any) => sum + (record.total_hours || 0), 0) || 0
  const clockedInToday = todayAttendance?.filter((a: any) => !a.clock_out).length || 0
  const completedToday = todayAttendance?.filter((a: any) => a.clock_out).length || 0
  const totalPayrollAmount = monthPayroll?.reduce((sum: number, p: any) => sum + (p.net_salary || 0), 0) || 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <CompanyHeader />

        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">HR Dashboard</h1>
          <p className="text-gray-600">Manage employees, payroll, and attendance</p>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Total Employees</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalEmployees}</div>
              <div className="flex gap-2 mt-1">
                <Badge variant="outline" className="text-xs bg-green-50">{fullTimeCount} Full-Time</Badge>
                <Badge variant="outline" className="text-xs bg-blue-50">{partTimeCount} Part-Time</Badge>
                <Badge variant="outline" className="text-xs bg-orange-50">{internCount} Intern</Badge>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Hours This Month</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalHoursThisMonth.toFixed(0)}h</div>
              <p className="text-xs text-muted-foreground">Total work hours</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Pending Leaves</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{pendingLeaves?.length || 0}</div>
              <p className="text-xs text-muted-foreground">Awaiting approval</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Attendance Today</CardTitle>
              <UserCheck className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{clockedInToday + completedToday}</div>
              <div className="flex gap-2 mt-1 text-xs">
                <span className="text-yellow-600">{clockedInToday} active</span>
                <span className="text-muted-foreground">•</span>
                <span className="text-green-600">{completedToday} completed</span>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Workforce Overview */}
        <div className="grid gap-6 md:grid-cols-3 mb-8">
          <Card className="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-green-800">Full-Time Staff</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-green-700">{fullTimeCount}</div>
              <p className="text-xs text-green-600">EPF, SOCSO, EIS deductions apply</p>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-blue-800">Part-Time Staff</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-blue-700">{partTimeCount}</div>
              <p className="text-xs text-blue-600">EPF, SOCSO, EIS deductions apply</p>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-orange-800">Interns</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold text-orange-700">{internCount}</div>
              <p className="text-xs text-orange-600">No statutory deductions</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>Common HR tasks</CardDescription>
            </CardHeader>
            <CardContent className="space-y-2">
              <a
                href="/hr/payroll"
                className="block p-4 rounded-lg border hover:bg-gray-50 transition-colors cursor-pointer"
              >
                <div className="flex items-center gap-3">
                  <DollarSign className="h-5 w-5 text-green-600" />
                  <div>
                    <p className="font-medium">Process Payroll</p>
                    <p className="text-sm text-muted-foreground">Generate monthly payslips</p>
                  </div>
                </div>
              </a>
              <a
                href="/hr/employees"
                className="block p-4 rounded-lg border hover:bg-gray-50 transition-colors cursor-pointer"
              >
                <div className="flex items-center gap-3">
                  <Users className="h-5 w-5 text-blue-600" />
                  <div>
                    <p className="font-medium">Manage Employees</p>
                    <p className="text-sm text-muted-foreground">View and edit employee profiles</p>
                  </div>
                </div>
              </a>
              <a
                href="/hr/attendance"
                className="block p-4 rounded-lg border hover:bg-gray-50 transition-colors cursor-pointer"
              >
                <div className="flex items-center gap-3">
                  <Clock className="h-5 w-5 text-purple-600" />
                  <div>
                    <p className="font-medium">View Attendance</p>
                    <p className="text-sm text-muted-foreground">Track employee clock-ins</p>
                  </div>
                </div>
              </a>
              <a
                href="/hr/reports"
                className="block p-4 rounded-lg border hover:bg-gray-50 transition-colors cursor-pointer"
              >
                <div className="flex items-center gap-3">
                  <FileText className="h-5 w-5 text-orange-600" />
                  <div>
                    <p className="font-medium">Statutory Reports</p>
                    <p className="text-sm text-muted-foreground">EPF, SOCSO, EIS reports</p>
                  </div>
                </div>
              </a>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Pending Leave Requests</CardTitle>
              <CardDescription>Requires your approval</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {pendingLeaves && pendingLeaves.length > 0 ? (
                  <>
                    {pendingLeaves.map((leave: any) => (
                      <div key={leave.id} className="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-100">
                        <div>
                          <p className="font-medium text-sm">{leave.profiles.full_name}</p>
                          <p className="text-xs text-muted-foreground capitalize">{leave.leave_type} leave</p>
                          <p className="text-xs text-muted-foreground">
                            {new Date(leave.start_date).toLocaleDateString("en-MY")} - {new Date(leave.end_date).toLocaleDateString("en-MY")}
                          </p>
                        </div>
                        <Badge variant="outline" className="bg-orange-100 text-orange-700">Pending</Badge>
                      </div>
                    ))}
                    <a href="/hr/leaves" className="block text-center text-sm text-indigo-600 hover:underline mt-2">
                      View all leave requests →
                    </a>
                  </>
                ) : (
                  <div className="text-center py-4 text-muted-foreground">
                    <Calendar className="h-8 w-8 mx-auto mb-2 opacity-50" />
                    <p className="text-sm">No pending leave requests</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Monthly Summary</CardTitle>
              <CardDescription>{now.toLocaleDateString("en-MY", { month: "long", year: "numeric" })}</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-2">
                    <Clock className="h-4 w-4 text-purple-600" />
                    <span className="text-sm">Total Hours Logged</span>
                  </div>
                  <span className="font-bold">{totalHoursThisMonth.toFixed(1)}h</span>
                </div>
                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-2">
                    <DollarSign className="h-4 w-4 text-green-600" />
                    <span className="text-sm">Payroll Processed</span>
                  </div>
                  <span className="font-bold">
                    {monthPayroll && monthPayroll.length > 0 
                      ? `RM ${totalPayrollAmount.toLocaleString("en-MY", { minimumFractionDigits: 2 })}` 
                      : "Not yet"
                    }
                  </span>
                </div>
                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-2">
                    <Users className="h-4 w-4 text-blue-600" />
                    <span className="text-sm">Staff Paid</span>
                  </div>
                  <span className="font-bold">{monthPayroll?.length || 0} / {totalEmployees}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
