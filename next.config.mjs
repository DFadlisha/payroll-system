/** @type {import('next').NextConfig} */
const nextConfig = {
  // Enable static export for Capacitor mobile app
  // Uncomment 'output: export' when building for mobile app stores
  // output: 'export',
  
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  // Required for static export
  trailingSlash: true,
}

export default nextConfig
