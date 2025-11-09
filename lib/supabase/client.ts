import { createClient as createSupabaseClient } from "@supabase/supabase-js"

let client: any = null

function createNoopSupabaseClient() {
  // minimal no-op implementation so build/prerender can run without env vars in development
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

export function createClient(): any {
  if (client) return client

  if (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY) {
    // Warn and return a noop client so builds and local dev can proceed without env vars.
    // In production you should provide real Supabase env vars to enable real data access.
    // To enforce presence of env vars, set ENV_ENFORCE_SUPABASE=1 in your environment.
    if (process.env.ENV_ENFORCE_SUPABASE === '1') {
      throw new Error('Missing Supabase env vars: NEXT_PUBLIC_SUPABASE_URL and NEXT_PUBLIC_SUPABASE_ANON_KEY')
    }
    console.warn('Supabase env vars missing — using noop client. Set NEXT_PUBLIC_SUPABASE_URL and NEXT_PUBLIC_SUPABASE_ANON_KEY for real data.')
    client = createNoopSupabaseClient()
    return client
  }

  client = createSupabaseClient<any, any>(process.env.NEXT_PUBLIC_SUPABASE_URL, process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY, {
    auth: {
      persistSession: true,
      autoRefreshToken: true,
    },
  })

  return client
}
