"use client"

import { useState, useEffect } from "react"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
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
  const [activeAttendance, setActiveAttendance] = useState<any>(null)
  const [isLoading, setIsLoading] = useState(false)
  const [locationLoading, setLocationLoading] = useState(false)
  const [location, setLocation] = useState<LocationData | null>(null)
  const router = useRouter()
  const supabase = createClient()
  const { toast } = useToast()

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
    <div className="space-y-4">
      {/* Current Time Display */}
      <div className="text-center py-4">
        <div className="text-4xl font-bold tabular-nums">
          {currentTime.toLocaleTimeString("en-MY", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          })}
        </div>
        <div className="text-sm text-muted-foreground mt-1">
          {currentTime.toLocaleDateString("en-MY", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
          })}
        </div>
      </div>

      {/* Location Status */}
      {(location || activeAttendance?.clock_in_address) && (
        <div className="flex items-start gap-2 p-3 bg-muted rounded-lg">
          <MapPin className="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
          <div className="text-sm">
            <p className="font-medium mb-1">Current Location</p>
            <p className="text-muted-foreground">
              {location?.address || activeAttendance?.clock_in_address}
            </p>
          </div>
        </div>
      )}

      {/* Action Buttons */}
      <div className="space-y-3">
        {!activeAttendance ? (
          <Button
            onClick={handleClockIn}
            disabled={isLoading || locationLoading}
            className="w-full h-14 text-lg"
            size="lg"
          >
            {isLoading || locationLoading ? (
              <>
                <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                {locationLoading ? "Getting Location..." : "Clocking In..."}
              </>
            ) : (
              <>
                <LogIn className="mr-2 h-5 w-5" />
                Clock In
              </>
            )}
          </Button>
        ) : (
          <>
            <Card className="bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800">
              <CardContent className="pt-6">
                <div className="text-center space-y-2">
                  <p className="text-sm text-muted-foreground">Clocked in at</p>
                  <p className="text-2xl font-bold">
                    {new Date(activeAttendance.clock_in).toLocaleTimeString("en-MY", {
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </p>
                  {activeAttendance.clock_in_address && (
                    <div className="flex items-start gap-2 justify-center">
                      <MapPin className="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                      <p className="text-sm text-muted-foreground text-left">
                        {activeAttendance.clock_in_address}
                      </p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
            <Button
              onClick={handleClockOut}
              disabled={isLoading || locationLoading}
              className="w-full h-14 text-lg"
              variant="destructive"
              size="lg"
            >
              {isLoading || locationLoading ? (
                <>
                  <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                  {locationLoading ? "Getting Location..." : "Clocking Out..."}
                </>
              ) : (
                <>
                  <LogOut className="mr-2 h-5 w-5" />
                  Clock Out
                </>
              )}
            </Button>
          </>
        )}
      </div>

      {/* Permission Note */}
      <p className="text-xs text-center text-muted-foreground">
        Location permission is required for attendance tracking
      </p>
    </div>
  )
}
