import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Calendar } from "lucide-react"

export default async function AttendanceHistoryPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: attendanceRecords } = await supabase
    .from("attendance")
    .select("*")
    .eq("user_id", user.id)
    .order("clock_in", { ascending: false })
    .limit(50)

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <Calendar className="h-8 w-8" />
            Attendance History
          </h1>
          <p className="text-gray-600">View your complete attendance records</p>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Recent Attendance</CardTitle>
            <CardDescription>Your last 50 attendance records</CardDescription>
          </CardHeader>
          <CardContent>
            {attendanceRecords && attendanceRecords.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Date</TableHead>
                      <TableHead>Clock In</TableHead>
                      <TableHead>Clock Out</TableHead>
                      <TableHead>Total Hours</TableHead>
                      <TableHead>Overtime</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {attendanceRecords.map((record: any) => (
                      <TableRow key={record.id}>
                        <TableCell>
                          {new Date(record.clock_in).toLocaleDateString("en-MY", {
                            year: "numeric",
                            month: "short",
                            day: "numeric",
                          })}
                        </TableCell>
                        <TableCell>
                          {new Date(record.clock_in).toLocaleTimeString("en-MY", {
                            hour: "2-digit",
                            minute: "2-digit",
                          })}
                        </TableCell>
                        <TableCell>
                          {record.clock_out
                            ? new Date(record.clock_out).toLocaleTimeString("en-MY", {
                                hour: "2-digit",
                                minute: "2-digit",
                              })
                            : "-"}
                        </TableCell>
                        <TableCell>{record.total_hours ? `${record.total_hours.toFixed(2)}h` : "-"}</TableCell>
                        <TableCell>{record.overtime_hours ? `${record.overtime_hours.toFixed(2)}h` : "0h"}</TableCell>
                        <TableCell>
                          <Badge variant={record.status === "active" ? "default" : "secondary"}>
                            {record.status === "active" ? "Active" : "Completed"}
                          </Badge>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <p>No attendance records found</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
