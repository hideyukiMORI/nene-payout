import { Navigate, Route, Routes } from 'react-router-dom'
import { VendorsPage } from '@/pages/vendors/VendorsPage'
import { LoginPage } from '@/pages/login/LoginPage'
import { ForbiddenPage } from '@/pages/forbidden/ForbiddenPage'
import { AuthGate } from './auth-gate'

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/forbidden" element={<ForbiddenPage />} />
      <Route
        path="/vendors"
        element={
          <AuthGate>
            <VendorsPage />
          </AuthGate>
        }
      />
      <Route path="/" element={<Navigate to="/vendors" replace />} />
      <Route path="*" element={<Navigate to="/vendors" replace />} />
    </Routes>
  )
}
