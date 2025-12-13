import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Calendar, Clock, CheckCircle2, XCircle, AlertCircle, CalendarDays } from "lucide-react"
import { MobileLeaveForm } from "@/components/mobile-leave-form"

export default async function MobileLeavesPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()

  if (!user) {
    redirect("/auth/login")
  }

  // Get user profile for employment type
  const { data: profile } = await supabase
    .from("profiles")
    .select("employment_type, created_at")
    .eq("id", user.id)
    .single()

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

  const getStatusConfig = (status: string) => {
    switch (status) {
      case "pending":
        return { 
          icon: Clock, 
          color: "bg-amber-100 text-amber-700 border-amber-200",
          dotColor: "bg-amber-500"
        }
      case "approved":
        return { 
          icon: CheckCircle2, 
          color: "bg-emerald-100 text-emerald-700 border-emerald-200",
          dotColor: "bg-emerald-500"
        }
      case "rejected":
        return { 
          icon: XCircle, 
          color: "bg-rose-100 text-rose-700 border-rose-200",
          dotColor: "bg-rose-500"
        }
      default:
        return { 
          icon: AlertCircle, 
          color: "bg-slate-100 text-slate-700 border-slate-200",
          dotColor: "bg-slate-500"
        }
    }
  }

  const renderLeaveCard = (leave: any) => {
    const statusConfig = getStatusConfig(leave.status)
    const StatusIcon = statusConfig.icon
    
    return (
      <Card key={leave.id} className="border-0 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
        <CardContent className="p-0">
          {/* Header */}
          <div className="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 border-b border-slate-100 dark:border-slate-800">
            <div className="flex items-center gap-3">
              <div className={`p-2 rounded-xl ${leave.status === "pending" ? "bg-amber-100" : leave.status === "approved" ? "bg-emerald-100" : "bg-rose-100"}`}>
                <StatusIcon className={`h-4 w-4 ${leave.status === "pending" ? "text-amber-600" : leave.status === "approved" ? "text-emerald-600" : "text-rose-600"}`} />
              </div>
              <div>
                <p className="font-semibold text-slate-900 dark:text-white capitalize">
                  {leave.leave_type.replace("_", " ")} Leave
                </p>
                <p className="text-xs text-slate-500">{leave.days} day(s)</p>
              </div>
            </div>
            <Badge className={`${statusConfig.color} border font-medium capitalize`}>
              {leave.status}
            </Badge>
          </div>

          <div className="p-4 space-y-4">
            {/* Date Range */}
            <div className="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
              <Calendar className="h-4 w-4 text-indigo-500" />
              <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                {new Date(leave.start_date).toLocaleDateString("en-MY", {
                  month: "short",
                  day: "numeric",
                })}
                {" — "}
                {new Date(leave.end_date).toLocaleDateString("en-MY", {
                  month: "short",
                  day: "numeric",
                  year: "numeric",
                })}
              </span>
            </div>

            {/* Reason */}
            <div>
              <p className="text-xs text-slate-500 font-medium mb-1">Reason</p>
              <p className="text-sm text-slate-700 dark:text-slate-300">{leave.reason}</p>
            </div>

            {/* Review Info */}
            {leave.reviewed_at && (
              <div className="pt-3 border-t border-slate-100 dark:border-slate-800">
                <p className="text-xs text-slate-500">
                  Reviewed on {new Date(leave.reviewed_at).toLocaleDateString("en-MY", {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                  })}
                </p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    )
  }

  const renderEmptyState = (icon: any, message: string) => {
    const Icon = icon
    return (
      <Card className="border-0 shadow-md">
        <CardContent className="py-12">
          <div className="text-center space-y-3">
            <div className="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center">
              <Icon className="h-8 w-8 text-slate-400" />
            </div>
            <p className="text-sm text-slate-500">{message}</p>
          </div>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500" />
        <div className="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.07)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50" />
        
        <div className="relative px-5 pt-12 pb-8">
          <div className="flex items-center gap-3 mb-2">
            <div className="p-2.5 rounded-xl bg-white/20 backdrop-blur-sm">
              <CalendarDays className="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-white">Leave Management</h1>
              <p className="text-amber-100 text-sm">Apply and track your leaves</p>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 py-6 space-y-6 -mt-2">
        {/* Apply Leave Form */}
        <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
          <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <div className="flex items-center gap-3">
              <div className="p-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/30">
                <Calendar className="h-5 w-5 text-white" />
              </div>
              <div>
                <h3 className="font-semibold text-slate-900 dark:text-white">Apply for Leave</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400">Submit a new request</p>
              </div>
            </div>
          </div>
          <CardContent className="p-5">
            <MobileLeaveForm userId={user.id} employmentType={profile?.employment_type} internStartDate={profile?.created_at} />
          </CardContent>
        </Card>

        {/* Leave History */}
        <div className="space-y-4">
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white px-1">Leave History</h2>
          
          <Tabs defaultValue="all" className="w-full">
            <TabsList className="grid w-full grid-cols-4 h-12 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
              <TabsTrigger value="all" className="rounded-lg text-xs font-semibold data-[state=active]:bg-white data-[state=active]:shadow-md">
                All
              </TabsTrigger>
              <TabsTrigger value="pending" className="rounded-lg text-xs font-semibold data-[state=active]:bg-white data-[state=active]:shadow-md">
                Pending
              </TabsTrigger>
              <TabsTrigger value="approved" className="rounded-lg text-xs font-semibold data-[state=active]:bg-white data-[state=active]:shadow-md">
                Approved
              </TabsTrigger>
              <TabsTrigger value="rejected" className="rounded-lg text-xs font-semibold data-[state=active]:bg-white data-[state=active]:shadow-md">
                Rejected
              </TabsTrigger>
            </TabsList>

            <TabsContent value="all" className="space-y-3 mt-4">
              {leaves && leaves.length > 0 ? (
                leaves.map(renderLeaveCard)
              ) : (
                renderEmptyState(Calendar, "No leave requests found")
              )}
            </TabsContent>

            <TabsContent value="pending" className="space-y-3 mt-4">
              {pendingLeaves.length > 0 ? (
                pendingLeaves.map(renderLeaveCard)
              ) : (
                renderEmptyState(Clock, "No pending requests")
              )}
            </TabsContent>

            <TabsContent value="approved" className="space-y-3 mt-4">
              {approvedLeaves.length > 0 ? (
                approvedLeaves.map(renderLeaveCard)
              ) : (
                renderEmptyState(CheckCircle2, "No approved requests")
              )}
            </TabsContent>

            <TabsContent value="rejected" className="space-y-3 mt-4">
              {rejectedLeaves.length > 0 ? (
                rejectedLeaves.map(renderLeaveCard)
              ) : (
                renderEmptyState(XCircle, "No rejected requests")
              )}
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </div>
  )
}
