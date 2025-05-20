"use client"

import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { LogOut } from "lucide-react"

export function LogoutModal({
  onClose,
  onConfirm,
}: {
  onClose: () => void
  onConfirm: () => void
}) {
  return (
    <Dialog open={true} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100">
            <LogOut className="h-10 w-10 text-red-600" />
          </div>
          <DialogTitle className="text-center text-xl pt-4">Logout?</DialogTitle>
          <DialogDescription className="text-center">
            Are you sure you want to logout from your admin session?
          </DialogDescription>
        </DialogHeader>
        <DialogFooter className="flex flex-col sm:flex-row sm:justify-center sm:space-x-2">
          <Button variant="destructive" onClick={onConfirm}>
            Yes, Logout
          </Button>
          <Button variant="outline" onClick={onClose}>
            Cancel
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
