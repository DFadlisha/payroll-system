import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { MobileNav } from "@/components/mobile-nav"

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

  // Allow all staff types (permanent, part-time, intern) to use mobile app
  if (!profile || profile.role === "hr") {
    redirect("/hr")
  }

  return (
    <div className="flex flex-col min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/30">
      {/* Main Content */}
      <main className="flex-1 overflow-auto pb-24">{children}</main>

      {/* Bottom Navigation */}
      <MobileNav />
    </div>
  )
}
