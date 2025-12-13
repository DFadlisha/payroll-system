"use client"

import { useState } from "react"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Eye, User, Mail, Briefcase, Wallet, CreditCard, Shield, CheckCircle, XCircle } from "lucide-react"

interface Employee {
  id: string
  full_name: string
  email: string
  role: string
  employment_type: string
  basic_salary: number
  epf_number: string | null
  socso_number: string | null
  is_malaysian_citizen: boolean
  created_at: string
}

interface ViewEmployeeDialogProps {
  employee: Employee
}

export function ViewEmployeeDialog({ employee }: ViewEmployeeDialogProps) {
  const [open, setOpen] = useState(false)

  const isIntern = employee.employment_type === "intern"
  const employmentTypeLabel = 
    employee.employment_type === "permanent" ? "Full-Time" : 
    employee.employment_type === "part-time" ? "Part-Time" : "Intern"

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="ghost" size="sm">
          <Eye className="h-4 w-4" />
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Employee Profile
          </DialogTitle>
          <DialogDescription>
            Detailed information for {employee.full_name}
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {/* Header with name and badges */}
          <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg">
            <div className="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
              <User className="h-8 w-8 text-indigo-600" />
            </div>
            <div className="flex-1">
              <h3 className="text-xl font-semibold">{employee.full_name}</h3>
              <p className="text-sm text-muted-foreground">{employee.email}</p>
              <div className="flex gap-2 mt-2">
                <Badge variant="outline" className="capitalize">
                  {employee.role}
                </Badge>
                <Badge 
                  variant={isIntern ? "secondary" : "default"}
                  className={
                    employee.employment_type === "permanent" ? "bg-green-100 text-green-800" :
                    employee.employment_type === "part-time" ? "bg-blue-100 text-blue-800" :
                    "bg-orange-100 text-orange-800"
                  }
                >
                  {employmentTypeLabel}
                </Badge>
              </div>
            </div>
          </div>

          {/* Salary Info */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Wallet className="h-4 w-4" />
                Salary Information
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                RM {employee.basic_salary?.toLocaleString("en-MY", { minimumFractionDigits: 2 }) || "0.00"}
              </div>
              <p className="text-sm text-muted-foreground">Monthly basic salary</p>
            </CardContent>
          </Card>

          {/* Statutory Details */}
          <div className="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium flex items-center gap-2">
                  <CreditCard className="h-4 w-4" />
                  EPF Number
                </CardTitle>
              </CardHeader>
              <CardContent>
                {isIntern ? (
                  <p className="text-sm text-orange-600">Not applicable for interns</p>
                ) : (
                  <p className="font-mono">{employee.epf_number || "Not provided"}</p>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium flex items-center gap-2">
                  <Shield className="h-4 w-4" />
                  SOCSO Number
                </CardTitle>
              </CardHeader>
              <CardContent>
                {isIntern ? (
                  <p className="text-sm text-orange-600">Not applicable for interns</p>
                ) : (
                  <p className="font-mono">{employee.socso_number || "Not provided"}</p>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Citizenship Status */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Briefcase className="h-4 w-4" />
                Citizenship Status
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                {employee.is_malaysian_citizen ? (
                  <>
                    <CheckCircle className="h-5 w-5 text-green-600" />
                    <span className="font-medium text-green-700">Malaysian Citizen</span>
                  </>
                ) : (
                  <>
                    <XCircle className="h-5 w-5 text-orange-600" />
                    <span className="font-medium text-orange-700">Non-Malaysian Citizen</span>
                  </>
                )}
              </div>
              <p className="text-xs text-muted-foreground mt-1">
                {employee.is_malaysian_citizen 
                  ? "Standard EPF rates (11% employee, 12-13% employer)"
                  : "Reduced EPF rates (11% employee, 4-4.5% employer)"
                }
              </p>
            </CardContent>
          </Card>

          {/* Deductions Summary */}
          {!isIntern && (
            <Card className="bg-gray-50">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Monthly Deductions Summary</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid gap-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">EPF (Employee)</span>
                    <span>11% = RM {((employee.basic_salary || 0) * 0.11).toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">SOCSO (Employee)</span>
                    <span>~0.5%</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">EIS (Employee)</span>
                    <span>0.2%</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}

          {isIntern && (
            <div className="p-4 bg-orange-50 border border-orange-200 rounded-lg">
              <p className="text-sm text-orange-800">
                <strong>Note:</strong> Interns are exempt from EPF, SOCSO, and EIS deductions.
              </p>
            </div>
          )}

          {/* Account Info */}
          <div className="text-xs text-muted-foreground pt-2 border-t">
            <p>Account created: {new Date(employee.created_at).toLocaleDateString("en-MY", {
              day: "numeric",
              month: "long",
              year: "numeric"
            })}</p>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}
