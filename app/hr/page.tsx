import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Users, DollarSign, Clock, TrendingUp } from "lucide-react"
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
  const { count: totalEmployees } = await supabase
    .from("profiles")
    .select("*", { count: "exact", head: true })
    .eq("company_id", profile.company_id)

  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)

  const { data: companyProfiles } = await supabase.from("profiles").select("id").eq("company_id", profile.company_id)

  const companyUserIds = companyProfiles?.map((p: any) => p.id) || []

  const { data: monthAttendance } = await supabase
    .from("attendance")
    .select("*")
    .in("user_id", companyUserIds)
    .gte("clock_in", firstDay.toISOString())

  const { data: pendingLeaves } = await supabase
    .from("leaves")
    .select("*")
    .in("user_id", companyUserIds)
    .eq("status", "pending")

  const totalHoursThisMonth = monthAttendance?.reduce((sum: number, record: any) => sum + (record.total_hours || 0), 0) || 0

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
              <div className="text-2xl font-bold">{totalEmployees || 0}</div>
              <p className="text-xs text-muted-foreground">Active staff members</p>
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
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {monthAttendance?.filter((a: any) => {
                  const clockIn = new Date(a.clock_in)
                  return clockIn.toDateString() === now.toDateString()
                }).length || 0}
              </div>
              <p className="text-xs text-muted-foreground">Clocked in today</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
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
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Recent Activity</CardTitle>
              <CardDescription>Latest system updates</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {pendingLeaves && pendingLeaves.length > 0 ? (
                  <div className="text-sm">
                    <p className="font-medium text-orange-600">{pendingLeaves.length} leave requests pending</p>
                    <p className="text-muted-foreground">Review and approve leave applications</p>
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">No pending actions</p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
