"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { CalendarIcon, Loader2, Send, CalendarDays } from "lucide-react"
import { format } from "date-fns"
import { useToast } from "@/hooks/use-toast"
import { cn } from "@/lib/utils"

interface MobileLeaveFormProps {
  userId: string
  employmentType?: string
  internStartDate?: string
}

export function MobileLeaveForm({ userId, employmentType, internStartDate }: MobileLeaveFormProps) {
  const [leaveType, setLeaveType] = useState<string>("")
  const [startDate, setStartDate] = useState<Date>()
  const [endDate, setEndDate] = useState<Date>()
  const [reason, setReason] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()
  const supabase = createClient()
  const { toast } = useToast()

  // Calculate intern months for replacement leave entitlement
  const calculateInternMonths = () => {
    if (!internStartDate) return 0
    const start = new Date(internStartDate)
    const now = new Date()
    const months = (now.getFullYear() - start.getFullYear()) * 12 + (now.getMonth() - start.getMonth())
    return Math.max(0, months)
  }

  const internMonths = calculateInternMonths()
  const isIntern = employmentType === "intern"

  // Leave options based on employment type
  const leaveOptions = isIntern
    ? [
        { value: "replacement", label: "Replacement Leave", description: `${internMonths} day(s) based on ${internMonths} month(s)` },
        { value: "emergency", label: "Emergency Leave" },
      ]
    : [
        { value: "annual", label: "Annual Leave" },
        { value: "sick", label: "Sick Leave" },
        { value: "emergency", label: "Emergency Leave" },
        { value: "unpaid", label: "Unpaid Leave" },
      ]

  const calculateDays = () => {
    if (!startDate || !endDate) return 0
    const diffTime = Math.abs(endDate.getTime() - startDate.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
    return diffDays
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!leaveType || !startDate || !endDate || !reason) {
      toast({
        title: "Error",
        description: "Please fill in all fields",
        variant: "destructive",
      })
      return
    }

    if (endDate < startDate) {
      toast({
        title: "Error",
        description: "End date must be after start date",
        variant: "destructive",
      })
      return
    }

    setIsLoading(true)
    try {
      const { error } = await supabase.from("leaves").insert({
        user_id: userId,
        leave_type: leaveType,
        start_date: format(startDate, "yyyy-MM-dd"),
        end_date: format(endDate, "yyyy-MM-dd"),
        days: calculateDays(),
        reason,
        status: "pending",
      })

      if (error) throw error

      toast({
        title: "Success",
        description: "Leave request submitted successfully",
      })

      // Reset form
      setLeaveType("")
      setStartDate(undefined)
      setEndDate(undefined)
      setReason("")
      router.refresh()
    } catch (error: any) {
      toast({
        title: "Error",
        description: error.message || "Failed to submit leave request",
        variant: "destructive",
      })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {/* Leave Type */}
      <div className="space-y-2">
        <Label htmlFor="leave-type" className="text-sm font-semibold text-slate-700 dark:text-slate-300">Leave Type</Label>
        <Select value={leaveType} onValueChange={setLeaveType}>
          <SelectTrigger id="leave-type" className="h-12 rounded-xl border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            <SelectValue placeholder="Select leave type" />
          </SelectTrigger>
          <SelectContent className="rounded-xl">
            {leaveOptions.map((option) => (
              <SelectItem key={option.value} value={option.value} className="rounded-lg">
                <div className="flex flex-col">
                  <span>{option.label}</span>
                  {option.description && (
                    <span className="text-xs text-slate-500">{option.description}</span>
                  )}
                </div>
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Intern Leave Info */}
      {isIntern && (
        <div className="p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800">
          <p className="text-xs font-medium text-indigo-700 dark:text-indigo-300">
            📋 As an intern ({internMonths} month{internMonths !== 1 ? 's' : ''}), you are entitled to {internMonths} replacement leave day{internMonths !== 1 ? 's' : ''} and emergency leave.
          </p>
        </div>
      )}

      {/* Date Selection Row */}
      <div className="grid grid-cols-2 gap-3">
        {/* Start Date */}
        <div className="space-y-2">
          <Label className="text-sm font-semibold text-slate-700 dark:text-slate-300">Start Date</Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button
                variant="outline"
                className={cn(
                  "w-full h-12 justify-start text-left font-normal rounded-xl border-slate-200 dark:border-slate-700",
                  !startDate && "text-muted-foreground"
                )}
              >
                <CalendarIcon className="mr-2 h-4 w-4 text-amber-500" />
                {startDate ? format(startDate, "dd MMM") : "Start"}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0 rounded-xl" align="start">
              <Calendar
                mode="single"
                selected={startDate}
                onSelect={setStartDate}
                disabled={(date) => date < new Date(new Date().setHours(0, 0, 0, 0))}
                initialFocus
                className="rounded-xl"
              />
            </PopoverContent>
          </Popover>
        </div>

        {/* End Date */}
        <div className="space-y-2">
          <Label className="text-sm font-semibold text-slate-700 dark:text-slate-300">End Date</Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button
                variant="outline"
                className={cn(
                  "w-full h-12 justify-start text-left font-normal rounded-xl border-slate-200 dark:border-slate-700",
                  !endDate && "text-muted-foreground"
                )}
              >
                <CalendarIcon className="mr-2 h-4 w-4 text-amber-500" />
                {endDate ? format(endDate, "dd MMM") : "End"}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0 rounded-xl" align="start">
              <Calendar
                mode="single"
                selected={endDate}
                onSelect={setEndDate}
                disabled={(date) => 
                  date < new Date(new Date().setHours(0, 0, 0, 0)) ||
                  (startDate ? date < startDate : false)
                }
                initialFocus
                className="rounded-xl"
              />
            </PopoverContent>
          </Popover>
        </div>
      </div>

      {/* Days Calculation */}
      {startDate && endDate && (
        <div className="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-lg bg-amber-100 dark:bg-amber-800">
              <CalendarDays className="h-4 w-4 text-amber-600 dark:text-amber-400" />
            </div>
            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Total Days</span>
          </div>
          <span className="text-2xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
            {calculateDays()}
          </span>
        </div>
      )}

      {/* Reason */}
      <div className="space-y-2">
        <Label htmlFor="reason" className="text-sm font-semibold text-slate-700 dark:text-slate-300">Reason</Label>
        <Textarea
          id="reason"
          placeholder="Enter reason for leave..."
          value={reason}
          onChange={(e) => setReason(e.target.value)}
          rows={3}
          className="resize-none rounded-xl border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
        />
      </div>

      {/* Submit Button */}
      <Button 
        type="submit" 
        disabled={isLoading} 
        className="w-full h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-lg shadow-amber-500/30 transition-all duration-200" 
        size="lg"
      >
        {isLoading ? (
          <>
            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            Submitting...
          </>
        ) : (
          <>
            <Send className="mr-2 h-4 w-4" />
            Submit Request
          </>
        )}
      </Button>
    </form>
  )
}
