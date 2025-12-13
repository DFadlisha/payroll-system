"use client"

import { useState, useRef } from "react"
import { useRouter } from "next/navigation"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { CalendarIcon, Loader2, Send, CalendarDays, Upload, FileText, X } from "lucide-react"
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
  const [medicalFile, setMedicalFile] = useState<File | null>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)
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
  const isMedicalLeave = leaveType === "medical"

  // Leave options based on employment type
  const leaveOptions = isIntern
    ? [
        { value: "replacement", label: "Replacement Leave", description: `${internMonths} day(s) based on ${internMonths} month(s)` },
        { value: "medical", label: "Medical Leave", description: "Requires MC upload" },
        { value: "emergency", label: "Emergency Leave" },
      ]
    : [
        { value: "annual", label: "Annual Leave" },
        { value: "medical", label: "Medical Leave", description: "Requires MC or appointment proof" },
        { value: "emergency", label: "Emergency Leave" },
        { value: "unpaid", label: "Unpaid Leave" },
      ]

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      // Check file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        toast({
          title: "Error",
          description: "File size must be less than 5MB",
          variant: "destructive",
        })
        return
      }
      // Check file type
      const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "application/pdf"]
      if (!allowedTypes.includes(file.type)) {
        toast({
          title: "Error",
          description: "Only JPG, PNG or PDF files are allowed",
          variant: "destructive",
        })
        return
      }
      setMedicalFile(file)
    }
  }

  const removeFile = () => {
    setMedicalFile(null)
    if (fileInputRef.current) {
      fileInputRef.current.value = ""
    }
  }

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

    // Medical leave requires proof upload
    if (isMedicalLeave && !medicalFile) {
      toast({
        title: "Error",
        description: "Please upload MC or appointment proof for medical leave",
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
      let medicalProofUrl = null

      // Upload medical proof if provided
      if (medicalFile) {
        const fileExt = medicalFile.name.split('.').pop()
        const fileName = `${userId}-${Date.now()}.${fileExt}`
        const filePath = `medical-proofs/${fileName}`

        const { error: uploadError } = await supabase.storage
          .from('leave-documents')
          .upload(filePath, medicalFile)

        if (uploadError) {
          // If bucket doesn't exist, just store without file
          console.warn("Could not upload file:", uploadError.message)
        } else {
          const { data: urlData } = supabase.storage
            .from('leave-documents')
            .getPublicUrl(filePath)
          medicalProofUrl = urlData.publicUrl
        }
      }

      const { error } = await supabase.from("leaves").insert({
        user_id: userId,
        leave_type: leaveType,
        start_date: format(startDate, "yyyy-MM-dd"),
        end_date: format(endDate, "yyyy-MM-dd"),
        days: calculateDays(),
        reason: isMedicalLeave ? `${reason} [MC/Appointment proof: ${medicalFile?.name || 'attached'}]` : reason,
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
      setMedicalFile(null)
      if (fileInputRef.current) {
        fileInputRef.current.value = ""
      }
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

      {/* Medical Leave File Upload */}
      {isMedicalLeave && (
        <div className="space-y-2">
          <Label className="text-sm font-semibold text-slate-700 dark:text-slate-300">
            Medical Certificate / Appointment Proof <span className="text-rose-500">*</span>
          </Label>
          <div className="p-3 bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20 rounded-xl border border-rose-100 dark:border-rose-800">
            <p className="text-xs font-medium text-rose-700 dark:text-rose-300 mb-3">
              🏥 Please upload your MC or medical appointment letter as proof
            </p>
            
            {!medicalFile ? (
              <div 
                className="border-2 border-dashed border-rose-200 dark:border-rose-700 rounded-xl p-4 text-center cursor-pointer hover:border-rose-400 transition-colors"
                onClick={() => fileInputRef.current?.click()}
              >
                <Upload className="h-8 w-8 mx-auto text-rose-400 mb-2" />
                <p className="text-sm text-rose-600 dark:text-rose-400 font-medium">Tap to upload</p>
                <p className="text-xs text-rose-500 dark:text-rose-500 mt-1">JPG, PNG or PDF (max 5MB)</p>
              </div>
            ) : (
              <div className="flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-xl border border-rose-200 dark:border-rose-700">
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-lg bg-rose-100 dark:bg-rose-800">
                    <FileText className="h-4 w-4 text-rose-600 dark:text-rose-400" />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-slate-700 dark:text-slate-300 truncate max-w-[150px]">
                      {medicalFile.name}
                    </p>
                    <p className="text-xs text-slate-500">
                      {(medicalFile.size / 1024).toFixed(1)} KB
                    </p>
                  </div>
                </div>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={removeFile}
                  className="h-8 w-8 p-0 text-rose-500 hover:text-rose-700 hover:bg-rose-100"
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
            )}
            
            <input
              ref={fileInputRef}
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              onChange={handleFileChange}
              className="hidden"
            />
          </div>
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
