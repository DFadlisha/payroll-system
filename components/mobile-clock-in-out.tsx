"use client"

import { useState, useEffect } from "react"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { LogIn, LogOut, MapPin, Loader2 } from "lucide-react"
import { useRouter } from "next/navigation"
import { useToast } from "@/hooks/use-toast"

interface MobileClockInOutProps {
  userId: string
}

interface LocationData {
  latitude: number
  longitude: number
  address: string
}

export function MobileClockInOut({ userId }: MobileClockInOutProps) {
  const [currentTime, setCurrentTime] = useState(new Date())
  const [mounted, setMounted] = useState(false)
  const [activeAttendance, setActiveAttendance] = useState<any>(null)
  const [isLoading, setIsLoading] = useState(false)
  const [locationLoading, setLocationLoading] = useState(false)
  const [location, setLocation] = useState<LocationData | null>(null)
  const router = useRouter()
  const supabase = createClient()
  const { toast } = useToast()

  useEffect(() => {
    setMounted(true)
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

  const getLocation = async (): Promise<LocationData> => {
    setLocationLoading(true)
    try {
      return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
          reject(new Error("Geolocation is not supported by your browser"))
          return
        }

        navigator.geolocation.getCurrentPosition(
          async (position) => {
            const { latitude, longitude } = position.coords

            // Reverse geocoding to get address
            try {
              const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
              )
              const data = await response.json()
              const address = data.display_name || `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`

              resolve({ latitude, longitude, address })
            } catch (error) {
              // Fallback to coordinates if reverse geocoding fails
              resolve({
                latitude,
                longitude,
                address: `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`,
              })
            }
          },
          (error) => {
            reject(new Error("Unable to retrieve your location"))
          },
          {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
          }
        )
      })
    } finally {
      setLocationLoading(false)
    }
  }

  const handleClockIn = async () => {
    setIsLoading(true)
    try {
      const locationData = await getLocation()
      setLocation(locationData)

      const { error } = await supabase.from("attendance").insert({
        user_id: userId,
        clock_in: new Date().toISOString(),
        status: "active",
        overtime_hours: 0,
        clock_in_latitude: locationData.latitude,
        clock_in_longitude: locationData.longitude,
        clock_in_address: locationData.address,
      })

      if (error) throw error

      toast({
        title: "Clocked In Successfully",
        description: `Location: ${locationData.address}`,
      })

      checkActiveAttendance()
      router.refresh()
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to clock in. Please try again.",
        variant: "destructive",
      })
    } finally {
      setIsLoading(false)
    }
  }

  const handleClockOut = async () => {
    if (!activeAttendance) return

    setIsLoading(true)
    try {
      const locationData = await getLocation()
      setLocation(locationData)

      const clockOutTime = new Date()
      const clockInTime = new Date(activeAttendance.clock_in)
      const totalHours = (clockOutTime.getTime() - clockInTime.getTime()) / (1000 * 60 * 60)

      const { error } = await supabase
        .from("attendance")
        .update({
          clock_out: clockOutTime.toISOString(),
          total_hours: totalHours,
          status: "completed",
          clock_out_latitude: locationData.latitude,
          clock_out_longitude: locationData.longitude,
          clock_out_address: locationData.address,
        })
        .eq("id", activeAttendance.id)

      if (error) throw error

      toast({
        title: "Clocked Out Successfully",
        description: `Total hours: ${totalHours.toFixed(2)} hours\nLocation: ${locationData.address}`,
      })

      setActiveAttendance(null)
      router.refresh()
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to clock out. Please try again.",
        variant: "destructive",
      })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-5">
      {/* Current Time Display - Premium Design */}
      <div className="text-center py-6 px-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl">
        <div className="text-5xl font-bold tabular-nums tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent" suppressHydrationWarning>
          {mounted ? currentTime.toLocaleTimeString("en-MY", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          }) : "--:--:--"}
        </div>
        <div className="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium" suppressHydrationWarning>
          {mounted ? currentTime.toLocaleDateString("en-MY", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
          }) : "Loading..."}
        </div>
      </div>

      {/* Location Status */}
      {(location || activeAttendance?.clock_in_address) && (
        <div className="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/50 dark:to-indigo-950/50 rounded-2xl border border-blue-200/50 dark:border-blue-800/50">
          <div className="p-2 rounded-xl bg-blue-500/10">
            <MapPin className="h-4 w-4 text-blue-600 dark:text-blue-400" />
          </div>
          <div className="flex-1">
            <p className="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-1">Current Location</p>
            <p className="text-sm text-blue-600/80 dark:text-blue-400/80">
              {location?.address || activeAttendance?.clock_in_address}
            </p>
          </div>
        </div>
      )}

      {/* Action Buttons */}
      <div className="space-y-4">
        {!activeAttendance ? (
          <Button
            onClick={handleClockIn}
            disabled={isLoading || locationLoading}
            className="w-full h-16 text-lg font-semibold bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 shadow-lg shadow-emerald-500/30 rounded-2xl transition-all duration-300 active:scale-[0.98]"
            size="lg"
          >
            {isLoading || locationLoading ? (
              <>
                <Loader2 className="mr-3 h-6 w-6 animate-spin" />
                {locationLoading ? "Getting Location..." : "Clocking In..."}
              </>
            ) : (
              <>
                <LogIn className="mr-3 h-6 w-6" />
                Clock In
              </>
            )}
          </Button>
        ) : (
          <>
            <div className="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/50 dark:to-teal-950/50 rounded-2xl p-5 border border-emerald-200/50 dark:border-emerald-800/50">
              <div className="text-center space-y-3">
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                  <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                  <span className="text-xs font-semibold">Currently Working</span>
                </div>
                <p className="text-sm text-emerald-600 dark:text-emerald-400">Clocked in at</p>
                <p className="text-3xl font-bold text-emerald-700 dark:text-emerald-300">
                  {new Date(activeAttendance.clock_in).toLocaleTimeString("en-MY", {
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </p>
                {activeAttendance.clock_in_address && (
                  <div className="flex items-center gap-2 justify-center text-emerald-600/70 dark:text-emerald-400/70">
                    <MapPin className="h-3 w-3 flex-shrink-0" />
                    <p className="text-xs truncate max-w-[250px]">
                      {activeAttendance.clock_in_address}
                    </p>
                  </div>
                )}
              </div>
            </div>
            <Button
              onClick={handleClockOut}
              disabled={isLoading || locationLoading}
              className="w-full h-16 text-lg font-semibold bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 shadow-lg shadow-rose-500/30 rounded-2xl transition-all duration-300 active:scale-[0.98]"
              size="lg"
            >
              {isLoading || locationLoading ? (
                <>
                  <Loader2 className="mr-3 h-6 w-6 animate-spin" />
                  {locationLoading ? "Getting Location..." : "Clocking Out..."}
                </>
              ) : (
                <>
                  <LogOut className="mr-3 h-6 w-6" />
                  Clock Out
                </>
              )}
            </Button>
          </>
        )}
      </div>

      {/* Permission Note */}
      <p className="text-xs text-center text-slate-400 dark:text-slate-500">
        📍 Location permission required for attendance tracking
      </p>
    </div>
  )
}
