"use client"

import { Button } from "@/components/ui/button"
import { LogOut, Loader2 } from "lucide-react"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { useState } from "react"

export function MobileSignOutButton() {
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()
  const supabase = createClient()

  const handleSignOut = async () => {
    setIsLoading(true)
    try {
      await supabase.auth.signOut()
      router.push("/auth/login")
      router.refresh()
    } catch (error) {
      console.error("Sign out error:", error)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <Button 
      variant="outline" 
      className="w-full h-14 text-base font-semibold border-2 border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950 hover:border-rose-300 dark:hover:border-rose-700 rounded-2xl transition-all duration-300 active:scale-[0.98]" 
      onClick={handleSignOut}
      disabled={isLoading}
    >
      {isLoading ? (
        <Loader2 className="mr-2 h-5 w-5 animate-spin" />
      ) : (
        <LogOut className="mr-2 h-5 w-5" />
      )}
      {isLoading ? "Signing Out..." : "Sign Out"}
    </Button>
  )
}
