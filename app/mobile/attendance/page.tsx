import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Clock, MapPin, Calendar } from "lucide-react"
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
    <div className="container max-w-2xl px-4 py-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Attendance</h1>
        <p className="text-muted-foreground">Track your work hours with location</p>
      </div>

      {/* Clock In/Out Section */}
      <Card>
        <CardHeader>
          <CardTitle>Clock In / Out</CardTitle>
          <CardDescription>Your location will be recorded</CardDescription>
        </CardHeader>
        <CardContent>
          <MobileClockInOut userId={user.id} />
        </CardContent>
      </Card>

      {/* Attendance History */}
      <div className="space-y-3">
        <h2 className="text-lg font-semibold">Recent Attendance</h2>
        {attendanceRecords && attendanceRecords.length > 0 ? (
          <div className="space-y-3">
            {attendanceRecords.map((record: any) => (
              <Card key={record.id}>
                <CardContent className="pt-6">
                  <div className="space-y-3">
                    {/* Date */}
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                        <span className="font-medium">
                          {new Date(record.clock_in).toLocaleDateString("en-MY", {
                            weekday: "short",
                            year: "numeric",
                            month: "short",
                            day: "numeric",
                          })}
                        </span>
                      </div>
                      <Badge variant={record.status === "active" ? "default" : "secondary"}>
                        {record.status}
                      </Badge>
                    </div>

                    {/* Clock In */}
                    <div className="space-y-2">
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-muted-foreground">Clock In</span>
                        <span className="font-medium">
                          {new Date(record.clock_in).toLocaleTimeString("en-MY", {
                            hour: "2-digit",
                            minute: "2-digit",
                          })}
                        </span>
                      </div>
                      {record.clock_in_address && (
                        <div className="flex items-start gap-2 pl-4">
                          <MapPin className="h-3 w-3 mt-0.5 text-muted-foreground flex-shrink-0" />
                          <span className="text-xs text-muted-foreground">
                            {record.clock_in_address}
                          </span>
                        </div>
                      )}
                    </div>

                    {/* Clock Out */}
                    {record.clock_out && (
                      <div className="space-y-2">
                        <div className="flex items-center justify-between">
                          <span className="text-sm text-muted-foreground">Clock Out</span>
                          <span className="font-medium">
                            {new Date(record.clock_out).toLocaleTimeString("en-MY", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })}
                          </span>
                        </div>
                        {record.clock_out_address && (
                          <div className="flex items-start gap-2 pl-4">
                            <MapPin className="h-3 w-3 mt-0.5 text-muted-foreground flex-shrink-0" />
                            <span className="text-xs text-muted-foreground">
                              {record.clock_out_address}
                            </span>
                          </div>
                        )}
                      </div>
                    )}

                    {/* Total Hours */}
                    {record.total_hours && (
                      <div className="flex items-center justify-between pt-2 border-t">
                        <span className="text-sm font-medium">Total Hours</span>
                        <span className="text-lg font-bold">
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
          <Card>
            <CardContent className="pt-6">
              <div className="text-center py-8">
                <Clock className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                <p className="text-muted-foreground">No attendance records found</p>
                <p className="text-sm text-muted-foreground mt-1">
                  Clock in to start tracking your attendance
                </p>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
