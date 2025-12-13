import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Clock, MapPin, Calendar, History } from "lucide-react"
import { MobileClockInOut } from "@/components/mobile-clock-in-out"

export default async function MobileAttendancePage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()

  if (!user) {
    redirect("/auth/login")
  }

  // Get attendance records for the last 30 days
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

  const { data: attendanceRecords } = await supabase
    .from("attendance")
    .select("*")
    .eq("user_id", user.id)
    .gte("clock_in", thirtyDaysAgo.toISOString())
    .order("clock_in", { ascending: false })

  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-blue-600 via-cyan-600 to-teal-500" />
        <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2)_1px,transparent_1px)] bg-[length:20px_20px]" />
        
        <div className="relative px-5 pt-12 pb-8">
          <div className="flex items-center gap-3 mb-2">
            <div className="p-2.5 rounded-xl bg-white/20 backdrop-blur-sm">
              <Clock className="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-white">Attendance</h1>
              <p className="text-blue-100 text-sm">Track your work hours</p>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 py-6 space-y-6 -mt-2">
        {/* Clock In/Out Section */}
        <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
          <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <div className="flex items-center gap-3">
              <div className="p-2.5 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg shadow-blue-500/30">
                <Clock className="h-5 w-5 text-white" />
              </div>
              <div>
                <h3 className="font-semibold text-slate-900 dark:text-white">Clock In / Out</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400">Location will be recorded</p>
              </div>
            </div>
          </div>
          <CardContent className="p-5">
            <MobileClockInOut userId={user.id} />
          </CardContent>
        </Card>

        {/* Attendance History */}
        <div className="space-y-4">
          <div className="flex items-center gap-2 px-1">
            <History className="h-5 w-5 text-slate-500" />
            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Recent Records</h2>
            <Badge variant="outline" className="ml-auto">{attendanceRecords?.length || 0} records</Badge>
          </div>
          
          {attendanceRecords && attendanceRecords.length > 0 ? (
            <div className="space-y-3">
              {attendanceRecords.map((record: any) => (
                <Card key={record.id} className="border-0 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                  <CardContent className="p-0">
                    {/* Date Header */}
                    <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-4 py-3 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4 text-slate-500" />
                        <span className="font-semibold text-slate-700 dark:text-slate-300">
                          {new Date(record.clock_in).toLocaleDateString("en-MY", {
                            weekday: "short",
                            day: "numeric",
                            month: "short",
                          })}
                        </span>
                      </div>
                      <Badge 
                        className={record.status === "active" 
                          ? "bg-emerald-100 text-emerald-700 border-emerald-200" 
                          : "bg-slate-100 text-slate-600 border-slate-200"
                        }
                      >
                        {record.status === "active" ? "Working" : "Completed"}
                      </Badge>
                    </div>

                    <div className="p-4 space-y-4">
                      {/* Clock Times */}
                      <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-1">
                          <p className="text-xs text-slate-500 font-medium flex items-center gap-1">
                            <span className="w-2 h-2 rounded-full bg-emerald-500" />
                            Clock In
                          </p>
                          <p className="text-lg font-bold text-slate-900 dark:text-white">
                            {new Date(record.clock_in).toLocaleTimeString("en-MY", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })}
                          </p>
                        </div>
                        {record.clock_out && (
                          <div className="space-y-1">
                            <p className="text-xs text-slate-500 font-medium flex items-center gap-1">
                              <span className="w-2 h-2 rounded-full bg-rose-500" />
                              Clock Out
                            </p>
                            <p className="text-lg font-bold text-slate-900 dark:text-white">
                              {new Date(record.clock_out).toLocaleTimeString("en-MY", {
                                hour: "2-digit",
                                minute: "2-digit",
                              })}
                            </p>
                          </div>
                        )}
                      </div>

                      {/* Location */}
                      {record.clock_in_address && (
                        <div className="flex items-start gap-2 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                          <MapPin className="h-4 w-4 mt-0.5 text-blue-500 flex-shrink-0" />
                          <p className="text-xs text-slate-600 dark:text-slate-400">
                            {record.clock_in_address}
                          </p>
                        </div>
                      )}

                      {/* Total Hours */}
                      {record.total_hours && (
                        <div className="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                          <span className="text-sm text-slate-500 font-medium">Total Hours</span>
                          <span className="text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            {record.total_hours.toFixed(2)} hrs
                          </span>
                        </div>
                      )}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          ) : (
            <Card className="border-0 shadow-md">
              <CardContent className="py-12">
                <div className="text-center space-y-3">
                  <div className="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center">
                    <Clock className="h-8 w-8 text-slate-400" />
                  </div>
                  <div>
                    <p className="font-semibold text-slate-700 dark:text-slate-300">No records yet</p>
                    <p className="text-sm text-slate-500">Clock in to start tracking your attendance</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      </div>
    </div>
  )
}
