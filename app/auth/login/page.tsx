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

export default function LoginPage() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [showPassword, setShowPassword] = useState(false)
  const [selectedCompany, setSelectedCompany] = useState<string>("")
  const [companies, setCompanies] = useState<Company[]>([])
  const [error, setError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()

  // Fetch companies on mount
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

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!selectedCompany) {
      setError("Please select a company")
      return
    }

    const supabase = createClient()
    setIsLoading(true)
    setError(null)

    try {
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password,
      })

      if (error) throw error

      if (data.session) {
        document.cookie = `sb-access-token=${data.session.access_token}; path=/; max-age=3600; secure; samesite=lax`
        document.cookie = `sb-refresh-token=${data.session.refresh_token}; path=/; max-age=604800; secure; samesite=lax`
      }

      // Get user profile and verify company
      const { data: profile } = await supabase
        .from("profiles")
        .select("role, company_id")
        .eq("id", data.user.id)
        .single()

      // Check if user belongs to selected company
      if (profile?.company_id !== selectedCompany) {
        await supabase.auth.signOut()
        document.cookie = "sb-access-token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT"
        document.cookie = "sb-refresh-token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT"
        throw new Error("You do not have access to this company")
      }

      // HR goes to web dashboard, Staff/Intern goes to mobile app
      router.push(profile?.role === "hr" ? "/hr" : "/mobile")
    } catch (error: unknown) {
      setError(error instanceof Error ? error.message : "An error occurred")
    } finally {
      setIsLoading(false)
    }
  }

  const selectedCompanyData = companies.find((c) => c.id === selectedCompany)

  return (
    <div className="flex min-h-svh w-full items-center justify-center p-6 md:p-10 bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="w-full max-w-sm">
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
            <CardDescription>Sign in to access your account</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleLogin}>
              <div className="flex flex-col gap-6">
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
                {error && <div className="text-sm text-red-600 bg-red-50 p-3 rounded-md">{error}</div>}
                <Button type="submit" className="w-full" disabled={isLoading}>
                  {isLoading ? "Signing in..." : "Sign In"}
                </Button>
              </div>
              <div className="mt-4 text-center text-sm text-muted-foreground">
                Don&apos;t have an account?{" "}
                <Link href="/auth/signup" className="text-primary underline underline-offset-4 hover:text-primary/80">
                  Sign up
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
