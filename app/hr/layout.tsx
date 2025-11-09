import type React from "react"
import { HRNav } from "@/components/hr-nav"

export default function HRLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen">
      <HRNav />
      {children}
    </div>
  )
}
