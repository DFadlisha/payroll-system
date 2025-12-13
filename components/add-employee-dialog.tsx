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
import { UserPlus, Loader2 } from "lucide-react"

interface AddEmployeeDialogProps {
  companyId: string
}

export function AddEmployeeDialog({ companyId }: AddEmployeeDialogProps) {
  const [open, setOpen] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [formData, setFormData] = useState({
    email: "",
    password: "",
    fullName: "",
    role: "staff" as "staff" | "hr",
    employmentType: "permanent" as "permanent" | "part-time" | "intern",
    basicSalary: "",
    hourlyRate: "",
    epfNumber: "",
    socsoNumber: "",
    citizenshipStatus: "citizen" as "citizen" | "permanent_resident" | "foreigner",
  })

  const { toast } = useToast()
  const router = useRouter()
  const supabase = createClient()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)

    try {
      // Create auth user
      const { data: authData, error: authError } = await supabase.auth.signUp({
        email: formData.email,
        password: formData.password,
        options: {
          data: {
            full_name: formData.fullName,
            role: formData.role,
            employment_type: formData.employmentType,
            basic_salary: parseFloat(formData.basicSalary) || 0,
            hourly_rate: formData.hourlyRate ? parseFloat(formData.hourlyRate) : null,
            epf_number: formData.employmentType !== "intern" ? formData.epfNumber : null,
            socso_number: formData.employmentType !== "intern" ? formData.socsoNumber : null,
            citizenship_status: formData.citizenshipStatus,
            company_id: companyId,
          },
        },
      })

      if (authError) throw authError

      toast({
        title: "Employee added successfully",
        description: `${formData.fullName} has been added to the system.`,
      })

      setOpen(false)
      setFormData({
        email: "",
        password: "",
        fullName: "",
        role: "staff",
        employmentType: "permanent",
        basicSalary: "",
        hourlyRate: "",
        epfNumber: "",
        socsoNumber: "",
        citizenshipStatus: "citizen",
      })
      router.refresh()
    } catch (error: any) {
      toast({
        title: "Error adding employee",
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
        <Button>
          <UserPlus className="h-4 w-4 mr-2" />
          Add Employee
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[500px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Add New Employee</DialogTitle>
          <DialogDescription>
            Create a new employee account. They will receive a verification email.
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
                placeholder="John Doe"
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="email">Email *</Label>
              <Input
                id="email"
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                placeholder="john@company.com"
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Password *</Label>
              <Input
                id="password"
                type="password"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                placeholder="Min 6 characters"
                required
                minLength={6}
              />
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
                placeholder="3000.00"
                required
              />
            </div>

            {formData.employmentType === "part-time" && (
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
                  Adding...
                </>
              ) : (
                "Add Employee"
              )}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  )
}
