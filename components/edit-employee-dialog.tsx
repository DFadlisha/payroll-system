"use client"

import { useState } from "react"
import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useToast } from "@/hooks/use-toast"
import { useRouter } from "next/navigation"
import { Pencil, Loader2 } from "lucide-react"

interface Employee {
  id: string
  email: string
  full_name: string
  role: string
  employment_type: string
  basic_salary: number
  hourly_rate: number | null
  epf_number: string | null
  socso_number: string | null
  citizenship_status: string | null
}

interface EditEmployeeDialogProps {
  employee: Employee
}

export function EditEmployeeDialog({ employee }: EditEmployeeDialogProps) {
  const [open, setOpen] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [formData, setFormData] = useState({
    fullName: employee.full_name,
    role: employee.role as "staff" | "hr",
    employmentType: (employee.employment_type || "permanent") as "permanent" | "part-time" | "intern",
    basicSalary: employee.basic_salary?.toString() || "",
    hourlyRate: employee.hourly_rate?.toString() || "",
    epfNumber: employee.epf_number || "",
    socsoNumber: employee.socso_number || "",
    citizenshipStatus: (employee.citizenship_status || "citizen") as "citizen" | "permanent_resident" | "foreigner",
  })

  const { toast } = useToast()
  const router = useRouter()
  const supabase = createClient()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)

    try {
      const { error } = await supabase
        .from("profiles")
        .update({
          full_name: formData.fullName,
          role: formData.role,
          employment_type: formData.employmentType,
          basic_salary: parseFloat(formData.basicSalary) || 0,
          hourly_rate: formData.hourlyRate ? parseFloat(formData.hourlyRate) : null,
          epf_number: formData.employmentType !== "intern" ? formData.epfNumber || null : null,
          socso_number: formData.employmentType !== "intern" ? formData.socsoNumber || null : null,
          citizenship_status: formData.citizenshipStatus,
          updated_at: new Date().toISOString(),
        })
        .eq("id", employee.id)

      if (error) throw error

      toast({
        title: "Employee updated",
        description: `${formData.fullName}'s profile has been updated.`,
      })

      setOpen(false)
      router.refresh()
    } catch (error: any) {
      toast({
        title: "Error updating employee",
        description: error.message,
        variant: "destructive",
      })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="ghost" size="sm">
          <Pencil className="h-4 w-4" />
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[500px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Edit Employee</DialogTitle>
          <DialogDescription>
            Update employee details for {employee.full_name}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4 mt-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2 space-y-2">
              <Label htmlFor="fullName">Full Name *</Label>
              <Input
                id="fullName"
                value={formData.fullName}
                onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                required
              />
            </div>

            <div className="col-span-2 space-y-2">
              <Label>Email</Label>
              <Input value={employee.email} disabled className="bg-gray-50" />
              <p className="text-xs text-muted-foreground">Email cannot be changed</p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="role">Role *</Label>
              <Select
                value={formData.role}
                onValueChange={(value: "staff" | "hr") => setFormData({ ...formData, role: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="staff">Staff</SelectItem>
                  <SelectItem value="hr">HR</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="employmentType">Employment Type *</Label>
              <Select
                value={formData.employmentType}
                onValueChange={(value: "permanent" | "part-time" | "intern") => 
                  setFormData({ ...formData, employmentType: value })
                }
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="permanent">Full-Time (Permanent)</SelectItem>
                  <SelectItem value="part-time">Part-Time</SelectItem>
                  <SelectItem value="intern">Intern</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="basicSalary">
                {formData.employmentType === "intern" ? "Monthly Allowance (RM)" : "Basic Salary (RM) *"}
              </Label>
              <Input
                id="basicSalary"
                type="number"
                step="0.01"
                value={formData.basicSalary}
                onChange={(e) => setFormData({ ...formData, basicSalary: e.target.value })}
                required
              />
            </div>

            {(formData.employmentType === "part-time" || formData.employmentType === "intern") && (
              <div className="space-y-2">
                <Label htmlFor="hourlyRate">Hourly Rate (RM)</Label>
                <Input
                  id="hourlyRate"
                  type="number"
                  step="0.01"
                  value={formData.hourlyRate}
                  onChange={(e) => setFormData({ ...formData, hourlyRate: e.target.value })}
                  placeholder="15.00"
                />
              </div>
            )}

            <div className="space-y-2">
              <Label htmlFor="citizenshipStatus">Citizenship Status *</Label>
              <Select
                value={formData.citizenshipStatus}
                onValueChange={(value: "citizen" | "permanent_resident" | "foreigner") => 
                  setFormData({ ...formData, citizenshipStatus: value })
                }
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

            {formData.employmentType !== "intern" && (
              <>
                <div className="space-y-2">
                  <Label htmlFor="epfNumber">EPF Number</Label>
                  <Input
                    id="epfNumber"
                    value={formData.epfNumber}
                    onChange={(e) => setFormData({ ...formData, epfNumber: e.target.value })}
                    placeholder="12345678"
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="socsoNumber">SOCSO Number</Label>
                  <Input
                    id="socsoNumber"
                    value={formData.socsoNumber}
                    onChange={(e) => setFormData({ ...formData, socsoNumber: e.target.value })}
                    placeholder="A12345678"
                  />
                </div>
              </>
            )}
          </div>

          {formData.employmentType === "intern" && (
            <div className="p-3 bg-orange-50 text-orange-800 rounded-md text-sm">
              <strong>Note:</strong> Interns are exempt from EPF, SOCSO, and EIS deductions.
            </div>
          )}

          <div className="flex justify-end gap-3 pt-4">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={isLoading}>
              {isLoading ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Saving...
                </>
              ) : (
                "Save Changes"
              )}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  )
}
