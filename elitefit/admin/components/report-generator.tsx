"use client"

import { useState } from "react"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Checkbox } from "@/components/ui/checkbox"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { CalendarIcon, FileIcon as FilePdf, FileSpreadsheet, Loader2 } from "lucide-react"

export function ReportGenerator({ onClose }: { onClose: () => void }) {
  const [reportType, setReportType] = useState("members")
  const [dateRange, setDateRange] = useState<{ from: Date | undefined; to: Date | undefined }>({
    from: undefined,
    to: undefined,
  })
  const [format, setFormat] = useState("pdf")
  const [includeCharts, setIncludeCharts] = useState(true)
  const [includeTables, setIncludeTables] = useState(true)
  const [isGenerating, setIsGenerating] = useState(false)

  const handleGenerate = () => {
    setIsGenerating(true)

    // Simulate API call to generate report
    setTimeout(() => {
      setIsGenerating(false)

      // In a real app, this would trigger a download or open a new tab
      if (format === "pdf") {
        // Simulate PDF download
        alert("PDF report generated and downloaded!")
      } else {
        // Simulate Excel download
        alert("Excel report generated and downloaded!")
      }

      onClose()
    }, 2000)
  }

  return (
    <Dialog open={true} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Generate Report</DialogTitle>
        </DialogHeader>

        <div className="grid gap-4 py-4">
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="report-type" className="text-right">
              Report Type
            </Label>
            <Select value={reportType} onValueChange={setReportType}>
              <SelectTrigger id="report-type" className="col-span-3">
                <SelectValue placeholder="Select report type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="members">Members Report</SelectItem>
                <SelectItem value="trainers">Trainers Report</SelectItem>
                <SelectItem value="equipment">Equipment Report</SelectItem>
                <SelectItem value="sessions">Sessions Report</SelectItem>
                <SelectItem value="financial">Financial Report</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="date-range" className="text-right">
              Date Range
            </Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button id="date-range" variant={"outline"} className="col-span-3 justify-start text-left font-normal">
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {dateRange?.from ? (
                    dateRange.to ? (
                      <>
                        {format(dateRange.from, "LLL dd, y")} - {format(dateRange.to, "LLL dd, y")}
                      </>
                    ) : (
                      format(dateRange.from, "LLL dd, y")
                    )
                  ) : (
                    <span>Pick a date range</span>
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  initialFocus
                  mode="range"
                  defaultMonth={dateRange?.from}
                  selected={dateRange}
                  onSelect={setDateRange}
                  numberOfMonths={2}
                />
              </PopoverContent>
            </Popover>
          </div>

          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="format" className="text-right">
              Format
            </Label>
            <Select value={format} onValueChange={setFormat}>
              <SelectTrigger id="format" className="col-span-3">
                <SelectValue placeholder="Select format" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pdf">PDF Document</SelectItem>
                <SelectItem value="excel">Excel Spreadsheet</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="grid grid-cols-4 items-center gap-4">
            <Label className="text-right">Include</Label>
            <div className="col-span-3 space-y-2">
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="include-charts"
                  checked={includeCharts}
                  onCheckedChange={(checked) => setIncludeCharts(checked as boolean)}
                />
                <Label htmlFor="include-charts">Charts and Graphs</Label>
              </div>
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="include-tables"
                  checked={includeTables}
                  onCheckedChange={(checked) => setIncludeTables(checked as boolean)}
                />
                <Label htmlFor="include-tables">Data Tables</Label>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={onClose}>
            Cancel
          </Button>
          <Button
            onClick={handleGenerate}
            disabled={isGenerating}
            className={format === "pdf" ? "bg-red-600 hover:bg-red-700" : "bg-green-600 hover:bg-green-700"}
          >
            {isGenerating ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Generating...
              </>
            ) : (
              <>
                {format === "pdf" ? <FilePdf className="mr-2 h-4 w-4" /> : <FileSpreadsheet className="mr-2 h-4 w-4" />}
                Generate {format.toUpperCase()}
              </>
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
