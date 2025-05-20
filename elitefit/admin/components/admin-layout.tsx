"use client"

import type React from "react"

import { useState } from "react"
import { usePathname } from "next/navigation"
import {
  Sidebar,
  SidebarContent,
  SidebarHeader,
  SidebarFooter,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarTrigger,
} from "@/components/ui/sidebar"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { LayoutDashboard, Users, Dumbbell, Cog, Settings, Archive, LogOut, ChevronDown } from "lucide-react"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { LogoutModal } from "@/components/logout-modal"

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname()
  const [showLogoutModal, setShowLogoutModal] = useState(false)

  return (
    <div className="min-h-screen bg-gray-100">
      <Sidebar>
        <SidebarHeader>
          <div className="flex items-center gap-2 px-2 py-4">
            <div className="flex h-10 w-10 items-center justify-center rounded-md bg-blue-600 text-white">
              <span className="text-xl font-bold">EF</span>
            </div>
            <div className="flex flex-col">
              <span className="text-lg font-bold">EliteFit</span>
              <span className="text-xs text-gray-500">Admin Dashboard</span>
            </div>
          </div>
        </SidebarHeader>

        <SidebarContent>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/dashboard"}>
                <a href="/dashboard">
                  <LayoutDashboard className="h-5 w-5" />
                  <span>Dashboard</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/members"}>
                <a href="/members">
                  <Users className="h-5 w-5" />
                  <span>Members</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/trainers"}>
                <a href="/trainers">
                  <Dumbbell className="h-5 w-5" />
                  <span>Trainers</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/equipment"}>
                <a href="/equipment">
                  <Cog className="h-5 w-5" />
                  <span>Equipment</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/settings"}>
                <a href="/settings">
                  <Settings className="h-5 w-5" />
                  <span>Settings</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>

            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={pathname === "/archive"}>
                <a href="/archive">
                  <Archive className="h-5 w-5" />
                  <span>Archive</span>
                </a>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarContent>

        <SidebarFooter>
          <div className="px-3 py-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="w-full justify-start px-2">
                  <div className="flex items-center gap-2">
                    <Avatar className="h-8 w-8">
                      <AvatarFallback className="bg-blue-100 text-blue-600">A</AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col items-start text-sm">
                      <span className="font-medium">Admin</span>
                      <span className="text-xs text-gray-500">admin@elitefit.com</span>
                    </div>
                    <ChevronDown className="ml-auto h-4 w-4" />
                  </div>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuItem onSelect={() => setShowLogoutModal(true)}>
                  <LogOut className="mr-2 h-4 w-4" />
                  <span>Log out</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </SidebarFooter>
      </Sidebar>

      <div className="flex flex-col md:pl-64">
        <header className="sticky top-0 z-10 flex h-16 items-center gap-4 border-b bg-background px-4 md:px-6">
          <SidebarTrigger />
          <div className="flex flex-1 items-center justify-end gap-4">
            <div className="flex items-center gap-4">
              <span className="text-sm text-gray-500">
                {new Date().toLocaleDateString("en-US", {
                  weekday: "long",
                  year: "numeric",
                  month: "long",
                  day: "numeric",
                })}
              </span>
            </div>
          </div>
        </header>
        <main className="flex-1 p-4 md:p-6">{children}</main>
      </div>

      {showLogoutModal && (
        <LogoutModal
          onClose={() => setShowLogoutModal(false)}
          onConfirm={() => (window.location.href = "../logout.php")}
        />
      )}
    </div>
  )
}
