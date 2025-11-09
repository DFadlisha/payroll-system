export interface Profile {
  id: string
  email: string
  full_name: string
  role: "staff" | "hr"
  company_id?: string
  employment_type?: "permanent" | "contract" | "intern"
  epf_number?: string
  socso_number?: string
  citizenship_status?: "citizen" | "permanent_resident" | "foreigner"
  basic_salary: number
  hourly_rate?: number
  created_at: string
  updated_at: string
}

export interface Attendance {
  id: string
  user_id: string
  clock_in: string
  clock_out?: string
  total_hours?: number
  overtime_hours: number
  status: "active" | "completed"
  created_at: string
}

export interface Payroll {
  id: string
  user_id: string
  month: number
  year: number
  regular_hours: number
  overtime_hours: number
  gross_pay: number
  epf_employee: number
  epf_employer: number
  socso_employee: number
  socso_employer: number
  eis_employee: number
  eis_employer: number
  net_pay: number
  status: "draft" | "finalized" | "paid"
  created_at: string
  updated_at: string
}

export interface Leave {
  id: string
  user_id: string
  leave_type: "annual" | "sick" | "emergency" | "unpaid"
  start_date: string
  end_date: string
  days: number
  reason: string
  status: "pending" | "approved" | "rejected"
  reviewed_by?: string
  reviewed_at?: string
  created_at: string
}

export interface Company {
  id: string
  name: string
  logo_url: string | null
  created_at: string
  updated_at: string
}
