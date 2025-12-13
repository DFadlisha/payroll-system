import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Home, Clock, Calendar, FileText, User } from "lucide-react"
import Link from "next/link"

export default async function MobileLayout({
  children,
}: {
  children: React.ReactNode
}) {
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

  if (!profile || profile.role !== "staff") {
    redirect("/staff")
  }

  return (
    <div className="flex flex-col h-screen bg-background">
      {/* Header */}
      <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div className="container flex h-14 items-center px-4">
          <div className="flex items-center space-x-2">
            <Clock className="h-6 w-6" />
            <span className="font-bold">Mobile Payroll</span>
          </div>
          <div className="ml-auto">
            <span className="text-sm text-muted-foreground">
              {profile.employment_type === "intern" && "Intern"}
              {profile.employment_type === "part-time" && "Part-Time"}
              {profile.employment_type === "permanent" && "Staff"}
              {profile.employment_type === "contract" && "Contract"}
            </span>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-1 overflow-auto pb-16">{children}</main>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-0 right-0 z-50 border-t bg-background">
        <div className="grid grid-cols-5 gap-1 px-2 py-2">
          <Link
            href="/mobile"
            className="flex flex-col items-center justify-center gap-1 rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <Home className="h-5 w-5" />
            <span className="text-xs">Home</span>
          </Link>
          <Link
            href="/mobile/attendance"
            className="flex flex-col items-center justify-center gap-1 rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <Clock className="h-5 w-5" />
            <span className="text-xs">Attendance</span>
          </Link>
          <Link
            href="/mobile/leaves"
            className="flex flex-col items-center justify-center gap-1 rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <Calendar className="h-5 w-5" />
            <span className="text-xs">Leaves</span>
          </Link>
          <Link
            href="/mobile/payslips"
            className="flex flex-col items-center justify-center gap-1 rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <FileText className="h-5 w-5" />
            <span className="text-xs">Payslips</span>
          </Link>
          <Link
            href="/mobile/profile"
            className="flex flex-col items-center justify-center gap-1 rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <User className="h-5 w-5" />
            <span className="text-xs">Profile</span>
          </Link>
        </div>
      </nav>
    </div>
  )
}
