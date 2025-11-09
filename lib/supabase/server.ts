import { createClient as createSupabaseClient } from "@supabase/supabase-js"
import { cookies } from "next/headers"

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
        single: async () => ({ data: null, error: null }),
        insert: async (_payload?: any) => ({ data: null, error: null }),
        upsert: async (_payload?: any, _opts?: any) => ({ data: null, error: null }),
        update: async (_payload?: any) => ({ data: null, error: null }),
        delete: async () => ({ data: null, error: null }),
        order: (_col?: any, _opts?: any) => chain,
        range: (_from?: number, _to?: number) => chain,
        limit: (_n?: number) => chain,
      }

      return chain
    },
  }

  return noop as any
}

export async function createClient() {
  const cookieStore = await cookies()

  // Get auth tokens from cookies
  const accessToken = cookieStore.get("sb-access-token")?.value
  const refreshToken = cookieStore.get("sb-refresh-token")?.value

  if (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY) {
    if (process.env.ENV_ENFORCE_SUPABASE === '1') {
      throw new Error('Missing Supabase env vars: NEXT_PUBLIC_SUPABASE_URL and NEXT_PUBLIC_SUPABASE_ANON_KEY')
    }
    console.warn('Supabase env vars missing — using noop client on server.')
    return createNoopSupabaseClient()
  }

  const client = createSupabaseClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
    {
      auth: {
        persistSession: false,
      },
      global: {
        headers: accessToken
          ? {
              Authorization: `Bearer ${accessToken}`,
            }
          : {},
      },
    },
  )

  // If we have a refresh token, try to refresh the session
  if (refreshToken && !accessToken) {
    const { data } = await client.auth.refreshSession({ refresh_token: refreshToken })
    if (data.session) {
      // Update cookies with new tokens
      cookieStore.set("sb-access-token", data.session.access_token, {
        path: "/",
        secure: true,
        sameSite: "lax",
      })
      cookieStore.set("sb-refresh-token", data.session.refresh_token, {
        path: "/",
        secure: true,
        sameSite: "lax",
      })
    }
  }

  return client
}
