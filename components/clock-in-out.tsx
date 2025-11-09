"use client"

import { useState, useEffect } from "react"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Clock, LogIn, LogOut } from "lucide-react"
import { useRouter } from "next/navigation"

interface ClockInOutProps {
  userId: string
}

export function ClockInOut({ userId }: ClockInOutProps) {
  const [currentTime, setCurrentTime] = useState(new Date())
  const [activeAttendance, setActiveAttendance] = useState<any>(null)
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()
  const supabase = createClient()

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(new Date())
    }, 1000)

    checkActiveAttendance()

    return () => clearInterval(timer)
  }, [])

  const checkActiveAttendance = async () => {
    const { data } = await supabase
      .from("attendance")
      .select("*")
      .eq("user_id", userId)
      .eq("status", "active")
      .order("clock_in", { ascending: false })
      .limit(1)
      .maybeSingle()

    setActiveAttendance(data)
  }

  const handleClockIn = async () => {
    setIsLoading(true)
    try {
      const { error } = await supabase.from("attendance").insert({
        user_id: userId,
        clock_in: new Date().toISOString(),
        status: "active",
      })

      if (error) throw error

      await checkActiveAttendance()
      router.refresh()
    } catch (error) {
      console.error("Clock in error:", error)
      alert("Failed to clock in. Please try again.")
    } finally {
      setIsLoading(false)
    }
  }

  const handleClockOut = async () => {
    if (!activeAttendance) return

    setIsLoading(true)
    try {
      const clockOutTime = new Date()
      const clockInTime = new Date(activeAttendance.clock_in)
      const totalHours = (clockOutTime.getTime() - clockInTime.getTime()) / (1000 * 60 * 60)

      // Calculate overtime (assuming 8 hours is regular)
      const regularHours = Math.min(totalHours, 8)
      const overtimeHours = Math.max(totalHours - 8, 0)

      const { error } = await supabase
        .from("attendance")
        .update({
          clock_out: clockOutTime.toISOString(),
          total_hours: totalHours,
          overtime_hours: overtimeHours,
          status: "completed",
        })
        .eq("id", activeAttendance.id)

      if (error) throw error

      setActiveAttendance(null)
      router.refresh()
    } catch (error) {
      console.error("Clock out error:", error)
      alert("Failed to clock out. Please try again.")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <Card className="bg-white shadow-lg">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5" />
          Clock In/Out
        </CardTitle>
        <CardDescription>Record your attendance</CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="text-center">
          <div className="text-4xl font-bold text-gray-900 mb-2">
            {currentTime.toLocaleTimeString("en-MY", {
              hour: "2-digit",
              minute: "2-digit",
              second: "2-digit",
            })}
          </div>
          <div className="text-sm text-gray-600">
            {currentTime.toLocaleDateString("en-MY", {
              weekday: "long",
              year: "numeric",
              month: "long",
              day: "numeric",
            })}
          </div>
        </div>

        {activeAttendance ? (
          <div className="space-y-4">
            <div className="bg-green-50 border border-green-200 rounded-lg p-4">
              <p className="text-sm text-green-800 font-medium mb-1">Clocked In</p>
              <p className="text-lg font-bold text-green-900">
                {new Date(activeAttendance.clock_in).toLocaleTimeString("en-MY", {
                  hour: "2-digit",
                  minute: "2-digit",
                })}
              </p>
            </div>
            <Button onClick={handleClockOut} disabled={isLoading} className="w-full" size="lg" variant="destructive">
              <LogOut className="mr-2 h-5 w-5" />
              {isLoading ? "Clocking Out..." : "Clock Out"}
            </Button>
          </div>
        ) : (
          <Button onClick={handleClockIn} disabled={isLoading} className="w-full" size="lg">
            <LogIn className="mr-2 h-5 w-5" />
            {isLoading ? "Clocking In..." : "Clock In"}
          </Button>
        )}
      </CardContent>
    </Card>
  )
}
