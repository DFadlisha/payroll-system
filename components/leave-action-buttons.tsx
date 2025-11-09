"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { Check, X } from "lucide-react"

interface LeaveActionButtonsProps {
  leaveId: string
  hrId: string
}

export function LeaveActionButtons({ leaveId, hrId }: LeaveActionButtonsProps) {
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()
  const supabase = createClient()

  const handleAction = async (status: "approved" | "rejected") => {
    setIsLoading(true)

    try {
      const { error } = await supabase
        .from("leaves")
        .update({
          status,
          reviewed_by: hrId,
          reviewed_at: new Date().toISOString(),
        })
        .eq("id", leaveId)

      if (error) throw error

      router.refresh()
    } catch (error) {
      console.error("[v0] Leave action error:", error)
      alert("Failed to update leave status")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="flex gap-2">
      <Button
        size="sm"
        variant="default"
        onClick={() => handleAction("approved")}
        disabled={isLoading}
        className="bg-green-600 hover:bg-green-700"
      >
        <Check className="h-4 w-4" />
      </Button>
      <Button
        size="sm"
        variant="destructive"
        onClick={() => handleAction("rejected")}
        disabled={isLoading}
        className="bg-red-600 hover:bg-red-700"
      >
        <X className="h-4 w-4" />
      </Button>
    </div>
  )
}
