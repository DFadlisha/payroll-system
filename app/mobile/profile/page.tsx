import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { User, Mail, Briefcase, DollarSign, Hash, LogOut } from "lucide-react"

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

  return (
    <div className="container max-w-2xl px-4 py-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Profile</h1>
        <p className="text-muted-foreground">Your personal information</p>
      </div>

      {/* Profile Info Card */}
      <Card>
        <CardHeader>
          <div className="flex items-center gap-4">
            <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
              <User className="h-8 w-8 text-primary" />
            </div>
            <div>
              <CardTitle>{profile?.full_name}</CardTitle>
              <CardDescription>{user.email}</CardDescription>
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Personal Information */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Personal Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center gap-3">
            <Mail className="h-5 w-5 text-muted-foreground" />
            <div className="flex-1">
              <p className="text-sm text-muted-foreground">Email</p>
              <p className="font-medium">{user.email}</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <User className="h-5 w-5 text-muted-foreground" />
            <div className="flex-1">
              <p className="text-sm text-muted-foreground">Full Name</p>
              <p className="font-medium">{profile?.full_name}</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <Briefcase className="h-5 w-5 text-muted-foreground" />
            <div className="flex-1">
              <p className="text-sm text-muted-foreground">Employment Type</p>
              <Badge variant="outline" className="mt-1 capitalize">
                {profile?.employment_type?.replace("-", " ")}
              </Badge>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <DollarSign className="h-5 w-5 text-muted-foreground" />
            <div className="flex-1">
              <p className="text-sm text-muted-foreground">Basic Salary</p>
              <p className="font-medium">RM {profile?.basic_salary?.toFixed(2)}</p>
            </div>
          </div>

          {profile?.hourly_rate && (
            <div className="flex items-center gap-3">
              <DollarSign className="h-5 w-5 text-muted-foreground" />
              <div className="flex-1">
                <p className="text-sm text-muted-foreground">Hourly Rate</p>
                <p className="font-medium">RM {profile.hourly_rate.toFixed(2)}/hr</p>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Statutory Information */}
      {(profile?.epf_number || profile?.socso_number) && (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Statutory Information</CardTitle>
            <CardDescription>Your EPF, SOCSO, and other statutory details</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {profile?.epf_number && (
              <div className="flex items-center gap-3">
                <Hash className="h-5 w-5 text-muted-foreground" />
                <div className="flex-1">
                  <p className="text-sm text-muted-foreground">EPF Number</p>
                  <p className="font-medium font-mono">{profile.epf_number}</p>
                </div>
              </div>
            )}

            {profile?.socso_number && (
              <div className="flex items-center gap-3">
                <Hash className="h-5 w-5 text-muted-foreground" />
                <div className="flex-1">
                  <p className="text-sm text-muted-foreground">SOCSO Number</p>
                  <p className="font-medium font-mono">{profile.socso_number}</p>
                </div>
              </div>
            )}

            {profile?.citizenship_status && (
              <div className="flex items-center gap-3">
                <User className="h-5 w-5 text-muted-foreground" />
                <div className="flex-1">
                  <p className="text-sm text-muted-foreground">Citizenship Status</p>
                  <Badge variant="outline" className="mt-1 capitalize">
                    {profile.citizenship_status.replace("_", " ")}
                  </Badge>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {/* Info Note */}
      {profile?.employment_type === "intern" && (
        <Card className="bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800">
          <CardContent className="pt-6">
            <p className="text-sm text-blue-900 dark:text-blue-100">
              <strong>Note for Interns:</strong> As an intern, you are not subject to EPF, SOCSO,
              and EIS deductions. Your net pay will be the same as your gross pay.
            </p>
          </CardContent>
        </Card>
      )}

      {/* Logout Button */}
      <form action="/auth/signout" method="post">
        <Button variant="outline" className="w-full" size="lg" type="submit">
          <LogOut className="mr-2 h-5 w-5" />
          Sign Out
        </Button>
      </form>
    </div>
  )
}
