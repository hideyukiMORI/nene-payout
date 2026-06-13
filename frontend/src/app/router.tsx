import { Navigate, Route, Routes } from 'react-router-dom'
import { VendorsPage } from '@/pages/vendors/VendorsPage'
import { VendorCreatePage } from '@/pages/vendors/VendorCreatePage'
import { VendorEditPage } from '@/pages/vendors/VendorEditPage'
import { InvoicesPage } from '@/pages/invoices/InvoicesPage'
import { InvoiceCreatePage } from '@/pages/invoices/InvoiceCreatePage'
import { InvoiceEditPage } from '@/pages/invoices/InvoiceEditPage'
import { InvoicePayPage } from '@/pages/invoices/InvoicePayPage'
import { PaymentsPage } from '@/pages/payments/PaymentsPage'
import { LoginPage } from '@/pages/login/LoginPage'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { AuthGate } from './auth-gate'
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
        <Route path="/received-invoices" element={<InvoicesPage />} />
        <Route path="/received-invoices/new" element={<InvoiceCreatePage />} />
        <Route path="/received-invoices/:receivedInvoiceId/edit" element={<InvoiceEditPage />} />
        <Route path="/received-invoices/:receivedInvoiceId/pay" element={<InvoicePayPage />} />
        <Route path="/vendors" element={<VendorsPage />} />
        <Route path="/vendors/new" element={<VendorCreatePage />} />
        <Route path="/vendors/:vendorId/edit" element={<VendorEditPage />} />
        <Route path="/payments" element={<PaymentsPage />} />
      </Route>
      <Route path="/" element={<Navigate to="/received-invoices" replace />} />
      <Route path="*" element={<Navigate to="/received-invoices" replace />} />
    </Routes>
  )
}
