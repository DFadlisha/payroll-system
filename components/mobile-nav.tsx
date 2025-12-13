"use client"

import { Home, Clock, Calendar, FileText, User } from "lucide-react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"

export function MobileNav() {
  const pathname = usePathname()

  const navItems = [
    { href: "/mobile", icon: Home, label: "Home" },
    { href: "/mobile/attendance", icon: Clock, label: "Clock" },
    { href: "/mobile/leaves", icon: Calendar, label: "Leave" },
    { href: "/mobile/payslips", icon: FileText, label: "Pay" },
    { href: "/mobile/profile", icon: User, label: "Me" },
  ]

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800">
      <div className="flex justify-around items-center px-1 py-1 max-w-lg mx-auto">
        {navItems.map((item) => {
          const Icon = item.icon
          const isActive = pathname === item.href
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                "flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl transition-all duration-300 min-w-[56px]",
                isActive
                  ? "bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30"
                  : "text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 active:scale-95"
              )}
            >
              <Icon className={cn("h-5 w-5 transition-transform", isActive && "scale-110")} />
              <span className="text-[10px] font-medium">{item.label}</span>
            </Link>
          )
        })}
      </div>
      {/* Safe area for iOS */}
      <div className="h-safe-area-inset-bottom bg-white/90 dark:bg-slate-900/90" />
    </nav>
  )
}
