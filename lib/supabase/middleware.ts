import { createClient as createSupabaseClient } from "@supabase/supabase-js"
import { NextResponse, type NextRequest } from "next/server"

function createNoopSupabaseClient() {
  const noop = {
    auth: {
      getUser: async (_token?: string) => ({ data: { user: null }, error: null }),
      refreshSession: async (_opts?: any) => ({ data: { session: null, user: null }, error: null }),
      signOut: async () => ({ error: null }),
    },
    from: (_table: string) => {
      const chain: any = {
        select: (_cols?: any) => chain,
        eq: (_col?: any, _val?: any) => chain,
        maybeSingle: async () => ({ data: null, error: null }),
      }

      return chain
    },
  }

  return noop as any
}

export async function updateSession(request: NextRequest) {
  const supabaseResponse = NextResponse.next({
    request,
  })

  // Get auth tokens from cookies
  const accessToken = request.cookies.get("sb-access-token")?.value
  const refreshToken = request.cookies.get("sb-refresh-token")?.value

  const supabase = (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY)
    ? (process.env.ENV_ENFORCE_SUPABASE === '1'
        ? (() => { throw new Error('Missing Supabase env vars: NEXT_PUBLIC_SUPABASE_URL and NEXT_PUBLIC_SUPABASE_ANON_KEY') })()
        : (console.warn('Supabase env vars missing — using noop client in middleware.'), createNoopSupabaseClient())
      )
    : createSupabaseClient(process.env.NEXT_PUBLIC_SUPABASE_URL, process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY, {
        auth: { persistSession: false },
        global: {
          headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : {},
        },
      })

  let user = null

  // Try to get user with current access token
  if (accessToken) {
    const { data } = await supabase.auth.getUser(accessToken)
    user = data.user
  }

  // If no user and we have refresh token, try to refresh
  if (!user && refreshToken) {
    const { data, error } = await supabase.auth.refreshSession({ refresh_token: refreshToken })
    if (data?.session && !error) {
      user = data.user
      // Update cookies with new tokens
      supabaseResponse.cookies.set("sb-access-token", data.session.access_token, {
        path: "/",
        secure: true,
        sameSite: "lax",
      })
      supabaseResponse.cookies.set("sb-refresh-token", data.session.refresh_token, {
        path: "/",
        secure: true,
        sameSite: "lax",
      })
    }
  }

  // Redirect unauthenticated users to login
  if (!user && !request.nextUrl.pathname.startsWith("/auth") && request.nextUrl.pathname !== "/") {
    const url = request.nextUrl.clone()
    url.pathname = "/auth/login"
    return NextResponse.redirect(url)
  }

  // Redirect authenticated users away from auth pages to their dashboard
  if (user && request.nextUrl.pathname.startsWith("/auth")) {
    const url = request.nextUrl.clone()

    // Get user role to redirect to appropriate dashboard
    const userId = (user as any)?.id
    const { data: profile } = await supabase.from("profiles").select("role").eq("id", userId).maybeSingle()

    url.pathname = profile?.role === "hr" ? "/hr" : "/staff"
    return NextResponse.redirect(url)
  }

  return supabaseResponse
}
