import type React from "react"
import type { Metadata, Viewport } from "next"
import { Geist, Geist_Mono } from "next/font/google"
import { Analytics } from "@vercel/analytics/next"
import "./globals.css"

const _geist = Geist({ subsets: ["latin"] })
const _geistMono = Geist_Mono({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "MI-NES Payroll",
  description: "Staff Attendance & Payroll Management System by NES Solution & Network Sdn Bhd",
  generator: "Next.js",
  manifest: "/manifest.json",
  keywords: ["payroll", "attendance", "HR", "Malaysia", "EPF", "SOCSO", "EIS"],
  authors: [{ name: "NES Solution & Network Sdn Bhd" }],
  applicationName: "MI-NES Payroll",
  appleWebApp: {
    capable: true,
    statusBarStyle: "default",
    title: "MI-NES Payroll",
  },
  formatDetection: {
    telephone: false,
  },
  openGraph: {
    type: "website",
    siteName: "MI-NES Payroll",
    title: "MI-NES Payroll System",
    description: "Staff Attendance & Payroll Management System",
  },
}

export const viewport: Viewport = {
  themeColor: "#4F46E5",
  width: "device-width",
  initialScale: 1,
  maximumScale: 1,
  userScalable: false,
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en">
      <head>
        <link rel="apple-touch-icon" href="/icons/icon-192x192.png" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="mobile-web-app-capable" content="yes" />
      </head>
      <body className={`font-sans antialiased`}>
        {children}
        <Analytics />
      </body>
    </html>
  )
}
