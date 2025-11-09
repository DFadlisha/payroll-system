import { createClient } from "@/lib/supabase/server"
import Image from "next/image"

export async function CompanyHeader() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()

  if (!user) return null

  const { data: profile } = await supabase
    .from("profiles")
    .select("company_id, companies(name, logo_url)")
    .eq("id", user.id)
    .single()

  if (!profile?.companies) return null

  const company = profile.companies as { name: string; logo_url: string | null }

  return (
    <div className="flex items-center gap-3 mb-6 p-4 bg-white rounded-lg shadow-sm border">
      {company.logo_url && (
        <div className="relative w-12 h-12 flex-shrink-0">
          <Image src={company.logo_url || "/placeholder.svg"} alt={company.name} fill className="object-contain" />
        </div>
      )}
      <div>
        <p className="text-sm text-muted-foreground">Company</p>
        <p className="font-semibold text-gray-900">{company.name}</p>
      </div>
    </div>
  )
}
