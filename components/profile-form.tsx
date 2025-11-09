"use client"

import type React from "react"

import { useState } from "react"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useRouter } from "next/navigation"
import type { Profile } from "@/lib/types"

interface ProfileFormProps {
  profile: Profile
}

export function ProfileForm({ profile }: ProfileFormProps) {
  const [fullName, setFullName] = useState(profile.full_name)
  const [epfNumber, setEpfNumber] = useState(profile.epf_number || "")
  const [socsoNumber, setSocsoNumber] = useState(profile.socso_number || "")
  const [citizenshipStatus, setCitizenshipStatus] = useState(profile.citizenship_status || "citizen")
  const [employmentType, setEmploymentType] = useState(profile.employment_type || "permanent")
  const [isLoading, setIsLoading] = useState(false)
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null)
  const router = useRouter()
  const supabase = createClient()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    setMessage(null)

    try {
      const { error } = await supabase
        .from("profiles")
        .update({
          full_name: fullName,
          epf_number: employmentType !== "intern" ? epfNumber || null : null,
          socso_number: employmentType !== "intern" ? socsoNumber || null : null,
          citizenship_status: citizenshipStatus,
          employment_type: employmentType,
          updated_at: new Date().toISOString(),
        })
        .eq("id", profile.id)

      if (error) throw error

      setMessage({ type: "success", text: "Profile updated successfully!" })
      router.refresh()
    } catch (error) {
      setMessage({ type: "error", text: error instanceof Error ? error.message : "Failed to update profile" })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid gap-4">
        <div className="grid gap-2">
          <Label htmlFor="email">Email</Label>
          <Input id="email" type="email" value={profile.email} disabled className="bg-gray-50" />
          <p className="text-xs text-muted-foreground">Email cannot be changed</p>
        </div>

        <div className="grid gap-2">
          <Label htmlFor="fullName">Full Name</Label>
          <Input id="fullName" type="text" value={fullName} onChange={(e) => setFullName(e.target.value)} required />
        </div>

        <div className="grid gap-2">
          <Label htmlFor="role">Role</Label>
          <Input id="role" type="text" value={profile.role.toUpperCase()} disabled className="bg-gray-50" />
          <p className="text-xs text-muted-foreground">Role is assigned by HR</p>
        </div>

        <div className="grid gap-2">
          <Label htmlFor="employmentType">Employment Type</Label>
          <Select
            value={employmentType}
            onValueChange={(value: "permanent" | "contract" | "intern") => setEmploymentType(value)}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="permanent">Permanent</SelectItem>
              <SelectItem value="contract">Contract</SelectItem>
              <SelectItem value="intern">Intern</SelectItem>
            </SelectContent>
          </Select>
          {employmentType === "intern" && (
            <p className="text-xs text-muted-foreground">Interns do not have EPF, SOCSO, and EIS deductions</p>
          )}
        </div>

        <div className="grid gap-2">
          <Label htmlFor="basicSalary">Basic Salary (RM)</Label>
          <Input
            id="basicSalary"
            type="text"
            value={`RM ${profile.basic_salary.toFixed(2)}`}
            disabled
            className="bg-gray-50"
          />
          <p className="text-xs text-muted-foreground">Salary is managed by HR</p>
        </div>

        {employmentType !== "intern" && (
          <>
            <div className="grid gap-2">
              <Label htmlFor="epfNumber">EPF Number</Label>
              <Input
                id="epfNumber"
                type="text"
                placeholder="e.g., 12345678"
                value={epfNumber}
                onChange={(e) => setEpfNumber(e.target.value)}
              />
            </div>

            <div className="grid gap-2">
              <Label htmlFor="socsoNumber">SOCSO Number</Label>
              <Input
                id="socsoNumber"
                type="text"
                placeholder="e.g., 87654321"
                value={socsoNumber}
                onChange={(e) => setSocsoNumber(e.target.value)}
              />
            </div>
          </>
        )}

        <div className="grid gap-2">
          <Label htmlFor="citizenshipStatus">Citizenship Status</Label>
          <Select
            value={citizenshipStatus}
            onValueChange={(value: "citizen" | "permanent_resident" | "foreigner") => setCitizenshipStatus(value)}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="citizen">Malaysian Citizen</SelectItem>
              <SelectItem value="permanent_resident">Permanent Resident</SelectItem>
              <SelectItem value="foreigner">Foreigner</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      {message && (
        <div
          className={`p-3 rounded-md text-sm ${
            message.type === "success" ? "bg-green-50 text-green-800" : "bg-red-50 text-red-800"
          }`}
        >
          {message.text}
        </div>
      )}

      <Button type="submit" disabled={isLoading} className="w-full">
        {isLoading ? "Saving..." : "Save Changes"}
      </Button>
    </form>
  )
}
