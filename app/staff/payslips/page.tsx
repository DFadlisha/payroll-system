import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Payslip } from "@/components/payslip"
import { FileText } from "lucide-react"

export default async function PayslipsPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase.from("profiles").select("*").eq("id", user.id).single()

  if (!profile) {
    redirect("/auth/login")
  }

  const { data: payrolls } = await supabase
    .from("payroll")
    .select("*")
    .eq("user_id", user.id)
    .order("year", { ascending: false })
    .order("month", { ascending: false })

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
            <FileText className="h-8 w-8" />
            My Payslips
          </h1>
          <p className="text-gray-600">View and download your payslips</p>
        </div>

        {payrolls && payrolls.length > 0 ? (
          <div className="space-y-6">
            {payrolls.map((payroll) => (
              <Payslip key={payroll.id} payroll={payroll} profile={profile} />
            ))}
          </div>
        ) : (
          <Card>
            <CardHeader>
              <CardTitle>No Payslips Available</CardTitle>
              <CardDescription>Your payslips will appear here once HR processes payroll</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground">
                Payslips are typically generated at the end of each month. Please check back later.
              </p>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
