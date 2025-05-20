"use client"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Button } from "@/components/ui/button"
import { Download, FileSpreadsheet, FileIcon as FilePdf, Users, Dumbbell, Cog, Calendar } from "lucide-react"
import {
  BarChart,
  LineChart,
  PieChart,
  RecentRegistrations,
  EquipmentStatusTable,
} from "@/components/dashboard-widgets"
import { ReportGenerator } from "@/components/report-generator"

export default function DashboardPage() {
  const [showReportModal, setShowReportModal] = useState(false)

  return (
    <div className="flex flex-col gap-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
        <Button onClick={() => setShowReportModal(true)} className="bg-blue-600 hover:bg-blue-700">
          <Download className="mr-2 h-4 w-4" /> Generate Report
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Members</CardTitle>
            <div className="p-2 bg-blue-100 rounded-full text-blue-600">
              <Users className="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">254</div>
            <p className="text-xs text-muted-foreground">+12% from last month</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Trainers</CardTitle>
            <div className="p-2 bg-green-100 rounded-full text-green-600">
              <Dumbbell className="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">15</div>
            <p className="text-xs text-muted-foreground">+2 since last month</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Equipment Count</CardTitle>
            <div className="p-2 bg-yellow-100 rounded-full text-yellow-600">
              <Cog className="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">48</div>
            <p className="text-xs text-muted-foreground">16 in maintenance</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Today's Sessions</CardTitle>
            <div className="p-2 bg-purple-100 rounded-full text-purple-600">
              <Calendar className="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">12</div>
            <p className="text-xs text-muted-foreground">4 completed</p>
          </CardContent>
        </Card>
      </div>

      {/* Charts */}
      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList>
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="analytics">Analytics</TabsTrigger>
          <TabsTrigger value="reports">Reports</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
            <Card className="col-span-4">
              <CardHeader>
                <CardTitle>Member Registration Growth</CardTitle>
              </CardHeader>
              <CardContent className="pl-2">
                <LineChart />
              </CardContent>
            </Card>

            <Card className="col-span-3">
              <CardHeader>
                <CardTitle>Equipment Status</CardTitle>
              </CardHeader>
              <CardContent>
                <PieChart />
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="analytics" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
            <Card className="col-span-4">
              <CardHeader>
                <CardTitle>Workout Plan Popularity</CardTitle>
              </CardHeader>
              <CardContent className="pl-2">
                <BarChart />
              </CardContent>
            </Card>

            <Card className="col-span-3">
              <CardHeader>
                <CardTitle>Member Body Types</CardTitle>
              </CardHeader>
              <CardContent>
                <PieChart
                  data={[
                    { name: "Ectomorph", value: 35 },
                    { name: "Mesomorph", value: 45 },
                    { name: "Endomorph", value: 20 },
                  ]}
                  colors={["#3b82f6", "#10b981", "#f59e0b"]}
                />
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="reports" className="space-y-4">
          <div className="grid gap-4 grid-cols-1">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Available Reports</CardTitle>
                <div className="flex space-x-2">
                  <Button variant="outline" size="sm" onClick={() => setShowReportModal(true)}>
                    <FilePdf className="mr-2 h-4 w-4" /> PDF
                  </Button>
                  <Button variant="outline" size="sm" onClick={() => setShowReportModal(true)}>
                    <FileSpreadsheet className="mr-2 h-4 w-4" /> Excel
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Button
                      variant="outline"
                      className="h-24 flex flex-col items-center justify-center"
                      onClick={() => setShowReportModal(true)}
                    >
                      <Users className="h-8 w-8 mb-2" />
                      <span>Member Report</span>
                    </Button>
                    <Button
                      variant="outline"
                      className="h-24 flex flex-col items-center justify-center"
                      onClick={() => setShowReportModal(true)}
                    >
                      <Dumbbell className="h-8 w-8 mb-2" />
                      <span>Trainer Report</span>
                    </Button>
                    <Button
                      variant="outline"
                      className="h-24 flex flex-col items-center justify-center"
                      onClick={() => setShowReportModal(true)}
                    >
                      <Cog className="h-8 w-8 mb-2" />
                      <span>Equipment Report</span>
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>

      {/* Recent Registrations */}
      <div className="grid gap-4 grid-cols-1 lg:grid-cols-2">
        <Card className="col-span-1">
          <CardHeader>
            <CardTitle>Recent Registrations</CardTitle>
          </CardHeader>
          <CardContent>
            <RecentRegistrations />
          </CardContent>
        </Card>

        <Card className="col-span-1">
          <CardHeader>
            <CardTitle>Equipment Status</CardTitle>
          </CardHeader>
          <CardContent>
            <EquipmentStatusTable />
          </CardContent>
        </Card>
      </div>

      {/* Report Generator Modal */}
      {showReportModal && <ReportGenerator onClose={() => setShowReportModal(false)} />}
    </div>
  )
}
