"use client"

import { useState } from "react"
import {
  ResponsiveContainer,
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  LineChart as RechartsLineChart,
  Line,
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  Sector,
} from "recharts"
import { Badge } from "@/components/ui/badge"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

// Sample data - in a real app, this would come from your API
const memberGrowthData = [
  { day: "Jan", registrations: 12 },
  { day: "Feb", registrations: 19 },
  { day: "Mar", registrations: 15 },
  { day: "Apr", registrations: 27 },
  { day: "May", registrations: 24 },
  { day: "Jun", registrations: 32 },
  { day: "Jul", registrations: 38 },
]

const equipmentStatusData = [
  { name: "Available", value: 18 },
  { name: "Maintenance", value: 4 },
  { name: "Out of Service", value: 2 },
]

const workoutPlanData = [
  { name: "Strength Training", count: 45 },
  { name: "Cardio Blast", count: 32 },
  { name: "Yoga Fundamentals", count: 28 },
  { name: "HIIT Program", count: 22 },
  { name: "Total Body Reboot", count: 18 },
]

const recentUsers = [
  {
    id: 1,
    name: "Joyce Attawah Elli",
    email: "elliattawah23@gmail.com",
    role: "member",
    date: "Apr 28, 2025",
  },
  {
    id: 2,
    name: "Jefferson Forson",
    email: "jeffersonforson24@gmail.com",
    role: "member",
    date: "Apr 26, 2025",
  },
  {
    id: 3,
    name: "Max Grip",
    email: "maxgrip500@gmail.com",
    role: "member",
    date: "Apr 26, 2025",
  },
  {
    id: 4,
    name: "Jayda Mensah",
    email: "jayda@gmail.com",
    role: "member",
    date: "Apr 24, 2025",
  },
  {
    id: 5,
    name: "Nana Yaw",
    email: "biggie@gmail.com",
    role: "member",
    date: "Apr 19, 2025",
  },
]

const equipmentList = [
  {
    id: 18,
    name: "New and trancate",
    status: "out_of_service",
    lastMaintenance: "2025-04-26",
    nextMaintenance: "2025-05-09",
  },
  { id: 3, name: "Treadmill", status: "available", lastMaintenance: "2025-04-07", nextMaintenance: null },
  { id: 4, name: "Elliptical Trainer", status: "available", lastMaintenance: "2025-04-07", nextMaintenance: null },
  { id: 5, name: "Stationary Bike", status: "maintenance", lastMaintenance: "2025-02-05", nextMaintenance: null },
  {
    id: 11,
    name: "Chest Press Machine",
    status: "out_of_service",
    lastMaintenance: "2024-12-17",
    nextMaintenance: null,
  },
]

// Custom active shape for PieChart
const renderActiveShape = (props: any) => {
  const { cx, cy, innerRadius, outerRadius, startAngle, endAngle, fill, payload, percent, value } = props

  return (
    <g>
      <text x={cx} y={cy} dy={-20} textAnchor="middle" fill={fill}>
        {payload.name}
      </text>
      <text x={cx} y={cy} dy={8} textAnchor="middle" fill="#333" fontSize={20} fontWeight="bold">
        {value}
      </text>
      <text x={cx} y={cy} dy={30} textAnchor="middle" fill="#999">
        {`${(percent * 100).toFixed(0)}%`}
      </text>
      <Sector
        cx={cx}
        cy={cy}
        innerRadius={innerRadius}
        outerRadius={outerRadius + 5}
        startAngle={startAngle}
        endAngle={endAngle}
        fill={fill}
      />
    </g>
  )
}

// Bar Chart Component
export function BarChart() {
  return (
    <div className="h-[300px] w-full">
      <ResponsiveContainer width="100%" height="100%">
        <RechartsBarChart data={workoutPlanData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="name" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Bar dataKey="count" fill="#3b82f6" />
        </RechartsBarChart>
      </ResponsiveContainer>
    </div>
  )
}

// Line Chart Component
export function LineChart() {
  return (
    <div className="h-[300px] w-full">
      <ResponsiveContainer width="100%" height="100%">
        <RechartsLineChart data={memberGrowthData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="day" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Line type="monotone" dataKey="registrations" stroke="#3b82f6" activeDot={{ r: 8 }} strokeWidth={2} />
        </RechartsLineChart>
      </ResponsiveContainer>
    </div>
  )
}

// Pie Chart Component
export function PieChart({ data = equipmentStatusData, colors = ["#22c55e", "#eab308", "#ef4444"] }) {
  const [activeIndex, setActiveIndex] = useState(0)

  return (
    <div className="h-[300px] w-full">
      <ResponsiveContainer width="100%" height="100%">
        <RechartsPieChart>
          <Pie
            activeIndex={activeIndex}
            activeShape={renderActiveShape}
            data={data}
            cx="50%"
            cy="50%"
            innerRadius={60}
            outerRadius={80}
            fill="#8884d8"
            dataKey="value"
            onMouseEnter={(_, index) => setActiveIndex(index)}
          >
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={colors[index % colors.length]} />
            ))}
          </Pie>
        </RechartsPieChart>
      </ResponsiveContainer>
    </div>
  )
}

// Recent Registrations Component
export function RecentRegistrations() {
  return (
    <div className="space-y-4">
      {recentUsers.map((user) => (
        <div key={user.id} className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Avatar className="h-9 w-9">
              <AvatarFallback className="bg-blue-100 text-blue-600">
                {user.name
                  .split(" ")
                  .map((n) => n[0])
                  .join("")
                  .substring(0, 2)
                  .toUpperCase()}
              </AvatarFallback>
            </Avatar>
            <div>
              <p className="text-sm font-medium">{user.name}</p>
              <p className="text-xs text-gray-500">{user.email}</p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Badge variant={user.role === "member" ? "default" : "secondary"}>{user.role}</Badge>
            <span className="text-xs text-gray-500">{user.date}</span>
          </div>
        </div>
      ))}
    </div>
  )
}

// Equipment Status Table Component
export function EquipmentStatusTable() {
  return (
    <div className="space-y-2">
      {equipmentList.map((equipment) => (
        <div key={equipment.id} className="flex items-center justify-between border-b pb-2">
          <div>
            <p className="text-sm font-medium">{equipment.name}</p>
            <p className="text-xs text-gray-500">Last maintained: {equipment.lastMaintenance || "N/A"}</p>
          </div>
          <Badge
            variant={
              equipment.status === "available"
                ? "success"
                : equipment.status === "maintenance"
                  ? "warning"
                  : "destructive"
            }
          >
            {equipment.status.replace("_", " ")}
          </Badge>
        </div>
      ))}
    </div>
  )
}
