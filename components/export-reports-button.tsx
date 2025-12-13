"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from "@/components/ui/dropdown-menu"
import { Download, FileSpreadsheet, FileText } from "lucide-react"
import { exportToCSV, formatPayrollForExport, formatAttendanceForExport, generateStatutoryReport } from "@/lib/export-utils"

interface ExportReportsButtonProps {
  payrollData: any[]
  attendanceData?: any[]
  profiles: any[]
  month: number
  year: number
}

export function ExportReportsButton({ 
  payrollData, 
  attendanceData = [], 
  profiles, 
  month, 
  year 
}: ExportReportsButtonProps) {
  const [isExporting, setIsExporting] = useState(false)

  const monthName = new Date(year, month - 1).toLocaleDateString("en-MY", { month: "long", year: "numeric" })

  const handleExportPayroll = () => {
    setIsExporting(true)
    try {
      const exportData = formatPayrollForExport(payrollData, profiles)
      exportToCSV(exportData, `payroll-report-${year}-${String(month).padStart(2, "0")}`)
    } finally {
      setIsExporting(false)
    }
  }

  const handleExportStatutory = () => {
    setIsExporting(true)
    try {
      const statutoryData = payrollData.map(p => {
        const profile = profiles.find(pr => pr.id === p.user_id)
        return {
          employee_name: profile?.full_name || "Unknown",
          epf_number: profile?.epf_number || "-",
          socso_number: profile?.socso_number || "-",
          basic_salary: p.basic_salary || 0,
          gross_salary: p.gross_salary || 0,
          epf_employee: p.epf_employee || 0,
          epf_employer: p.epf_employer || 0,
          epf_total: (p.epf_employee || 0) + (p.epf_employer || 0),
          socso_employee: p.socso_employee || 0,
          socso_employer: p.socso_employer || 0,
          socso_total: (p.socso_employee || 0) + (p.socso_employer || 0),
          eis_employee: p.eis_employee || 0,
          eis_employer: p.eis_employer || 0,
          eis_total: (p.eis_employee || 0) + (p.eis_employer || 0),
          pcb_tax: p.pcb_tax || 0,
        }
      })
      exportToCSV(statutoryData, `statutory-report-${year}-${String(month).padStart(2, "0")}`)
    } finally {
      setIsExporting(false)
    }
  }

  const handleExportSummary = () => {
    setIsExporting(true)
    try {
      const summary = generateStatutoryReport(payrollData, month, year)
      const epfEmployee = payrollData.reduce((sum, p) => sum + (p.epf_employee || 0), 0)
      const epfEmployer = payrollData.reduce((sum, p) => sum + (p.epf_employer || 0), 0)
      const socsoEmployee = payrollData.reduce((sum, p) => sum + (p.socso_employee || 0), 0)
      const socsoEmployer = payrollData.reduce((sum, p) => sum + (p.socso_employer || 0), 0)
      const eisEmployee = payrollData.reduce((sum, p) => sum + (p.eis_employee || 0), 0)
      const eisEmployer = payrollData.reduce((sum, p) => sum + (p.eis_employer || 0), 0)
      const pcbTotal = payrollData.reduce((sum, p) => sum + (p.pcb_tax || 0), 0)
      const totalGross = payrollData.reduce((sum, p) => sum + (p.gross_salary || 0), 0)
      const totalNet = payrollData.reduce((sum, p) => sum + (p.net_salary || 0), 0)

      const summaryData = [{
        period: monthName,
        total_employees: payrollData.length,
        total_gross_salary: totalGross.toFixed(2),
        total_net_salary: totalNet.toFixed(2),
        epf_employee: epfEmployee.toFixed(2),
        epf_employer: epfEmployer.toFixed(2),
        epf_total: (epfEmployee + epfEmployer).toFixed(2),
        socso_employee: socsoEmployee.toFixed(2),
        socso_employer: socsoEmployer.toFixed(2),
        socso_total: (socsoEmployee + socsoEmployer).toFixed(2),
        eis_employee: eisEmployee.toFixed(2),
        eis_employer: eisEmployer.toFixed(2),
        eis_total: (eisEmployee + eisEmployer).toFixed(2),
        pcb_total: pcbTotal.toFixed(2),
      }]
      exportToCSV(summaryData, `statutory-summary-${year}-${String(month).padStart(2, "0")}`)
    } finally {
      setIsExporting(false)
    }
  }

  const handleExportAttendance = () => {
    if (attendanceData.length === 0) {
      alert("No attendance data to export")
      return
    }
    setIsExporting(true)
    try {
      const exportData = formatAttendanceForExport(attendanceData, profiles)
      exportToCSV(exportData, `attendance-report-${year}-${String(month).padStart(2, "0")}`)
    } finally {
      setIsExporting(false)
    }
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button disabled={isExporting || payrollData.length === 0}>
          <Download className="h-4 w-4 mr-2" />
          {isExporting ? "Exporting..." : "Export Reports"}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56">
        <DropdownMenuLabel>Download as CSV</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={handleExportPayroll}>
          <FileSpreadsheet className="h-4 w-4 mr-2" />
          Full Payroll Report
        </DropdownMenuItem>
        <DropdownMenuItem onClick={handleExportStatutory}>
          <FileText className="h-4 w-4 mr-2" />
          Statutory Contributions
        </DropdownMenuItem>
        <DropdownMenuItem onClick={handleExportSummary}>
          <FileText className="h-4 w-4 mr-2" />
          Monthly Summary
        </DropdownMenuItem>
        {attendanceData.length > 0 && (
          <>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={handleExportAttendance}>
              <FileSpreadsheet className="h-4 w-4 mr-2" />
              Attendance Report
            </DropdownMenuItem>
          </>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
