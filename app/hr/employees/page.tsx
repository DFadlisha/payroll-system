import { redirect } from "next/navigation"
import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { Users } from "lucide-react"
import { AddEmployeeDialog } from "@/components/add-employee-dialog"
import { EditEmployeeDialog } from "@/components/edit-employee-dialog"
import { ViewEmployeeDialog } from "@/components/view-employee-dialog"

export default async function EmployeesPage() {
  const supabase = await createClient()

  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) {
    redirect("/auth/login")
  }

  const { data: profile } = await supabase.from("profiles").select("*").eq("id", user.id).single()

  if (!profile || profile.role !== "hr") {
    redirect("/staff")
  }

  const { data: employees } = await supabase
    .from("profiles")
    .select("*")
    .eq("company_id", profile.company_id)
    .order("full_name")

  // Count by type
  const totalEmployees = employees?.length || 0
  const fullTimeCount = employees?.filter((e: any) => e.employment_type === "permanent").length || 0
  const partTimeCount = employees?.filter((e: any) => e.employment_type === "part-time").length || 0
  const internCount = employees?.filter((e: any) => e.employment_type === "intern").length || 0

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100">
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-2">
              <Users className="h-8 w-8" />
              Employee Management
            </h1>
            <p className="text-gray-600">View and manage all employees</p>
          </div>
          <AddEmployeeDialog companyId={profile.company_id} />
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold">{totalEmployees}</div>
              <p className="text-sm text-muted-foreground">Total Employees</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-green-600">{fullTimeCount}</div>
              <p className="text-sm text-muted-foreground">Full-Time</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-blue-600">{partTimeCount}</div>
              <p className="text-sm text-muted-foreground">Part-Time</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-orange-600">{internCount}</div>
              <p className="text-sm text-muted-foreground">Interns</p>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>All Employees</CardTitle>
            <CardDescription>Complete list of staff members</CardDescription>
          </CardHeader>
          <CardContent>
            {employees && employees.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Email</TableHead>
                      <TableHead>Role</TableHead>
                      <TableHead>Employment Type</TableHead>
                      <TableHead>Basic Salary</TableHead>
                      <TableHead>EPF Number</TableHead>
                      <TableHead>SOCSO Number</TableHead>
                      <TableHead>Citizenship</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {employees.map((employee: any) => (
                      <TableRow key={employee.id}>
                        <TableCell className="font-medium">{employee.full_name}</TableCell>
                        <TableCell>{employee.email}</TableCell>
                        <TableCell>
                          <Badge variant={employee.role === "hr" ? "default" : "secondary"}>
                            {employee.role.toUpperCase()}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge 
                            variant="outline" 
                            className={`capitalize ${
                              employee.employment_type === 'intern' 
                                ? 'bg-orange-50 text-orange-700 border-orange-200' 
                                : employee.employment_type === 'part-time'
                                ? 'bg-blue-50 text-blue-700 border-blue-200'
                                : 'bg-green-50 text-green-700 border-green-200'
                            }`}
                          >
                            {employee.employment_type === 'permanent' ? 'Full-Time' : (employee.employment_type || 'Permanent').replace('-', ' ')}
                          </Badge>
                        </TableCell>
                        <TableCell>RM {employee.basic_salary?.toFixed(2) || '0.00'}</TableCell>
                        <TableCell>
                          {employee.employment_type === 'intern' ? (
                            <span className="text-xs text-gray-400">N/A</span>
                          ) : (
                            employee.epf_number || "-"
                          )}
                        </TableCell>
                        <TableCell>
                          {employee.employment_type === 'intern' ? (
                            <span className="text-xs text-gray-400">N/A</span>
                          ) : (
                            employee.socso_number || "-"
                          )}
                        </TableCell>
                        <TableCell className="capitalize">{employee.citizenship_status || "-"}</TableCell>
                        <TableCell>
                          <div className="flex items-center gap-1">
                            <ViewEmployeeDialog employee={employee} />
                            <EditEmployeeDialog employee={employee} />
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <Users className="h-12 w-12 mx-auto mb-3 opacity-50" />
                <p>No employees found</p>
                <p className="text-sm mt-2">Click "Add Employee" to add your first employee</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
