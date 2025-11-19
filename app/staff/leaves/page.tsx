import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { LeaveRequestForm } from "@/components/leave-request-form"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Calendar } from "lucide-react"

export default async function LeavesPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: leaves } = await supabase
    .from("leaves")
    .select("*")
    .eq("user_id", user.id)
    .order("created_at", { ascending: false })

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <Calendar className="h-8 w-8" />
            Leave Management
          </h1>
          <p className="text-gray-600">Submit and track your leave requests</p>
        </div>

        <div className="grid gap-6 lg:grid-cols-3 mb-6">
          <Card className="lg:col-span-1">
            <CardHeader>
              <CardTitle>Request Leave</CardTitle>
              <CardDescription>Submit a new leave application</CardDescription>
            </CardHeader>
            <CardContent>
              <LeaveRequestForm userId={user.id} />
            </CardContent>
          </Card>

          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>My Leave Requests</CardTitle>
              <CardDescription>View your leave application history</CardDescription>
            </CardHeader>
            <CardContent>
              {leaves && leaves.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Type</TableHead>
                        <TableHead>Start Date</TableHead>
                        <TableHead>End Date</TableHead>
                        <TableHead>Days</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {leaves.map((leave: any) => (
                        <TableRow key={leave.id}>
                          <TableCell className="capitalize">{leave.leave_type}</TableCell>
                          <TableCell>
                            {new Date(leave.start_date).toLocaleDateString("en-MY", {
                              year: "numeric",
                              month: "short",
                              day: "numeric",
                            })}
                          </TableCell>
                          <TableCell>
                            {new Date(leave.end_date).toLocaleDateString("en-MY", {
                              year: "numeric",
                              month: "short",
                              day: "numeric",
                            })}
                          </TableCell>
                          <TableCell>{leave.days}</TableCell>
                          <TableCell>
                            <Badge
                              variant={
                                leave.status === "approved"
                                  ? "default"
                                  : leave.status === "rejected"
                                    ? "destructive"
                                    : "secondary"
                              }
                            >
                              {leave.status.toUpperCase()}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  <p>No leave requests yet</p>
                  <p className="text-sm mt-2">Submit your first leave request using the form</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
