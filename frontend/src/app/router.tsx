import { Navigate, Outlet, Route, Routes } from 'react-router-dom'
import { VendorsPage } from '@/pages/vendors/VendorsPage'
import { VendorCreatePage } from '@/pages/vendors/VendorCreatePage'
import { VendorEditPage } from '@/pages/vendors/VendorEditPage'
import { VendorDetailPage } from '@/pages/vendors/VendorDetailPage'
import { InvoicesPage } from '@/pages/invoices/InvoicesPage'
import { InvoiceCreatePage } from '@/pages/invoices/InvoiceCreatePage'
import { InvoiceEditPage } from '@/pages/invoices/InvoiceEditPage'
import { InvoicePayPage } from '@/pages/invoices/InvoicePayPage'
import { InvoicePdfPage } from '@/pages/invoices/InvoicePdfPage'
import { InvoiceDetailPage } from '@/pages/invoices/InvoiceDetailPage'
import { PaymentsPage } from '@/pages/payments/PaymentsPage'
import { PaymentDetailPage } from '@/pages/payments/PaymentDetailPage'
import { AuditLogsPage } from '@/pages/audit-logs/AuditLogsPage'
import { UsersPage } from '@/pages/users/UsersPage'
import { UserInvitePage } from '@/pages/users/UserInvitePage'
import { UserDetailPage } from '@/pages/users/UserDetailPage'
import { UserEditPage } from '@/pages/users/UserEditPage'
import { DashboardPage } from '@/pages/dashboard/DashboardPage'
import { LoginPage } from '@/pages/login/LoginPage'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { AuthGate } from './auth-gate'
import { RequireCapability } from './require-capability'
import { AppLayout } from './layout/AppLayout'

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/forbidden" element={<ForbiddenPage />} />
      <Route
        element={
          <AuthGate>
            <AppLayout />
          </AuthGate>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/received-invoices" element={<InvoicesPage />} />
        <Route path="/received-invoices/new" element={<InvoiceCreatePage />} />
        <Route path="/received-invoices/:receivedInvoiceId" element={<InvoiceDetailPage />} />
        <Route path="/received-invoices/:receivedInvoiceId/edit" element={<InvoiceEditPage />} />
        <Route path="/received-invoices/:receivedInvoiceId/pay" element={<InvoicePayPage />} />
        <Route path="/received-invoices/:receivedInvoiceId/pdf" element={<InvoicePdfPage />} />
        <Route
          element={
            <RequireCapability capability="ManageVendors">
              <Outlet />
            </RequireCapability>
          }
        >
          <Route path="/vendors" element={<VendorsPage />} />
          <Route path="/vendors/new" element={<VendorCreatePage />} />
          <Route path="/vendors/:vendorId" element={<VendorDetailPage />} />
          <Route path="/vendors/:vendorId/edit" element={<VendorEditPage />} />
        </Route>
        <Route path="/payments" element={<PaymentsPage />} />
        <Route path="/payments/:paymentExecutionId" element={<PaymentDetailPage />} />
        <Route
          element={
            <RequireCapability capability="ManageOrganizationSettings">
              <Outlet />
            </RequireCapability>
          }
        >
          <Route path="/users" element={<UsersPage />} />
          <Route path="/users/new" element={<UserInvitePage />} />
          <Route path="/users/:userId" element={<UserDetailPage />} />
          <Route path="/users/:userId/edit" element={<UserEditPage />} />
          <Route path="/audit-logs" element={<AuditLogsPage />} />
        </Route>
      </Route>
      <Route path="/" element={<Navigate to="/dashboard" replace />} />
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  )
}
