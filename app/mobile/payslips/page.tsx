import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { FileText, Download, DollarSign, Wallet, TrendingUp, Receipt, Info, ChevronRight } from "lucide-react"
import Link from "next/link"

export default async function MobilePayslipsPage() {
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

  // Get all payroll records
  const { data: payrolls } = await supabase
    .from("payroll")
    .select("*")
    .eq("user_id", user.id)
    .order("year", { ascending: false })
    .order("month", { ascending: false })

  const getStatusConfig = (status: string) => {
    switch (status) {
      case "draft":
        return { color: "bg-slate-100 text-slate-600 border-slate-200", dotColor: "bg-slate-400" }
      case "finalized":
        return { color: "bg-blue-100 text-blue-700 border-blue-200", dotColor: "bg-blue-500" }
      case "paid":
        return { color: "bg-emerald-100 text-emerald-700 border-emerald-200", dotColor: "bg-emerald-500" }
      default:
        return { color: "bg-slate-100 text-slate-600 border-slate-200", dotColor: "bg-slate-400" }
    }
  }

  const getMonthName = (month: number) => {
    const date = new Date(2000, month - 1, 1)
    return date.toLocaleDateString("en-MY", { month: "long" })
  }

  const getEmploymentTypeLabel = (type: string) => {
    switch (type) {
      case "intern":
        return "Intern"
      case "part-time":
        return "Part-Time Staff"
      case "permanent":
        return "Full-Time Staff"
      default:
        return type?.replace("-", " ")
    }
  }

  // Calculate total earnings this year
  const currentYear = new Date().getFullYear()
  const yearToDateEarnings = payrolls
    ?.filter((p: any) => p.year === currentYear && p.status === "paid")
    .reduce((sum: number, p: any) => sum + p.net_pay, 0) || 0

  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500" />
        <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2)_1px,transparent_1px)] bg-[length:20px_20px]" />
        
        <div className="relative px-5 pt-12 pb-8">
          <div className="flex items-center gap-3 mb-4">
            <div className="p-2.5 rounded-xl bg-white/20 backdrop-blur-sm">
              <Wallet className="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-white">Payslips</h1>
              <p className="text-emerald-100 text-sm">View and download payslips</p>
            </div>
          </div>

          {/* Year to Date Card */}
          <div className="bg-white/15 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-emerald-100 text-sm">Year to Date ({currentYear})</p>
                <p className="text-3xl font-bold text-white mt-1">
                  RM {yearToDateEarnings.toLocaleString("en-MY", { minimumFractionDigits: 2 })}
                </p>
              </div>
              <div className="p-3 rounded-xl bg-white/20">
                <TrendingUp className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 py-6 space-y-6 -mt-2">
        {/* Employee Info Card */}
        <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
          <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <p className="text-sm font-semibold text-slate-900 dark:text-white">Employee Details</p>
          </div>
          <CardContent className="p-4 space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-sm text-slate-500">Name</span>
              <span className="font-medium text-slate-900 dark:text-white">{profile?.full_name}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-slate-500">Employment Type</span>
              <Badge className={`font-medium ${
                profile?.employment_type === "permanent" 
                  ? "bg-indigo-100 text-indigo-700 border-indigo-200" 
                  : profile?.employment_type === "part-time"
                  ? "bg-purple-100 text-purple-700 border-purple-200"
                  : "bg-amber-100 text-amber-700 border-amber-200"
              } border`}>
                {getEmploymentTypeLabel(profile?.employment_type)}
              </Badge>
            </div>
            {profile?.epf_number && (
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">EPF Number</span>
                <span className="font-medium text-slate-900 dark:text-white font-mono">{profile.epf_number}</span>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Payslips List */}
        <div className="space-y-4">
          <h2 className="text-lg font-semibold text-slate-900 dark:text-white px-1">Payment History</h2>
          
          {payrolls && payrolls.length > 0 ? (
            <div className="space-y-3">
              {payrolls.map((payroll: any) => {
                const statusConfig = getStatusConfig(payroll.status)
                return (
                  <Card key={payroll.id} className="border-0 shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                    <CardContent className="p-0">
                      {/* Header */}
                      <div className="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 border-b border-slate-100 dark:border-slate-800">
                        <div className="flex items-center gap-3">
                          <div className="p-2 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/30">
                            <Receipt className="h-4 w-4 text-white" />
                          </div>
                          <div>
                            <p className="font-semibold text-slate-900 dark:text-white">
                              {getMonthName(payroll.month)} {payroll.year}
                            </p>
                            <p className="text-xs text-slate-500">
                              {payroll.regular_hours.toFixed(1)} hrs
                              {payroll.overtime_hours > 0 && ` + ${payroll.overtime_hours.toFixed(1)} OT`}
                            </p>
                          </div>
                        </div>
                        <Badge className={`${statusConfig.color} border font-medium capitalize`}>
                          {payroll.status}
                        </Badge>
                      </div>

                      <div className="p-4 space-y-4">
                        {/* Net Pay Highlight */}
                        <div className="flex items-center justify-between p-3 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                          <span className="font-medium text-slate-700 dark:text-slate-300">Net Pay</span>
                          <span className="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            RM {payroll.net_pay.toFixed(2)}
                          </span>
                        </div>

                        {/* Amount Details */}
                        <div className="grid grid-cols-2 gap-3">
                          <div className="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <p className="text-xs text-slate-500 font-medium">Gross Pay</p>
                            <p className="font-semibold text-slate-900 dark:text-white mt-1">
                              RM {payroll.gross_pay.toFixed(2)}
                            </p>
                          </div>
                          <div className="p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                            <p className="text-xs text-rose-600 font-medium">Deductions</p>
                            <p className="font-semibold text-rose-600 mt-1">
                              - RM {(payroll.epf_employee + payroll.socso_employee + payroll.eis_employee).toFixed(2)}
                            </p>
                          </div>
                        </div>

                        {/* Deduction Breakdown */}
                        <div className="grid grid-cols-3 gap-2">
                          <div className="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p className="text-[10px] text-slate-500 font-medium">EPF</p>
                            <p className="text-sm font-semibold text-slate-900 dark:text-white">
                              RM {payroll.epf_employee.toFixed(2)}
                            </p>
                          </div>
                          <div className="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p className="text-[10px] text-slate-500 font-medium">SOCSO</p>
                            <p className="text-sm font-semibold text-slate-900 dark:text-white">
                              RM {payroll.socso_employee.toFixed(2)}
                            </p>
                          </div>
                          <div className="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p className="text-[10px] text-slate-500 font-medium">EIS</p>
                            <p className="text-sm font-semibold text-slate-900 dark:text-white">
                              RM {payroll.eis_employee.toFixed(2)}
                            </p>
                          </div>
                        </div>

                        {/* View Full Payslip Button */}
                        {(payroll.status === "finalized" || payroll.status === "paid") && (
                          <Link
                            href={`/staff/payslips?payrollId=${payroll.id}`}
                            target="_blank"
                            className="block"
                          >
                            <Button 
                              variant="outline" 
                              className="w-full h-12 rounded-xl border-2 border-slate-200 dark:border-slate-700 font-semibold group hover:border-emerald-500 hover:text-emerald-600 transition-colors"
                            >
                              <Download className="mr-2 h-4 w-4" />
                              View Full Payslip
                              <ChevronRight className="ml-auto h-4 w-4 group-hover:translate-x-1 transition-transform" />
                            </Button>
                          </Link>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                )
              })}
            </div>
          ) : (
            <Card className="border-0 shadow-md">
              <CardContent className="py-12">
                <div className="text-center space-y-3">
                  <div className="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center">
                    <FileText className="h-8 w-8 text-slate-400" />
                  </div>
                  <div>
                    <p className="font-medium text-slate-900 dark:text-white">No payslips available</p>
                    <p className="text-sm text-slate-500 mt-1">Payslips will appear here once generated by HR</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Info Note */}
        <Card className="border-0 shadow-md overflow-hidden">
          <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4">
            <div className="flex items-start gap-3">
              <div className="p-2 rounded-xl bg-blue-100 dark:bg-blue-800">
                <Info className="h-4 w-4 text-blue-600 dark:text-blue-400" />
              </div>
              <div className="space-y-1">
                <p className="text-sm font-semibold text-blue-900 dark:text-blue-100">
                  Payslip Information
                </p>
                <p className="text-xs text-blue-700 dark:text-blue-300 leading-relaxed">
                  {profile?.employment_type === "intern" &&
                    "As an intern, you are not subject to EPF/SOCSO/EIS deductions."}
                  {profile?.employment_type === "part-time" &&
                    "Part-time staff may have different deduction rates based on working hours."}
                  {profile?.employment_type === "permanent" &&
                    "Your payslip includes all statutory deductions (EPF, SOCSO, EIS)."}
                </p>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  )
}
