// Export utilities for generating CSV reports

export interface PayrollExportData {
  employee_name: string
  employee_email: string
  employment_type: string
  basic_salary: number
  gross_salary: number
  epf_employee: number
  socso_employee: number
  eis_employee: number
  pcb_tax: number
  total_deductions: number
  net_salary: number
  epf_employer: number
  socso_employer: number
  eis_employer: number
}

export interface AttendanceExportData {
  employee_name: string
  employee_email: string
  date: string
  clock_in: string
  clock_out: string | null
  total_hours: number | null
  clock_in_location: string | null
  clock_out_location: string | null
}

export function exportToCSV(data: any[], filename: string) {
  if (data.length === 0) {
    alert("No data to export")
    return
  }

  // Get headers from first object keys
  const headers = Object.keys(data[0])
  
  // Create CSV content
  const csvContent = [
    headers.join(","),
    ...data.map(row => 
      headers.map(header => {
        const value = row[header]
        // Handle strings with commas or quotes
        if (typeof value === "string" && (value.includes(",") || value.includes('"') || value.includes("\n"))) {
          return `"${value.replace(/"/g, '""')}"`
        }
        return value ?? ""
      }).join(",")
    )
  ].join("\n")

  // Create blob and download
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" })
  const link = document.createElement("a")
  const url = URL.createObjectURL(blob)
  
  link.setAttribute("href", url)
  link.setAttribute("download", `${filename}.csv`)
  link.style.visibility = "hidden"
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

export function formatPayrollForExport(payrollData: any[], profiles: any[]): PayrollExportData[] {
  const profileMap = profiles.reduce((acc, p) => {
    acc[p.id] = p
    return acc
  }, {} as Record<string, any>)

  return payrollData.map(payroll => {
    const profile = profileMap[payroll.user_id]
    return {
      employee_name: profile?.full_name || "Unknown",
      employee_email: profile?.email || "Unknown",
      employment_type: profile?.employment_type === "permanent" ? "Full-Time" : (profile?.employment_type || "N/A"),
      basic_salary: payroll.basic_salary || 0,
      gross_salary: payroll.gross_salary || 0,
      epf_employee: payroll.epf_employee || 0,
      socso_employee: payroll.socso_employee || 0,
      eis_employee: payroll.eis_employee || 0,
      pcb_tax: payroll.pcb_tax || 0,
      total_deductions: payroll.total_deductions || 0,
      net_salary: payroll.net_salary || 0,
      epf_employer: payroll.epf_employer || 0,
      socso_employer: payroll.socso_employer || 0,
      eis_employer: payroll.eis_employer || 0,
    }
  })
}

export function formatAttendanceForExport(attendanceData: any[], profiles: any[]): AttendanceExportData[] {
  const profileMap = profiles.reduce((acc, p) => {
    acc[p.id] = p
    return acc
  }, {} as Record<string, any>)

  return attendanceData.map(attendance => {
    const profile = profileMap[attendance.user_id]
    return {
      employee_name: profile?.full_name || "Unknown",
      employee_email: profile?.email || "Unknown",
      date: new Date(attendance.clock_in).toLocaleDateString("en-MY"),
      clock_in: new Date(attendance.clock_in).toLocaleTimeString("en-MY"),
      clock_out: attendance.clock_out 
        ? new Date(attendance.clock_out).toLocaleTimeString("en-MY") 
        : null,
      total_hours: attendance.total_hours,
      clock_in_location: attendance.clock_in_address || null,
      clock_out_location: attendance.clock_out_address || null,
    }
  })
}

export function generateStatutoryReport(payrollData: any[], month: number, year: number) {
  const epfTotal = payrollData.reduce((sum, p) => sum + (p.epf_employee || 0) + (p.epf_employer || 0), 0)
  const socsoTotal = payrollData.reduce((sum, p) => sum + (p.socso_employee || 0) + (p.socso_employer || 0), 0)
  const eisTotal = payrollData.reduce((sum, p) => sum + (p.eis_employee || 0) + (p.eis_employer || 0), 0)
  const pcbTotal = payrollData.reduce((sum, p) => sum + (p.pcb_tax || 0), 0)

  return {
    period: `${year}-${String(month).padStart(2, "0")}`,
    epf_total: epfTotal.toFixed(2),
    socso_total: socsoTotal.toFixed(2),
    eis_total: eisTotal.toFixed(2),
    pcb_total: pcbTotal.toFixed(2),
    employee_count: payrollData.length,
  }
}
