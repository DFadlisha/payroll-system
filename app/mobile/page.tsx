import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Clock, Calendar, FileText, MapPin, TrendingUp } from "lucide-react"
import Link from "next/link"
import { MobileClockInOut } from "@/components/mobile-clock-in-out"

export default async function MobilePage() {
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

  // Get today's attendance
  const today = new Date().toISOString().split("T")[0]
  const { data: todayAttendance } = await supabase
    .from("attendance")
    .select("*")
    .eq("user_id", user.id)
    .gte("clock_in", `${today}T00:00:00`)
    .lte("clock_in", `${today}T23:59:59`)
    .order("clock_in", { ascending: false })
    .limit(1)
    .maybeSingle()

  // Get pending leaves count
  const { count: pendingLeaves } = await supabase
    .from("leaves")
    .select("*", { count: "exact", head: true })
    .eq("user_id", user.id)
    .eq("status", "pending")

  // Get this month's attendance summary
  const startOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    .toISOString()
    .split("T")[0]
  const { data: monthAttendance } = await supabase
    .from("attendance")
    .select("total_hours")
    .eq("user_id", user.id)
    .gte("clock_in", `${startOfMonth}T00:00:00`)
    .eq("status", "completed")

  const totalHoursThisMonth = monthAttendance?.reduce(
    (sum: number, record: any) => sum + (record.total_hours || 0),
    0
  )

  return (
    <div className="container max-w-2xl px-4 py-6 space-y-6">
      {/* Welcome Section */}
      <div className="space-y-2">
        <h1 className="text-2xl font-bold">Welcome back!</h1>
        <p className="text-muted-foreground">{profile?.full_name}</p>
        <Badge variant="outline" className="text-xs">
          {profile?.employment_type === "intern" && "Intern"}
          {profile?.employment_type === "part-time" && "Part-Time Staff"}
          {profile?.employment_type === "permanent" && "Permanent Staff"}
          {profile?.employment_type === "contract" && "Contract Staff"}
        </Badge>
      </div>

      {/* Clock In/Out Card */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Attendance
          </CardTitle>
          <CardDescription>Clock in/out with location tracking</CardDescription>
        </CardHeader>
        <CardContent>
          <MobileClockInOut userId={user.id} />
        </CardContent>
      </Card>

      {/* Today's Status */}
      {todayAttendance && (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Today's Status</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Clock In</span>
              <span className="font-medium">
                {new Date(todayAttendance.clock_in).toLocaleTimeString("en-MY", {
                  hour: "2-digit",
                  minute: "2-digit",
                })}
              </span>
            </div>
            {todayAttendance.clock_in_address && (
              <div className="flex items-start gap-2">
                <MapPin className="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                <span className="text-sm text-muted-foreground">
                  {todayAttendance.clock_in_address}
                </span>
              </div>
            )}
            {todayAttendance.clock_out && (
              <>
                <div className="flex items-center justify-between">
                  <span className="text-sm text-muted-foreground">Clock Out</span>
                  <span className="font-medium">
                    {new Date(todayAttendance.clock_out).toLocaleTimeString("en-MY", {
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </span>
                </div>
                {todayAttendance.total_hours && (
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-muted-foreground">Total Hours</span>
                    <span className="font-medium">
                      {todayAttendance.total_hours.toFixed(2)} hours
                    </span>
                  </div>
                )}
              </>
            )}
          </CardContent>
        </Card>
      )}

      {/* Quick Stats */}
      <div className="grid grid-cols-2 gap-4">
        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium flex items-center gap-2">
              <TrendingUp className="h-4 w-4" />
              Hours This Month
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">{totalHoursThisMonth?.toFixed(1) || "0"}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-3">
            <CardTitle className="text-sm font-medium flex items-center gap-2">
              <Calendar className="h-4 w-4" />
              Pending Leaves
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">{pendingLeaves || 0}</p>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <div className="space-y-3">
        <h2 className="text-lg font-semibold">Quick Actions</h2>
        <div className="grid gap-3">
          <Link href="/mobile/attendance">
            <Card className="hover:bg-accent transition-colors cursor-pointer">
              <CardContent className="flex items-center gap-3 p-4">
                <div className="p-2 rounded-lg bg-primary/10">
                  <Clock className="h-5 w-5 text-primary" />
                </div>
                <div>
                  <p className="font-medium">View Attendance History</p>
                  <p className="text-sm text-muted-foreground">See all your clock records</p>
                </div>
              </CardContent>
            </Card>
          </Link>

          <Link href="/mobile/leaves">
            <Card className="hover:bg-accent transition-colors cursor-pointer">
              <CardContent className="flex items-center gap-3 p-4">
                <div className="p-2 rounded-lg bg-primary/10">
                  <Calendar className="h-5 w-5 text-primary" />
                </div>
                <div>
                  <p className="font-medium">Apply for Leave</p>
                  <p className="text-sm text-muted-foreground">Request time off</p>
                </div>
              </CardContent>
            </Card>
          </Link>

          <Link href="/mobile/payslips">
            <Card className="hover:bg-accent transition-colors cursor-pointer">
              <CardContent className="flex items-center gap-3 p-4">
                <div className="p-2 rounded-lg bg-primary/10">
                  <FileText className="h-5 w-5 text-primary" />
                </div>
                <div>
                  <p className="font-medium">View Payslips</p>
                  <p className="text-sm text-muted-foreground">Download your pay records</p>
                </div>
              </CardContent>
            </Card>
          </Link>
        </div>
      </div>
    </div>
  )
}
