"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { Button } from "@/components/ui/button"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { Home, Users, DollarSign, FileText, LogOut, Menu, Calendar } from "lucide-react"
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet"
import { useState } from "react"

export function HRNav() {
  const pathname = usePathname()
  const router = useRouter()
  const supabase = createClient()
  const [open, setOpen] = useState(false)

  const handleLogout = async () => {
    await supabase.auth.signOut()
    router.push("/auth/login")
  }

  const navItems = [
    { href: "/hr", label: "Dashboard", icon: Home },
    { href: "/hr/employees", label: "Employees", icon: Users },
    { href: "/hr/employees/payroll", label: "Employee Payroll", icon: Users },
    { href: "/hr/payroll", label: "Generate Payroll", icon: DollarSign },
    { href: "/hr/reports", label: "Reports", icon: FileText },
    { href: "/hr/leaves", label: "Leaves", icon: Calendar },
  ]

  const NavLinks = () => (
    <>
      {navItems.map((item) => {
        const Icon = item.icon
        const isActive = pathname === item.href
        return (
          <Link
            key={item.href}
            href={item.href}
            onClick={() => setOpen(false)}
            className={`flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
              isActive ? "bg-indigo-600 text-white" : "text-gray-700 hover:bg-gray-100"
            }`}
          >
            <Icon className="h-5 w-5" />
            <span>{item.label}</span>
          </Link>
        )
      })}
    </>
  )

  return (
    <>
      {/* Desktop Navigation */}
      <nav className="hidden md:flex bg-white shadow-sm border-b sticky top-0 z-10">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-8">
              <Link href="/hr" className="text-xl font-bold text-indigo-600">
                HR Portal
              </Link>
              <div className="flex gap-2">
                <NavLinks />
              </div>
            </div>
            <Button variant="ghost" onClick={handleLogout} className="text-gray-700">
              <LogOut className="h-5 w-5 mr-2" />
              Logout
            </Button>
          </div>
        </div>
      </nav>

      {/* Mobile Navigation */}
      <nav className="md:hidden bg-white shadow-sm border-b sticky top-0 z-10">
        <div className="flex items-center justify-between h-16 px-4">
          <Link href="/hr" className="text-xl font-bold text-indigo-600">
            HR Portal
          </Link>
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon">
                <Menu className="h-6 w-6" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-64">
              <div className="flex flex-col gap-4 mt-8">
                <NavLinks />
                <Button variant="ghost" onClick={handleLogout} className="justify-start text-gray-700">
                  <LogOut className="h-5 w-5 mr-2" />
                  Logout
                </Button>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </nav>
    </>
  )
}
