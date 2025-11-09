import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Calendar } from "lucide-react"

interface TodayAttendanceProps {
  userId: string
}

export async function TodayAttendance({ userId }: TodayAttendanceProps) {
  const supabase = await createClient()

  // Get today's date range
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)

  const { data: attendanceRecords } = await supabase
    .from("attendance")
    .select("*")
    .eq("user_id", userId)
    .gte("clock_in", today.toISOString())
    .lt("clock_in", tomorrow.toISOString())
    .order("clock_in", { ascending: false })

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Calendar className="h-5 w-5" />
          Today&apos;s Attendance
        </CardTitle>
        <CardDescription>Your attendance records for today</CardDescription>
      </CardHeader>
      <CardContent>
        {attendanceRecords && attendanceRecords.length > 0 ? (
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Clock In</TableHead>
                  <TableHead>Clock Out</TableHead>
                  <TableHead>Total Hours</TableHead>
                  <TableHead>Overtime</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {attendanceRecords.map((record) => (
                  <TableRow key={record.id}>
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
            <p>No attendance records for today</p>
            <p className="text-sm mt-2">Clock in to start tracking your time</p>
          </div>
        )}
      </CardContent>
    </Card>
  )
}
