import { BrowserRouter } from 'react-router-dom'
import { Providers } from '@/app/providers'
import { AppRoutes } from '@/app/router'
import { RootErrorBoundary } from '@/app/root-error-boundary'

export function App() {
  return (
    <RootErrorBoundary>
      <Providers>
        <BrowserRouter>
          <AppRoutes />
        </BrowserRouter>
      </Providers>
    </RootErrorBoundary>
  )
}
