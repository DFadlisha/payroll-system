"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { Button } from "@/components/ui/button"
import { createClient } from "@/lib/supabase/client"
import { useRouter } from "next/navigation"
import { Home, Calendar, FileText, User, LogOut, Menu, CalendarDays } from "lucide-react"
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet"
import { useState } from "react"

export function StaffNav() {
  const pathname = usePathname()
  const router = useRouter()
  const supabase = createClient()
  const [open, setOpen] = useState(false)

  const handleLogout = async () => {
    await supabase.auth.signOut()
    router.push("/auth/login")
  }

  const navItems = [
    { href: "/staff", label: "Dashboard", icon: Home },
    { href: "/staff/attendance", label: "Attendance", icon: Calendar },
    { href: "/staff/payslips", label: "Payslips", icon: FileText },
    { href: "/staff/leaves", label: "Leaves", icon: CalendarDays },
    { href: "/staff/profile", label: "Profile", icon: User },
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
              isActive
                ? "bg-primary text-primary-foreground"
                : "text-foreground hover:bg-accent hover:text-accent-foreground"
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
      <nav className="hidden md:flex bg-card shadow-sm border-b sticky top-0 z-10">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-8">
              <Link href="/staff" className="text-xl font-bold text-primary">
                MI-NES System
              </Link>
              <div className="flex gap-2">
                <NavLinks />
              </div>
            </div>
            <Button variant="ghost" onClick={handleLogout} className="text-foreground">
              <LogOut className="h-5 w-5 mr-2" />
              Logout
            </Button>
          </div>
        </div>
      </nav>

      {/* Mobile Navigation */}
      <nav className="md:hidden bg-card shadow-sm border-b sticky top-0 z-10">
        <div className="flex items-center justify-between h-16 px-4">
          <Link href="/staff" className="text-xl font-bold text-primary">
            MI-NES System
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
                <Button variant="ghost" onClick={handleLogout} className="justify-start text-foreground">
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
