import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Clock, DollarSign, FileText, Users, Building2, GraduationCap, Briefcase, Shield } from "lucide-react"
import Link from "next/link"

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      {/* Hero Section */}
      <div className="container mx-auto px-4 py-12">
        {/* Company Logos */}
        <div className="flex justify-center items-center gap-8 mb-8">
          <div className="text-center">
            <div className="w-24 h-24 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-2 border-2 border-blue-200">
              <span className="text-2xl font-bold text-blue-600">NES</span>
            </div>
            <p className="text-xs text-gray-500">NES Solution</p>
          </div>
          <div className="text-3xl text-gray-300">×</div>
          <div className="text-center">
            <div className="w-24 h-24 bg-white rounded-full shadow-lg flex items-center justify-center mx-auto mb-2 border-2 border-orange-200">
              <span className="text-2xl font-bold text-orange-600">MI</span>
            </div>
            <p className="text-xs text-gray-500">Mentari Infiniti</p>
          </div>
        </div>

        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold text-gray-900 mb-4 text-balance">MI-NES System</h1>
          <p className="text-lg text-blue-600 font-medium mb-2">
            "Quality For A Sustainable Future" • "Leading The Way To A Brighter Tomorrow"
          </p>
          <p className="text-xl text-gray-600 mb-8 text-pretty max-w-3xl mx-auto">
            Malaysian Payroll & Attendance Management System - Comprehensive solution for managing staff attendance,
            payroll calculations with EPF, SOCSO, and EIS deductions
          </p>
          <div className="flex gap-4 justify-center">
            <Button asChild size="lg" className="bg-blue-600 hover:bg-blue-700">
              <Link href="/auth/login">Sign In</Link>
            </Button>
            <Button asChild variant="outline" size="lg">
              <Link href="/auth/signup">Create Account</Link>
            </Button>
          </div>
        </div>

        {/* Features Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <Clock className="h-10 w-10 text-blue-600 mb-2" />
              <CardTitle>Attendance Tracking</CardTitle>
              <CardDescription>Easy clock-in/out system with GPS location tracking</CardDescription>
            </CardHeader>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <DollarSign className="h-10 w-10 text-green-600 mb-2" />
              <CardTitle>Payroll Calculation</CardTitle>
              <CardDescription>Automated calculations with Malaysian statutory deductions</CardDescription>
            </CardHeader>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <FileText className="h-10 w-10 text-purple-600 mb-2" />
              <CardTitle>Digital Payslips</CardTitle>
              <CardDescription>Detailed payslips with all deductions and contributions</CardDescription>
            </CardHeader>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <Users className="h-10 w-10 text-orange-600 mb-2" />
              <CardTitle>Leave Management</CardTitle>
              <CardDescription>Submit and approve leave requests seamlessly</CardDescription>
            </CardHeader>
          </Card>
        </div>

        {/* Company Info Section */}
        <div className="grid md:grid-cols-2 gap-6 mb-12">
          {/* NES Solution */}
          <Card className="bg-white/80 backdrop-blur">
            <CardHeader>
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <span className="text-lg font-bold text-blue-600">NES</span>
                </div>
                <div>
                  <CardTitle className="text-lg">NES Solution & Network Sdn Bhd</CardTitle>
                  <CardDescription>Est. December 22, 2023 • Bukit Baru, Melaka</CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-gray-600">
                A service management and training firm specializing in electrical and IT solutions, 
                particularly networking configuration.
              </p>
              <div className="space-y-2">
                <p className="text-xs font-semibold text-gray-500 uppercase">Core Services</p>
                <div className="flex flex-wrap gap-2">
                  <span className="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">IT & Networking</span>
                  <span className="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">CCTV Installation</span>
                  <span className="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">ICT Training</span>
                  <span className="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">Production Support</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Mentari Infiniti */}
          <Card className="bg-white/80 backdrop-blur">
            <CardHeader>
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                  <span className="text-lg font-bold text-orange-600">MI</span>
                </div>
                <div>
                  <CardTitle className="text-lg">Mentari Infiniti Sdn. Bhd.</CardTitle>
                  <CardDescription>Est. February 5, 2016 • Melaka</CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <p className="text-sm text-gray-600">
                A multifaceted service provider focusing on ICT solutions, professional training, 
                and general trading.
              </p>
              <div className="space-y-2">
                <p className="text-xs font-semibold text-gray-500 uppercase">Core Services</p>
                <div className="flex flex-wrap gap-2">
                  <span className="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs">Software Solutions</span>
                  <span className="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs">Network Infrastructure</span>
                  <span className="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs">Training & Consultancy</span>
                  <span className="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs">General Trading</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Key Clients */}
        <Card className="bg-white/80 backdrop-blur mb-12">
          <CardHeader>
            <CardTitle className="text-2xl flex items-center gap-2">
              <Building2 className="h-6 w-6" />
              Trusted By Leading Organizations
            </CardTitle>
            <CardDescription>Serving corporate, educational, and government entities across Malaysia</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-3 gap-6">
              <div>
                <h3 className="font-semibold text-sm mb-3 flex items-center gap-2 text-blue-700">
                  <GraduationCap className="h-4 w-4" />
                  Universities & Colleges
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Universiti Tun Hussein Onn Malaysia (UTHM)</li>
                  <li>• Universiti Teknikal Malaysia Melaka (UTeM)</li>
                  <li>• Universiti Teknologi MARA (UiTM)</li>
                  <li>• Politeknik Malaysia Melaka</li>
                  <li>• Kolej Poly-Tech MARA</li>
                </ul>
              </div>
              <div>
                <h3 className="font-semibold text-sm mb-3 flex items-center gap-2 text-green-700">
                  <Shield className="h-4 w-4" />
                  Government Bodies
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Majlis Bandaraya Melaka Bersejarah (MBMB)</li>
                  <li>• Majlis Bandaraya Seremban (MBS)</li>
                  <li>• Majlis Perbandaran Alor Gajah (MPAG)</li>
                  <li>• Melaka Islamic Religious Council (MAIM)</li>
                  <li>• Perwira Niaga Malaysia (PERNAMA)</li>
                </ul>
              </div>
              <div>
                <h3 className="font-semibold text-sm mb-3 flex items-center gap-2 text-purple-700">
                  <Briefcase className="h-4 w-4" />
                  Corporate & Manufacturing
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Sony Corporation</li>
                  <li>• Panasonic Corporation</li>
                  <li>• Konica Minolta Inc.</li>
                  <li>• Xepa-Soul Pattinson</li>
                  <li>• Daidong Engineering</li>
                </ul>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Statutory Compliance */}
        <Card className="bg-white/80 backdrop-blur">
          <CardHeader>
            <CardTitle className="text-2xl">Malaysian Statutory Compliance</CardTitle>
            <CardDescription>Fully compliant with Malaysian employment regulations</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-3 gap-6">
              <div>
                <h3 className="font-semibold text-lg mb-2 text-blue-700">EPF (KWSP)</h3>
                <p className="text-sm text-gray-600">
                  Employee Provident Fund contributions calculated at 11% (employee) and 12-13% (employer) based on
                  official schedules
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-lg mb-2 text-green-700">SOCSO (PERKESO)</h3>
                <p className="text-sm text-gray-600">
                  Social Security Organization contributions with tiered rates for both employee and employer portions
                </p>
              </div>
              <div>
                <h3 className="font-semibold text-lg mb-2 text-purple-700">EIS</h3>
                <p className="text-sm text-gray-600">
                  Employment Insurance System at 0.2% each for employee and employer with salary threshold checks
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Footer */}
        <div className="text-center mt-12 text-sm text-gray-500">
          <p>© 2024 MI-NES System. A collaboration between NES Solution & Network Sdn Bhd and Mentari Infiniti Sdn. Bhd.</p>
          <p className="mt-1">Bukit Baru, Melaka, Malaysia</p>
        </div>
      </div>
    </div>
  )
}
