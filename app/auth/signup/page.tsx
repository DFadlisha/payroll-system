"use client"

import type React from "react"

import { createClient } from "@/lib/supabase/client"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useState, useEffect } from "react"
import Image from "next/image"
import { Eye, EyeOff } from "lucide-react"

interface Company {
  id: string
  name: string
  logo_url: string | null
}

export default function SignUpPage() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [showPassword, setShowPassword] = useState(false)
  const [fullName, setFullName] = useState("")
  const [role, setRole] = useState<"staff" | "hr" | "intern">("staff")
  const [employmentType, setEmploymentType] = useState<"part-time" | "permanent" | "intern">("permanent")
  const [basicSalary, setBasicSalary] = useState("")
  const [selectedCompany, setSelectedCompany] = useState<string>("")
  const [companies, setCompanies] = useState<Company[]>([])
  const [error, setError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()

  useEffect(() => {
    const fetchCompanies = async () => {
      const supabase = createClient()
      const { data } = await supabase.from("companies").select("*").order("name")
      if (data && data.length > 0) {
        setCompanies(data)
        if (data.length === 1) {
          setSelectedCompany(data[0].id)
        }
        return
      }

      // If no companies returned, provide a dev-only demo company so UI can be tested
      if (process.env.NODE_ENV === "development") {
        const demo = [{ id: "demo-company", name: "Demo Company", logo_url: null }]
        setCompanies(demo)
        setSelectedCompany(demo[0].id)
      }
    }
    fetchCompanies()
  }, [])

  const handleSignUp = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!selectedCompany) {
      setError("Please select a company")
      return
    }

    const supabase = createClient()
    setIsLoading(true)
    setError(null)

    try {
      const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
          emailRedirectTo: process.env.NEXT_PUBLIC_DEV_SUPABASE_REDIRECT_URL || `${window.location.origin}/auth/verify`,
          data: {
            full_name: fullName,
            role: role === "intern" ? "staff" : role,
            employment_type: employmentType,
            basic_salary: Number.parseFloat(basicSalary) || 0,
            company_id: selectedCompany,
          },
        },
      })

      if (error) throw error
      
      // If email confirmation is disabled, user is already logged in
      if (data?.session) {
        // HR goes to web dashboard, Staff/Intern goes to mobile app
        router.push(role === "hr" ? "/hr" : "/mobile")
      } else {
        router.push("/auth/verify")
      }
    } catch (error: unknown) {
      setError(error instanceof Error ? error.message : "An error occurred")
    } finally {
      setIsLoading(false)
    }
  }

  const selectedCompanyData = companies.find((c) => c.id === selectedCompany)

  return (
    <div className="flex min-h-svh w-full items-center justify-center p-6 md:p-10 bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="w-full max-w-md">
        <Card>
          <CardHeader className="text-center">
            {selectedCompanyData?.logo_url && (
              <div className="flex justify-center mb-4">
                <div className="relative w-32 h-32">
                  <Image
                    src={selectedCompanyData.logo_url || "/placeholder.svg"}
                    alt={selectedCompanyData.name}
                    fill
                    className="object-contain"
                  />
                </div>
              </div>
            )}
            <CardTitle className="text-2xl font-bold">MI-NES System</CardTitle>
            <CardDescription>Register for the payroll system</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSignUp}>
              <div className="flex flex-col gap-4">
                <div className="grid gap-2">
                  <Label htmlFor="company">Company</Label>
                  <Select value={selectedCompany} onValueChange={setSelectedCompany}>
                    <SelectTrigger id="company">
                      <SelectValue placeholder="Select your company" />
                    </SelectTrigger>
                    <SelectContent>
                      {companies.length === 0 ? (
                        <SelectItem value="__no_companies" disabled>
                          No companies available
                        </SelectItem>
                      ) : (
                        companies.map((company) => (
                          <SelectItem key={company.id} value={company.id}>
                            {company.name}
                          </SelectItem>
                        ))
                      )}
                    </SelectContent>
                  </Select>
                </div>
                <div className="grid gap-2">
                  <Label htmlFor="fullName">Full Name</Label>
                  <Input
                    id="fullName"
                    type="text"
                    placeholder="John Doe"
                    required
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                  />
                </div>
                <div className="grid gap-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    placeholder="your.email@company.com"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </div>
                <div className="grid gap-2">
                  <Label htmlFor="password">Password</Label>
                  <div className="relative">
                    <Input
                      id="password"
                      type={showPassword ? "text" : "password"}
                      required
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="pr-10"
                    />
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                      onClick={() => setShowPassword(!showPassword)}
                    >
                      {showPassword ? (
                        <EyeOff className="h-4 w-4 text-muted-foreground" />
                      ) : (
                        <Eye className="h-4 w-4 text-muted-foreground" />
                      )}
                      <span className="sr-only">
                        {showPassword ? "Hide password" : "Show password"}
                      </span>
                    </Button>
                  </div>
                </div>
                <div className="grid gap-2">
                  <Label htmlFor="role">Role</Label>
                  <Select value={role} onValueChange={(value: "staff" | "hr" | "intern") => {
                    setRole(value)
                    if (value === "intern") {
                      setEmploymentType("intern")
                      setBasicSalary("800") // Fixed intern allowance
                    } else if (value === "staff") {
                      setEmploymentType("permanent")
                      setBasicSalary("") // Reset for manual input
                    } else {
                      setBasicSalary("") // Reset for HR
                    }
                  }}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="staff">Staff</SelectItem>
                      <SelectItem value="intern">Intern</SelectItem>
                      <SelectItem value="hr">HR</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                {role === "staff" && (
                  <div className="grid gap-2">
                    <Label htmlFor="employmentType">Employment Type</Label>
                    <Select value={employmentType} onValueChange={(value: "part-time" | "permanent") => {
                      setEmploymentType(value)
                      if (value === "part-time") {
                        setBasicSalary("1700") // Fixed part-time salary
                      } else {
                        setBasicSalary("") // Reset for manual input
                      }
                    }}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="permanent">Full-Time (Permanent)</SelectItem>
                        <SelectItem value="part-time">Part-Time</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                )}
                {role === "intern" ? (
                  <div className="grid gap-2">
                    <Label>Intern Allowance (RM)</Label>
                    <div className="flex items-center h-10 px-3 rounded-md border border-input bg-muted text-muted-foreground">
                      RM 800.00 (Fixed)
                    </div>
                  </div>
                ) : employmentType === "part-time" ? (
                  <div className="grid gap-2">
                    <Label>Part-Time Salary (RM)</Label>
                    <div className="flex items-center h-10 px-3 rounded-md border border-input bg-muted text-muted-foreground">
                      RM 1,700.00 (Fixed)
                    </div>
                  </div>
                ) : (
                  <div className="grid gap-2">
                    <Label htmlFor="basicSalary">Basic Salary (RM)</Label>
                    <Input
                      id="basicSalary"
                      type="number"
                      step="0.01"
                      placeholder="3000.00"
                      required
                      value={basicSalary}
                      onChange={(e) => setBasicSalary(e.target.value)}
                    />
                  </div>
                )}
                {error && <div className="text-sm text-red-600 bg-red-50 p-3 rounded-md">{error}</div>}
                <Button type="submit" className="w-full" disabled={isLoading}>
                  {isLoading ? "Creating account..." : "Sign Up"}
                </Button>
              </div>
              <div className="mt-4 text-center text-sm text-muted-foreground">
                Already have an account?{" "}
                <Link href="/auth/login" className="text-primary underline underline-offset-4 hover:text-primary/80">
                  Sign in
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
