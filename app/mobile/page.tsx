import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Clock, Calendar, FileText, MapPin, TrendingUp, ChevronRight, Sparkles } from "lucide-react"
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

  const getEmploymentBadge = (type: string) => {
    switch (type) {
      case "intern":
        return { label: "Intern", color: "bg-amber-100 text-amber-700 border-amber-200" }
      case "part-time":
        return { label: "Part-Time", color: "bg-blue-100 text-blue-700 border-blue-200" }
      case "permanent":
        return { label: "Full-Time", color: "bg-emerald-100 text-emerald-700 border-emerald-200" }
      default:
        return { label: type, color: "bg-slate-100 text-slate-700 border-slate-200" }
    }
  }

  const employmentBadge = getEmploymentBadge(profile?.employment_type || "")

  return (
    <div className="min-h-screen">
      {/* Hero Header with Gradient */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500" />
        <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2)_1px,transparent_1px)] bg-[length:20px_20px]" />
        
        <div className="relative px-5 pt-12 pb-8">
          {/* Greeting */}
          <div className="flex items-start justify-between mb-6">
            <div>
              <div className="flex items-center gap-2 mb-1">
                <Sparkles className="h-4 w-4 text-amber-300" />
                <span className="text-indigo-100 text-sm font-medium">Welcome back</span>
              </div>
              <h1 className="text-2xl font-bold text-white mb-2">{profile?.full_name}</h1>
              <Badge className={`${employmentBadge.color} border font-medium`}>
                {employmentBadge.label}
              </Badge>
            </div>
            <div className="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center border border-white/30">
              <span className="text-2xl font-bold text-white">
                {profile?.full_name?.charAt(0) || "U"}
              </span>
            </div>
          </div>

          {/* Quick Stats Cards */}
          <div className="grid grid-cols-2 gap-3">
            <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 border border-white/30">
              <div className="flex items-center gap-2 mb-2">
                <TrendingUp className="h-4 w-4 text-white/80" />
                <span className="text-xs text-white/80 font-medium">This Month</span>
              </div>
              <p className="text-2xl font-bold text-white">{totalHoursThisMonth?.toFixed(0) || "0"}</p>
              <p className="text-xs text-white/70">hours worked</p>
            </div>
            <div className="bg-white/20 backdrop-blur-sm rounded-2xl p-4 border border-white/30">
              <div className="flex items-center gap-2 mb-2">
                <Calendar className="h-4 w-4 text-white/80" />
                <span className="text-xs text-white/80 font-medium">Pending</span>
              </div>
              <p className="text-2xl font-bold text-white">{pendingLeaves || 0}</p>
              <p className="text-xs text-white/70">leave requests</p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="px-5 py-6 space-y-6 -mt-2">
        {/* Clock In/Out Card - Premium Design */}
        <Card className="overflow-hidden border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50">
          <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <div className="flex items-center gap-3">
              <div className="p-2.5 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                <Clock className="h-5 w-5 text-white" />
              </div>
              <div>
                <h3 className="font-semibold text-slate-900 dark:text-white">Attendance</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400">Clock in with GPS tracking</p>
              </div>
            </div>
          </div>
          <CardContent className="p-5">
            <MobileClockInOut userId={user.id} />
          </CardContent>
        </Card>

        {/* Today's Status - if clocked in */}
        {todayAttendance && (
          <Card className="border-0 shadow-lg shadow-emerald-100/50 dark:shadow-emerald-900/20 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/50 dark:to-teal-950/50">
            <CardContent className="p-5">
              <div className="flex items-center gap-3 mb-4">
                <div className="w-3 h-3 rounded-full bg-emerald-500 animate-pulse" />
                <h3 className="font-semibold text-emerald-900 dark:text-emerald-100">Today's Status</h3>
              </div>
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-emerald-700 dark:text-emerald-300">Clock In</span>
                  <span className="font-semibold text-emerald-900 dark:text-emerald-100">
                    {new Date(todayAttendance.clock_in).toLocaleTimeString("en-MY", {
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </span>
                </div>
                {todayAttendance.clock_in_address && (
                  <div className="flex items-start gap-2 bg-white/50 dark:bg-slate-900/50 rounded-xl p-3">
                    <MapPin className="h-4 w-4 mt-0.5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                    <span className="text-xs text-emerald-700 dark:text-emerald-300">
                      {todayAttendance.clock_in_address}
                    </span>
                  </div>
                )}
                {todayAttendance.clock_out && (
                  <>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-emerald-700 dark:text-emerald-300">Clock Out</span>
                      <span className="font-semibold text-emerald-900 dark:text-emerald-100">
                        {new Date(todayAttendance.clock_out).toLocaleTimeString("en-MY", {
                          hour: "2-digit",
                          minute: "2-digit",
                        })}
                      </span>
                    </div>
                    {todayAttendance.total_hours && (
                      <div className="flex items-center justify-between pt-3 border-t border-emerald-200 dark:border-emerald-800">
                        <span className="text-sm font-medium text-emerald-700 dark:text-emerald-300">Total</span>
                        <span className="text-lg font-bold text-emerald-900 dark:text-emerald-100">
                          {todayAttendance.total_hours.toFixed(2)} hrs
                        </span>
                      </div>
                    )}
                  </>
                )}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Quick Actions */}
        <div className="space-y-3">
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white px-1">Quick Actions</h2>
          <div className="space-y-3">
            <Link href="/mobile/attendance">
              <Card className="border-0 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.98]">
                <CardContent className="flex items-center gap-4 p-4">
                  <div className="p-3 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 shadow-lg shadow-blue-500/25">
                    <Clock className="h-5 w-5 text-white" />
                  </div>
                  <div className="flex-1">
                    <p className="font-semibold text-slate-900 dark:text-white">Attendance History</p>
                    <p className="text-sm text-slate-500 dark:text-slate-400">View all clock records</p>
                  </div>
                  <ChevronRight className="h-5 w-5 text-slate-400" />
                </CardContent>
              </Card>
            </Link>

            <Link href="/mobile/leaves">
              <Card className="border-0 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.98]">
                <CardContent className="flex items-center gap-4 p-4">
                  <div className="p-3 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                    <Calendar className="h-5 w-5 text-white" />
                  </div>
                  <div className="flex-1">
                    <p className="font-semibold text-slate-900 dark:text-white">Apply for Leave</p>
                    <p className="text-sm text-slate-500 dark:text-slate-400">Request time off</p>
                  </div>
                  <ChevronRight className="h-5 w-5 text-slate-400" />
                </CardContent>
              </Card>
            </Link>

            <Link href="/mobile/payslips">
              <Card className="border-0 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.98]">
                <CardContent className="flex items-center gap-4 p-4">
                  <div className="p-3 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25">
                    <FileText className="h-5 w-5 text-white" />
                  </div>
                  <div className="flex-1">
                    <p className="font-semibold text-slate-900 dark:text-white">View Payslips</p>
                    <p className="text-sm text-slate-500 dark:text-slate-400">Download pay records</p>
                  </div>
                  <ChevronRight className="h-5 w-5 text-slate-400" />
                </CardContent>
              </Card>
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
