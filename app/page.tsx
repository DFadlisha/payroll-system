import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Clock, DollarSign, FileText, Users } from "lucide-react"
import Link from "next/link"

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      <div className="container mx-auto px-4 py-16">
        <div className="text-center mb-16">
          <h1 className="text-5xl font-bold text-gray-900 mb-4 text-balance">MI-NES System</h1>
          <p className="text-xl text-gray-600 mb-8 text-pretty">
            Malaysian Payroll & Attendance Management System - Comprehensive solution for managing staff attendance,
            payroll calculations with EPF, SOCSO, and EIS deductions
          </p>
          <div className="flex gap-4 justify-center">
            <Button asChild size="lg">
              <Link href="/auth/login">Sign In</Link>
            </Button>
            <Button asChild variant="outline" size="lg">
              <Link href="/auth/signup">Create Account</Link>
            </Button>
          </div>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
          <Card>
            <CardHeader>
              <Clock className="h-10 w-10 text-blue-600 mb-2" />
              <CardTitle>Attendance Tracking</CardTitle>
              <CardDescription>Easy clock-in/out system with real-time tracking</CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <DollarSign className="h-10 w-10 text-green-600 mb-2" />
              <CardTitle>Payroll Calculation</CardTitle>
              <CardDescription>Automated calculations with Malaysian statutory deductions</CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <FileText className="h-10 w-10 text-purple-600 mb-2" />
              <CardTitle>Digital Payslips</CardTitle>
              <CardDescription>Detailed payslips with all deductions and contributions</CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <Users className="h-10 w-10 text-orange-600 mb-2" />
              <CardTitle>Leave Management</CardTitle>
              <CardDescription>Submit and approve leave requests seamlessly</CardDescription>
            </CardHeader>
          </Card>
        </div>

        <Card className="bg-white/80 backdrop-blur">
          <CardHeader>
            <CardTitle className="text-2xl">Malaysian Statutory Compliance</CardTitle>
            <CardDescription>Fully compliant with Malaysian employment regulations</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-3 gap-6">
              <div>
                <h3 className="font-semibold text-lg mb-2 text-blue-700">EPF (KWSP)</h3>
                <p className="text-sm text-gray-600">
                  Employee Provident Fund contributions calculated at 11% (employee) and 12-13% (employer) based on
                  official schedules
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-lg mb-2 text-green-700">SOCSO (PERKESO)</h3>
                <p className="text-sm text-gray-600">
                  Social Security Organization contributions with tiered rates for both employee and employer portions
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-lg mb-2 text-purple-700">EIS</h3>
                <p className="text-sm text-gray-600">
                  Employment Insurance System at 0.2% each for employee and employer with salary threshold checks
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
