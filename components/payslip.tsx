"use client"

import { useRef } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { FileText, Download } from "lucide-react"
import type { Payroll, Profile } from "@/lib/types"

interface PayslipProps {
  payroll: Payroll
  profile: Profile
}

export function Payslip({ payroll, profile }: PayslipProps) {
  const payslipRef = useRef<HTMLDivElement>(null)

  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ]

  const handleDownload = () => {
    window.print()
  }

  return (
    <>
      <style jsx global>{`
        @media print {
          body * {
            visibility: hidden;
          }
          .payslip-container,
          .payslip-container * {
            visibility: visible;
          }
          .payslip-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
          }
          .no-print {
            display: none !important;
          }
          .print-header {
            background: linear-gradient(to right, #2563eb, #4f46e5) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
          .print-badge {
            background: white !important;
            color: #2563eb !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
          .print-net-pay {
            background: linear-gradient(to right, #f0fdf4, #d1fae5) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
        }
      `}</style>

      <Card className="w-full payslip-container">
        <CardHeader className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white print-header">
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="text-2xl flex items-center gap-2">
                <FileText className="h-6 w-6" />
                Payslip
              </CardTitle>
              <CardDescription className="text-blue-100">
                {monthNames[payroll.month - 1]} {payroll.year}
              </CardDescription>
            </div>
            <div className="flex items-center gap-2">
              <Badge variant="secondary" className="bg-white text-blue-600 print-badge">
                {payroll.status.toUpperCase()}
              </Badge>
              <Button
                onClick={handleDownload}
                variant="secondary"
                size="sm"
                className="bg-white text-blue-600 hover:bg-blue-50 no-print"
              >
                <Download className="h-4 w-4 mr-2" />
                Download PDF
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent className="pt-6">
          <div className="space-y-6">
            {/* Employee Information */}
            <div>
              <h3 className="font-semibold text-lg mb-3">Employee Information</h3>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p className="text-muted-foreground">Name</p>
                  <p className="font-medium">{profile.full_name}</p>
                </div>
                <div>
                  <p className="text-muted-foreground">Email</p>
                  <p className="font-medium">{profile.email}</p>
                </div>
                {profile.epf_number && (
                  <div>
                    <p className="text-muted-foreground">EPF Number</p>
                    <p className="font-medium">{profile.epf_number}</p>
                  </div>
                )}
                {profile.socso_number && (
                  <div>
                    <p className="text-muted-foreground">SOCSO Number</p>
                    <p className="font-medium">{profile.socso_number}</p>
                  </div>
                )}
              </div>
            </div>

            <Separator />

            {/* Earnings */}
            <div>
              <h3 className="font-semibold text-lg mb-3">Earnings</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Regular Hours ({payroll.regular_hours.toFixed(2)}h)</span>
                  <span className="font-medium">
                    RM{" "}
                    {(
                      (payroll.gross_pay / (payroll.regular_hours + payroll.overtime_hours)) *
                      payroll.regular_hours
                    ).toFixed(2)}
                  </span>
                </div>
                {payroll.overtime_hours > 0 && (
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Overtime Hours ({payroll.overtime_hours.toFixed(2)}h)</span>
                    <span className="font-medium">
                      RM{" "}
                      {(
                        (payroll.gross_pay / (payroll.regular_hours + payroll.overtime_hours)) *
                        payroll.overtime_hours
                      ).toFixed(2)}
                    </span>
                  </div>
                )}
                <Separator />
                <div className="flex justify-between font-semibold text-base">
                  <span>Gross Pay</span>
                  <span className="text-green-600">RM {payroll.gross_pay.toFixed(2)}</span>
                </div>
              </div>
            </div>

            <Separator />

            {/* Deductions */}
            <div>
              <h3 className="font-semibold text-lg mb-3">Deductions</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">EPF (Employee 11%)</span>
                  <span className="font-medium text-red-600">- RM {payroll.epf_employee.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">SOCSO (Employee)</span>
                  <span className="font-medium text-red-600">- RM {payroll.socso_employee.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">EIS (Employee 0.2%)</span>
                  <span className="font-medium text-red-600">- RM {payroll.eis_employee.toFixed(2)}</span>
                </div>
                <Separator />
                <div className="flex justify-between font-semibold text-base">
                  <span>Total Deductions</span>
                  <span className="text-red-600">
                    - RM {(payroll.epf_employee + payroll.socso_employee + payroll.eis_employee).toFixed(2)}
                  </span>
                </div>
              </div>
            </div>

            <Separator />

            {/* Employer Contributions */}
            <div>
              <h3 className="font-semibold text-lg mb-3">Employer Contributions</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">EPF (Employer 12-13%)</span>
                  <span className="font-medium">RM {payroll.epf_employer.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">SOCSO (Employer)</span>
                  <span className="font-medium">RM {payroll.socso_employer.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">EIS (Employer 0.2%)</span>
                  <span className="font-medium">RM {payroll.eis_employer.toFixed(2)}</span>
                </div>
              </div>
            </div>

            <Separator />

            {/* Net Pay */}
            <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg print-net-pay">
              <div className="flex justify-between items-center">
                <span className="text-lg font-semibold">Net Pay</span>
                <span className="text-2xl font-bold text-green-600">RM {payroll.net_pay.toFixed(2)}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </>
  )
}
