import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { User, Mail, Briefcase, DollarSign, Hash, Shield, Building2 } from "lucide-react"
import { MobileSignOutButton } from "@/components/mobile-signout-button"

export default async function MobileProfilePage() {
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

  const getEmploymentBadge = (type: string) => {
    switch (type) {
      case "intern":
        return { label: "Intern", color: "bg-amber-100 text-amber-700 border-amber-200" }
      case "part-time":
        return { label: "Part-Time Staff", color: "bg-blue-100 text-blue-700 border-blue-200" }
      case "permanent":
        return { label: "Full-Time Staff", color: "bg-emerald-100 text-emerald-700 border-emerald-200" }
      default:
        return { label: type, color: "bg-slate-100 text-slate-700 border-slate-200" }
    }
  }

  const employmentBadge = getEmploymentBadge(profile?.employment_type || "")

  return (
    <div className="min-h-screen">
      {/* Profile Header */}
      <div className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500" />
        <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.2)_1px,transparent_1px)] bg-[length:20px_20px]" />
        
        <div className="relative px-5 pt-12 pb-20 text-center">
          {/* Avatar */}
          <div className="relative inline-block mb-4">
            <div className="w-24 h-24 rounded-3xl bg-white/20 backdrop-blur-sm flex items-center justify-center border-2 border-white/40 shadow-xl">
              <span className="text-4xl font-bold text-white">
                {profile?.full_name?.charAt(0) || "U"}
              </span>
            </div>
            <div className="absolute -bottom-1 -right-1 w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg">
              <Shield className="h-4 w-4 text-white" />
            </div>
          </div>
          
          <h1 className="text-2xl font-bold text-white mb-1">{profile?.full_name}</h1>
          <p className="text-indigo-200 text-sm mb-3">{user.email}</p>
          <Badge className={`${employmentBadge.color} border font-medium`}>
            {employmentBadge.label}
          </Badge>
        </div>
      </div>

      {/* Content */}
      <div className="px-5 py-6 space-y-4 -mt-10">
        {/* Personal Information Card */}
        <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
          <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-5 py-3 border-b border-slate-200 dark:border-slate-700">
            <h3 className="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
              <User className="h-4 w-4 text-indigo-500" />
              Personal Information
            </h3>
          </div>
          <CardContent className="p-0">
            <div className="divide-y divide-slate-100 dark:divide-slate-800">
              <div className="flex items-center gap-4 px-5 py-4">
                <div className="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950">
                  <Mail className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">Email Address</p>
                  <p className="font-semibold text-slate-900 dark:text-white truncate">{user.email}</p>
                </div>
              </div>

              <div className="flex items-center gap-4 px-5 py-4">
                <div className="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950">
                  <User className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">Full Name</p>
                  <p className="font-semibold text-slate-900 dark:text-white">{profile?.full_name}</p>
                </div>
              </div>

              <div className="flex items-center gap-4 px-5 py-4">
                <div className="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950">
                  <Briefcase className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">Employment Type</p>
                  <Badge className={`${employmentBadge.color} border font-medium mt-1`}>
                    {employmentBadge.label}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Salary Information Card */}
        <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
          <div className="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/50 dark:to-teal-950/50 px-5 py-3 border-b border-emerald-200/50 dark:border-emerald-800/50">
            <h3 className="font-semibold text-emerald-900 dark:text-emerald-100 flex items-center gap-2">
              <DollarSign className="h-4 w-4 text-emerald-600" />
              Compensation
            </h3>
          </div>
          <CardContent className="p-0">
            <div className="divide-y divide-slate-100 dark:divide-slate-800">
              <div className="flex items-center justify-between px-5 py-4">
                <div>
                  <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">Basic Salary</p>
                  <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                    RM {profile?.basic_salary?.toFixed(2) || "0.00"}
                  </p>
                </div>
                <div className="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950">
                  <DollarSign className="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                </div>
              </div>

              {profile?.hourly_rate && (
                <div className="flex items-center justify-between px-5 py-4">
                  <div>
                    <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">Hourly Rate</p>
                    <p className="text-xl font-bold text-blue-600 dark:text-blue-400">
                      RM {profile.hourly_rate.toFixed(2)}/hr
                    </p>
                  </div>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Statutory Information */}
        {(profile?.epf_number || profile?.socso_number) && (
          <Card className="border-0 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 overflow-hidden">
            <div className="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-5 py-3 border-b border-slate-200 dark:border-slate-700">
              <h3 className="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                <Building2 className="h-4 w-4 text-indigo-500" />
                Statutory Information
              </h3>
            </div>
            <CardContent className="p-0">
              <div className="divide-y divide-slate-100 dark:divide-slate-800">
                {profile?.epf_number && (
                  <div className="flex items-center gap-4 px-5 py-4">
                    <div className="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950">
                      <Hash className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div className="flex-1">
                      <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">EPF Number</p>
                      <p className="font-mono font-semibold text-slate-900 dark:text-white">{profile.epf_number}</p>
                    </div>
                  </div>
                )}

                {profile?.socso_number && (
                  <div className="flex items-center gap-4 px-5 py-4">
                    <div className="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950">
                      <Hash className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div className="flex-1">
                      <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">SOCSO Number</p>
                      <p className="font-mono font-semibold text-slate-900 dark:text-white">{profile.socso_number}</p>
                    </div>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Info Note for Interns */}
        {profile?.employment_type === "intern" && (
          <Card className="border-0 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/50 dark:to-indigo-950/50 shadow-lg shadow-blue-100/50 dark:shadow-blue-900/20">
            <CardContent className="p-5">
              <div className="flex gap-3">
                <div className="p-2 rounded-xl bg-blue-500/10 h-fit">
                  <Shield className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                  <p className="font-semibold text-blue-900 dark:text-blue-100 mb-1">Note for Interns</p>
                  <p className="text-sm text-blue-700 dark:text-blue-300">
                    As an intern, you are not subject to EPF, SOCSO, and EIS deductions. Your net pay will be the same as your gross pay.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Sign Out Button */}
        <div className="pt-4">
          <MobileSignOutButton />
        </div>
      </div>
    </div>
  )
}
