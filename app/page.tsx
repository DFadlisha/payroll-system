import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { 
  Clock, 
  DollarSign, 
  FileText, 
  Users, 
  Building2, 
  GraduationCap, 
  Briefcase, 
  Shield,
  CheckCircle2,
  ArrowRight,
  Sparkles,
  TrendingUp,
  MapPin,
  Calendar
} from "lucide-react"
import Link from "next/link"

export default function HomePage() {
  return (
    <div className="min-h-screen bg-slate-950 text-white overflow-hidden">
      {/* Animated Background */}
      <div className="fixed inset-0 -z-10">
        <div className="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950" />
        <div className="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl" />
        <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-blue-500/5 to-amber-500/5 rounded-full blur-3xl" />
      </div>

      {/* Navigation */}
      <nav className="border-b border-white/10 backdrop-blur-xl bg-slate-950/50 sticky top-0 z-50">
        <div className="container mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="flex items-center">
                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center font-bold text-sm shadow-lg shadow-blue-500/25">
                  NES
                </div>
                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center font-bold text-sm -ml-2 shadow-lg shadow-amber-500/25">
                  MI
                </div>
              </div>
              <span className="font-semibold text-lg hidden sm:block">MI-NES System</span>
            </div>
            <div className="flex items-center gap-3">
              <Button asChild variant="ghost" className="text-slate-300 hover:text-white hover:bg-white/10">
                <Link href="/auth/login">Sign In</Link>
              </Button>
              <Button asChild className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-lg shadow-blue-500/25">
                <Link href="/auth/signup">Get Started</Link>
              </Button>
            </div>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="container mx-auto px-6 pt-20 pb-32">
        <div className="max-w-4xl mx-auto text-center">
          {/* Badge */}
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 mb-8">
            <Sparkles className="w-4 h-4 text-amber-400" />
            <span className="text-sm text-slate-300">Trusted by Malaysian Enterprises</span>
          </div>

          {/* Main Heading */}
          <h1 className="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
            <span className="bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
              Payroll Management
            </span>
            <br />
            <span className="bg-gradient-to-r from-blue-400 via-blue-500 to-amber-400 bg-clip-text text-transparent">
              Made Simple
            </span>
          </h1>

          {/* Subtitle */}
          <p className="text-xl text-slate-400 mb-10 max-w-2xl mx-auto leading-relaxed">
            Complete Malaysian payroll solution with automated EPF, SOCSO & EIS calculations. 
            Streamline attendance, leaves, and payslips in one powerful platform.
          </p>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center mb-16">
            <Button asChild size="lg" className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-xl shadow-blue-500/25 text-lg px-8 h-14">
              <Link href="/auth/signup" className="flex items-center gap-2">
                Start Free Trial
                <ArrowRight className="w-5 h-5" />
              </Link>
            </Button>
            <Button asChild size="lg" variant="outline" className="border-white/20 bg-white/5 hover:bg-white/10 text-lg px-8 h-14">
              <Link href="/auth/login">Sign In to Dashboard</Link>
            </Button>
          </div>

          {/* Trust Indicators */}
          <div className="flex flex-wrap items-center justify-center gap-8 text-slate-500 text-sm">
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-5 h-5 text-green-500" />
              <span>LHDN Compliant</span>
            </div>
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-5 h-5 text-green-500" />
              <span>Bank-Level Security</span>
            </div>
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-5 h-5 text-green-500" />
              <span>24/7 Support</span>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="container mx-auto px-6 pb-24">
        <div className="text-center mb-16">
          <Badge variant="outline" className="mb-4 border-blue-500/30 text-blue-400">Features</Badge>
          <h2 className="text-3xl sm:text-4xl font-bold mb-4">Everything You Need</h2>
          <p className="text-slate-400 max-w-2xl mx-auto">Powerful tools to manage your entire workforce efficiently</p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {[
            {
              icon: Clock,
              title: "Smart Attendance",
              description: "GPS-enabled clock-in/out with real-time tracking and automated timesheets",
              gradient: "from-blue-500 to-cyan-500",
              shadowColor: "shadow-blue-500/20"
            },
            {
              icon: DollarSign,
              title: "Auto Payroll",
              description: "One-click salary calculation with all Malaysian statutory deductions",
              gradient: "from-green-500 to-emerald-500",
              shadowColor: "shadow-green-500/20"
            },
            {
              icon: FileText,
              title: "Digital Payslips",
              description: "Professional payslips with detailed breakdown sent automatically",
              gradient: "from-purple-500 to-pink-500",
              shadowColor: "shadow-purple-500/20"
            },
            {
              icon: Users,
              title: "Leave Portal",
              description: "Easy leave requests with approval workflows and balance tracking",
              gradient: "from-amber-500 to-orange-500",
              shadowColor: "shadow-amber-500/20"
            }
          ].map((feature, index) => (
            <Card key={index} className={`group bg-white/5 border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-1 ${feature.shadowColor} hover:shadow-xl`}>
              <CardContent className="p-6">
                <div className={`w-14 h-14 rounded-2xl bg-gradient-to-br ${feature.gradient} flex items-center justify-center mb-5 shadow-lg ${feature.shadowColor}`}>
                  <feature.icon className="w-7 h-7 text-white" />
                </div>
                <h3 className="text-xl font-semibold mb-2 text-white">{feature.title}</h3>
                <p className="text-slate-400 text-sm leading-relaxed">{feature.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* Statutory Compliance Section */}
      <section className="container mx-auto px-6 pb-24">
        <div className="bg-gradient-to-br from-white/5 to-white/[0.02] border border-white/10 rounded-3xl p-8 md:p-12">
          <div className="text-center mb-12">
            <Badge variant="outline" className="mb-4 border-green-500/30 text-green-400">100% Compliant</Badge>
            <h2 className="text-3xl sm:text-4xl font-bold mb-4">Malaysian Statutory Ready</h2>
            <p className="text-slate-400 max-w-2xl mx-auto">Automated calculations following the latest Malaysian employment regulations</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                title: "EPF (KWSP)",
                rate: "11% + 13%",
                description: "Employee Provident Fund with employer matching contributions",
                color: "text-blue-400",
                bgColor: "bg-blue-500/10",
                borderColor: "border-blue-500/20"
              },
              {
                title: "SOCSO (PERKESO)",
                rate: "Tiered Rates",
                description: "Social Security with injury and invalidity protection",
                color: "text-green-400",
                bgColor: "bg-green-500/10",
                borderColor: "border-green-500/20"
              },
              {
                title: "EIS",
                rate: "0.2% + 0.2%",
                description: "Employment Insurance for job loss protection",
                color: "text-purple-400",
                bgColor: "bg-purple-500/10",
                borderColor: "border-purple-500/20"
              }
            ].map((item, index) => (
              <div key={index} className={`${item.bgColor} border ${item.borderColor} rounded-2xl p-6 text-center`}>
                <h3 className={`text-xl font-bold mb-1 ${item.color}`}>{item.title}</h3>
                <p className="text-3xl font-bold text-white mb-3">{item.rate}</p>
                <p className="text-slate-400 text-sm">{item.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Companies Section */}
      <section className="container mx-auto px-6 pb-24">
        <div className="text-center mb-12">
          <Badge variant="outline" className="mb-4 border-amber-500/30 text-amber-400">Partnership</Badge>
          <h2 className="text-3xl sm:text-4xl font-bold mb-4">A Trusted Collaboration</h2>
          <p className="text-slate-400">Two established Malaysian companies, one powerful solution</p>
        </div>

        <div className="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
          {/* NES Solution */}
          <div className="bg-gradient-to-br from-blue-500/10 to-blue-500/5 border border-blue-500/20 rounded-3xl p-8">
            <div className="flex items-center gap-4 mb-6">
              <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center font-bold text-xl shadow-lg shadow-blue-500/25">
                NES
              </div>
              <div>
                <h3 className="text-xl font-bold">NES Solution & Network</h3>
                <p className="text-slate-400 text-sm flex items-center gap-1">
                  <MapPin className="w-3 h-3" /> Bukit Baru, Melaka
                </p>
              </div>
            </div>
            <p className="text-slate-300 mb-6 leading-relaxed">
              Service management and training firm specializing in electrical and IT solutions, particularly networking configuration.
            </p>
            <div className="flex flex-wrap gap-2">
              {["IT & Networking", "CCTV Systems", "ICT Training", "Production Support"].map((service) => (
                <span key={service} className="px-3 py-1.5 bg-blue-500/20 text-blue-300 rounded-full text-xs font-medium">
                  {service}
                </span>
              ))}
            </div>
          </div>

          {/* Mentari Infiniti */}
          <div className="bg-gradient-to-br from-amber-500/10 to-amber-500/5 border border-amber-500/20 rounded-3xl p-8">
            <div className="flex items-center gap-4 mb-6">
              <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center font-bold text-xl shadow-lg shadow-amber-500/25">
                MI
              </div>
              <div>
                <h3 className="text-xl font-bold">Mentari Infiniti</h3>
                <p className="text-slate-400 text-sm flex items-center gap-1">
                  <Calendar className="w-3 h-3" /> Est. 2016, Melaka
                </p>
              </div>
            </div>
            <p className="text-slate-300 mb-6 leading-relaxed">
              Multifaceted service provider focusing on ICT solutions, professional training, and general trading across Malaysia.
            </p>
            <div className="flex flex-wrap gap-2">
              {["Software Solutions", "Network Infrastructure", "Training", "Consultancy"].map((service) => (
                <span key={service} className="px-3 py-1.5 bg-amber-500/20 text-amber-300 rounded-full text-xs font-medium">
                  {service}
                </span>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Trusted By Section */}
      <section className="container mx-auto px-6 pb-24">
        <div className="bg-white/5 border border-white/10 rounded-3xl p-8 md:p-12">
          <div className="text-center mb-12">
            <div className="flex items-center justify-center gap-2 mb-4">
              <Building2 className="w-5 h-5 text-slate-400" />
              <span className="text-slate-400 text-sm font-medium uppercase tracking-wider">Trusted Partners</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold">Serving Leading Organizations</h2>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center md:text-left">
              <div className="inline-flex items-center gap-2 mb-4 text-blue-400">
                <GraduationCap className="w-5 h-5" />
                <span className="font-semibold">Education</span>
              </div>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li>UTHM • UTeM • UiTM</li>
                <li>Politeknik Malaysia Melaka</li>
                <li>Kolej Poly-Tech MARA</li>
              </ul>
            </div>
            <div className="text-center md:text-left">
              <div className="inline-flex items-center gap-2 mb-4 text-green-400">
                <Shield className="w-5 h-5" />
                <span className="font-semibold">Government</span>
              </div>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li>MBMB • MBS • MPAG</li>
                <li>MAIM • PERNAMA</li>
                <li>State & Federal Bodies</li>
              </ul>
            </div>
            <div className="text-center md:text-left">
              <div className="inline-flex items-center gap-2 mb-4 text-purple-400">
                <Briefcase className="w-5 h-5" />
                <span className="font-semibold">Corporate</span>
              </div>
              <ul className="space-y-2 text-slate-400 text-sm">
                <li>Sony • Panasonic</li>
                <li>Konica Minolta</li>
                <li>Xepa-Soul Pattinson</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="container mx-auto px-6 pb-24">
        <div className="bg-gradient-to-r from-blue-600 to-blue-700 rounded-3xl p-8 md:p-16 text-center relative overflow-hidden">
          {/* Decorative elements */}
          <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
          <div className="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2" />
          
          <div className="relative z-10">
            <TrendingUp className="w-12 h-12 mx-auto mb-6 text-blue-200" />
            <h2 className="text-3xl sm:text-4xl font-bold mb-4">Ready to Streamline Your Payroll?</h2>
            <p className="text-blue-100 mb-8 max-w-xl mx-auto">
              Join hundreds of Malaysian businesses who trust MI-NES System for their payroll management needs.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button asChild size="lg" className="bg-white text-blue-600 hover:bg-blue-50 shadow-xl text-lg px-8 h-14">
                <Link href="/auth/signup" className="flex items-center gap-2">
                  Get Started Free
                  <ArrowRight className="w-5 h-5" />
                </Link>
              </Button>
              <Button asChild size="lg" variant="outline" className="border-white/30 text-white hover:bg-white/10 text-lg px-8 h-14">
                <Link href="/auth/login">Sign In</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-white/10 bg-slate-950/50 backdrop-blur-xl">
        <div className="container mx-auto px-6 py-12">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex items-center gap-3">
              <div className="flex items-center">
                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center font-bold text-xs">
                  NES
                </div>
                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center font-bold text-xs -ml-1">
                  MI
                </div>
              </div>
              <span className="text-slate-400 text-sm">MI-NES System</span>
            </div>
            <div className="text-center md:text-right">
              <p className="text-slate-500 text-sm">
                © 2025 MI-NES System. A collaboration between NES Solution & Mentari Infiniti.
              </p>
              <p className="text-slate-600 text-xs mt-1">
                Bukit Baru, Melaka, Malaysia
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
