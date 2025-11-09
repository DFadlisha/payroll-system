import type React from "react"
import { StaffNav } from "@/components/staff-nav"

export default function StaffLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen">
      <StaffNav />
      {children}
    </div>
  )
}
