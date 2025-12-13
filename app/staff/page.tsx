import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"

export default async function StaffDashboard() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  
  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase.from("profiles").select("role").eq("id", user.id).single()

  if (!profile) {
    redirect("/auth/login")
  }

  // HR should go to /hr dashboard
  if (profile.role === "hr") {
    redirect("/hr")
  }

  // Staff and Interns should use mobile app - redirect to /mobile
  redirect("/mobile")
}
