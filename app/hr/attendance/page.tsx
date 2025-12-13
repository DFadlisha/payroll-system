import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Clock, MapPin, Users } from "lucide-react"

export default async function HRAttendancePage() {
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

  // Get today's date
  const today = new Date().toISOString().split("T")[0]
  
  // Get all company employees
  const { data: companyProfiles } = await supabase
    .from("profiles")
    .select("id, full_name, email, employment_type")
    .eq("company_id", profile.company_id)

  const companyUserIds = companyProfiles?.map((p: any) => p.id) || []

  // Get today's attendance
  const { data: todayAttendance } = await supabase
    .from("attendance")
    .select("*")
    .in("user_id", companyUserIds)
    .gte("clock_in", `${today}T00:00:00`)
    .lte("clock_in", `${today}T23:59:59`)
    .order("clock_in", { ascending: false })

  // Get this week's attendance
  const startOfWeek = new Date()
  startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay())
  
  const { data: weekAttendance } = await supabase
    .from("attendance")
    .select("*")
    .in("user_id", companyUserIds)
    .gte("clock_in", startOfWeek.toISOString())
    .order("clock_in", { ascending: false })

  // Calculate stats
  const clockedInToday = todayAttendance?.filter((a: any) => !a.clock_out).length || 0
  const completedToday = todayAttendance?.filter((a: any) => a.clock_out).length || 0
  const totalHoursToday = todayAttendance?.reduce((sum: number, a: any) => sum + (a.total_hours || 0), 0) || 0

  // Map user IDs to employee data
  const employeeMap = companyProfiles?.reduce((acc: any, emp: any) => {
    acc[emp.id] = emp
    return acc
  }, {}) || {}

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <Clock className="h-8 w-8" />
            Attendance Overview
          </h1>
          <p className="text-gray-600">
            Today: {new Date().toLocaleDateString("en-MY", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}
          </p>
        </div>

        {/* Today's Stats */}
        <div className="grid gap-4 md:grid-cols-4 mb-6">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total Employees</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{companyProfiles?.length || 0}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Currently Clocked In</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{clockedInToday}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Completed Today</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{completedToday}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total Hours Today</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-purple-600">{totalHoursToday.toFixed(1)}h</div>
            </CardContent>
          </Card>
        </div>

        {/* Today's Attendance */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Today's Attendance</CardTitle>
            <CardDescription>Real-time attendance tracking</CardDescription>
          </CardHeader>
          <CardContent>
            {todayAttendance && todayAttendance.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Employee</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Clock In</TableHead>
                      <TableHead>Clock In Location</TableHead>
                      <TableHead>Clock Out</TableHead>
                      <TableHead>Clock Out Location</TableHead>
                      <TableHead>Hours</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {todayAttendance.map((attendance: any) => {
                      const employee = employeeMap[attendance.user_id]
                      return (
                        <TableRow key={attendance.id}>
                          <TableCell>
                            <div className="font-medium">{employee?.full_name || "Unknown"}</div>
                            <div className="text-xs text-muted-foreground">{employee?.email}</div>
                          </TableCell>
                          <TableCell>
                            <Badge variant="outline" className="capitalize text-xs">
                              {employee?.employment_type === "permanent" ? "Full-Time" : (employee?.employment_type || "N/A").replace("-", " ")}
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {new Date(attendance.clock_in).toLocaleTimeString("en-MY", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })}
                          </TableCell>
                          <TableCell>
                            {attendance.clock_in_address ? (
                              <div className="flex items-center gap-1 text-xs text-muted-foreground max-w-[150px]">
                                <MapPin className="h-3 w-3 flex-shrink-0" />
                                <span className="truncate">{attendance.clock_in_address}</span>
                              </div>
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </TableCell>
                          <TableCell>
                            {attendance.clock_out ? (
                              new Date(attendance.clock_out).toLocaleTimeString("en-MY", {
                                hour: "2-digit",
                                minute: "2-digit",
                              })
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </TableCell>
                          <TableCell>
                            {attendance.clock_out_address ? (
                              <div className="flex items-center gap-1 text-xs text-muted-foreground max-w-[150px]">
                                <MapPin className="h-3 w-3 flex-shrink-0" />
                                <span className="truncate">{attendance.clock_out_address}</span>
                              </div>
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </TableCell>
                          <TableCell>
                            {attendance.total_hours ? (
                              <span className="font-medium">{attendance.total_hours.toFixed(2)}h</span>
                            ) : (
                              <span className="text-xs text-gray-400">-</span>
                            )}
                          </TableCell>
                          <TableCell>
                            <Badge
                              variant={attendance.clock_out ? "default" : "secondary"}
                              className={attendance.clock_out ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800"}
                            >
                              {attendance.clock_out ? "Completed" : "Active"}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      )
                    })}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <Clock className="h-12 w-12 mx-auto mb-3 opacity-50" />
                <p>No attendance records for today</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Employees Not Clocked In */}
        <Card>
          <CardHeader>
            <CardTitle>Not Clocked In Today</CardTitle>
            <CardDescription>Employees who haven't clocked in yet</CardDescription>
          </CardHeader>
          <CardContent>
            {(() => {
              const clockedInUserIds = todayAttendance?.map((a: any) => a.user_id) || []
              const notClockedIn = companyProfiles?.filter((emp: any) => !clockedInUserIds.includes(emp.id)) || []
              
              if (notClockedIn.length === 0) {
                return (
                  <div className="text-center py-4 text-green-600">
                    <p>All employees have clocked in today! 🎉</p>
                  </div>
                )
              }
              
              return (
                <div className="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                  {notClockedIn.map((emp: any) => (
                    <div key={emp.id} className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                      <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <Users className="h-5 w-5 text-gray-500" />
                      </div>
                      <div>
                        <p className="font-medium text-sm">{emp.full_name}</p>
                        <p className="text-xs text-muted-foreground capitalize">
                          {emp.employment_type === "permanent" ? "Full-Time" : (emp.employment_type || "Staff").replace("-", " ")}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              )
            })()}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
