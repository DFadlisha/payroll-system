import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Calendar, Clock, CheckCircle2, XCircle, AlertCircle } from "lucide-react"
import { MobileLeaveForm } from "@/components/mobile-leave-form"

export default async function MobileLeavesPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()

  if (!user) {
    redirect("/auth/login")
  }

  // Get all leave requests
  const { data: leaves } = await supabase
    .from("leaves")
    .select(`
      *,
      reviewer:reviewed_by(full_name)
    `)
    .eq("user_id", user.id)
    .order("created_at", { ascending: false })

  const pendingLeaves = leaves?.filter((leave: any) => leave.status === "pending") || []
  const approvedLeaves = leaves?.filter((leave: any) => leave.status === "approved") || []
  const rejectedLeaves = leaves?.filter((leave: any) => leave.status === "rejected") || []

  const getStatusIcon = (status: string) => {
    switch (status) {
      case "pending":
        return <Clock className="h-4 w-4" />
      case "approved":
        return <CheckCircle2 className="h-4 w-4" />
      case "rejected":
        return <XCircle className="h-4 w-4" />
      default:
        return <AlertCircle className="h-4 w-4" />
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "pending":
        return <Badge variant="outline" className="bg-yellow-50 text-yellow-700 border-yellow-200">{status}</Badge>
      case "approved":
        return <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">{status}</Badge>
      case "rejected":
        return <Badge variant="outline" className="bg-red-50 text-red-700 border-red-200">{status}</Badge>
      default:
        return <Badge variant="outline">{status}</Badge>
    }
  }

  const renderLeaveCard = (leave: any) => (
    <Card key={leave.id}>
      <CardContent className="pt-6">
        <div className="space-y-3">
          {/* Header */}
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-2">
              {getStatusIcon(leave.status)}
              <div>
                <p className="font-medium capitalize">{leave.leave_type.replace("_", " ")} Leave</p>
                <p className="text-sm text-muted-foreground">{leave.days} day(s)</p>
              </div>
            </div>
            {getStatusBadge(leave.status)}
          </div>

          {/* Date Range */}
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Calendar className="h-4 w-4" />
            <span>
              {new Date(leave.start_date).toLocaleDateString("en-MY", {
                month: "short",
                day: "numeric",
                year: "numeric",
              })}
              {" - "}
              {new Date(leave.end_date).toLocaleDateString("en-MY", {
                month: "short",
                day: "numeric",
                year: "numeric",
              })}
            </span>
          </div>

          {/* Reason */}
          <div className="pt-2 border-t">
            <p className="text-sm text-muted-foreground">Reason:</p>
            <p className="text-sm mt-1">{leave.reason}</p>
          </div>

          {/* Review Info */}
          {leave.reviewed_at && (
            <div className="pt-2 border-t text-xs text-muted-foreground">
              Reviewed on{" "}
              {new Date(leave.reviewed_at).toLocaleDateString("en-MY", {
                month: "short",
                day: "numeric",
                year: "numeric",
              })}
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  )

  return (
    <div className="container max-w-2xl px-4 py-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Leave Management</h1>
        <p className="text-muted-foreground">Apply and track your leave requests</p>
      </div>

      {/* Apply Leave Form */}
      <Card>
        <CardHeader>
          <CardTitle>Apply for Leave</CardTitle>
          <CardDescription>Submit a new leave request</CardDescription>
        </CardHeader>
        <CardContent>
          <MobileLeaveForm userId={user.id} />
        </CardContent>
      </Card>

      {/* Leave History */}
      <div className="space-y-3">
        <h2 className="text-lg font-semibold">Leave History</h2>
        <Tabs defaultValue="all" className="w-full">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="all">All</TabsTrigger>
            <TabsTrigger value="pending">Pending</TabsTrigger>
            <TabsTrigger value="approved">Approved</TabsTrigger>
            <TabsTrigger value="rejected">Rejected</TabsTrigger>
          </TabsList>

          <TabsContent value="all" className="space-y-3 mt-4">
            {leaves && leaves.length > 0 ? (
              leaves.map(renderLeaveCard)
            ) : (
              <Card>
                <CardContent className="pt-6">
                  <div className="text-center py-8">
                    <Calendar className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p className="text-muted-foreground">No leave requests found</p>
                  </div>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          <TabsContent value="pending" className="space-y-3 mt-4">
            {pendingLeaves.length > 0 ? (
              pendingLeaves.map(renderLeaveCard)
            ) : (
              <Card>
                <CardContent className="pt-6">
                  <div className="text-center py-8">
                    <Clock className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p className="text-muted-foreground">No pending requests</p>
                  </div>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          <TabsContent value="approved" className="space-y-3 mt-4">
            {approvedLeaves.length > 0 ? (
              approvedLeaves.map(renderLeaveCard)
            ) : (
              <Card>
                <CardContent className="pt-6">
                  <div className="text-center py-8">
                    <CheckCircle2 className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p className="text-muted-foreground">No approved requests</p>
                  </div>
                </CardContent>
              </Card>
            )}
          </TabsContent>

          <TabsContent value="rejected" className="space-y-3 mt-4">
            {rejectedLeaves.length > 0 ? (
              rejectedLeaves.map(renderLeaveCard)
            ) : (
              <Card>
                <CardContent className="pt-6">
                  <div className="text-center py-8">
                    <XCircle className="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                    <p className="text-muted-foreground">No rejected requests</p>
                  </div>
                </CardContent>
              </Card>
            )}
          </TabsContent>
        </Tabs>
      </div>
    </div>
  )
}
