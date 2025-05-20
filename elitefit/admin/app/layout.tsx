import type React from "react"
import { SidebarProvider } from "@/components/ui/sidebar"
import { ThemeProvider } from "@/components/theme-provider"
import AdminLayout from "@/components/admin-layout"
import "./globals.css"

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <head>
        <title>EliteFit Admin Dashboard</title>
        <meta name="description" content="EliteFit Gym Management System" />
      </head>
      <body>
        <ThemeProvider>
          <SidebarProvider>
            <AdminLayout>{children}</AdminLayout>
          </SidebarProvider>
        </ThemeProvider>
      </body>
    </html>
  )
}

export const metadata = {
      generator: 'v0.dev'
    };
