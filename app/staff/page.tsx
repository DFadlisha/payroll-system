import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { ClockInOut } from "@/components/clock-in-out"
import { TodayAttendance } from "@/components/today-attendance"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Calendar, FileText, Clock } from "lucide-react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { CompanyHeader } from "@/components/company-header"

export default async function StaffDashboard() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase.from("profiles").select("*").eq("id", user.id).single()

  if (!profile) {
    redirect("/auth/login")
  }

  // Get attendance stats for current month
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  const { data: monthAttendance } = await supabase
    .from("attendance")
    .select("*")
    .eq("user_id", user.id)
    .gte("clock_in", firstDay.toISOString())
    .lte("clock_in", lastDay.toISOString())

  const totalDays = monthAttendance?.length || 0
  const totalHours = monthAttendance?.reduce((sum: number, record: any) => sum + (record.total_hours || 0), 0) || 0
  const totalOvertime = monthAttendance?.reduce((sum: number, record: any) => sum + (record.overtime_hours || 0), 0) || 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <CompanyHeader />

        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Welcome, {profile.full_name}</h1>
          <p className="text-gray-600">Track your attendance and manage your work hours</p>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-6">
          <ClockInOut userId={user.id} />

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                This Month
              </CardTitle>
              <CardDescription>Your attendance summary</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-muted-foreground">Days Worked</p>
                <p className="text-2xl font-bold">{totalDays}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Total Hours</p>
                <p className="text-2xl font-bold">{totalHours.toFixed(1)}h</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Overtime</p>
                <p className="text-2xl font-bold text-orange-600">{totalOvertime.toFixed(1)}h</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="h-5 w-5" />
                Quick Actions
              </CardTitle>
              <CardDescription>Access your information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Button asChild variant="outline" className="w-full justify-start bg-transparent">
                <Link href="/staff/payslips">
                  <FileText className="mr-2 h-4 w-4" />
                  View Payslips
                </Link>
              </Button>
              <Button asChild variant="outline" className="w-full justify-start bg-transparent">
                <Link href="/staff/attendance">
                  <Clock className="mr-2 h-4 w-4" />
                  Attendance History
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>

        <TodayAttendance userId={user.id} />
      </div>
    </div>
  )
}
