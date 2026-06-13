import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import {
  render,
  renderHook,
  type RenderHookOptions,
  type RenderHookResult,
  type RenderOptions,
  type RenderResult,
} from '@testing-library/react'
import type { ReactElement, ReactNode } from 'react'
import { MemoryRouter } from 'react-router-dom'
import { I18nProvider } from '@/shared/i18n'

export function createTestQueryClient(): QueryClient {
  return new QueryClient({
    defaultOptions: {
      queries: { retry: false },
      mutations: { retry: false },
    },
  })
}

function Wrapper({ children }: { children: ReactNode }) {
  const queryClient = createTestQueryClient()

  return (
    <I18nProvider>
      <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
    </I18nProvider>
  )
}

export function renderWithProviders(ui: ReactElement, options?: RenderOptions): RenderResult {
  return render(ui, { wrapper: Wrapper, ...options })
}

function RouterWrapper({ children }: { children: ReactNode }) {
  const queryClient = createTestQueryClient()

  return (
    <I18nProvider>
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>{children}</MemoryRouter>
      </QueryClientProvider>
    </I18nProvider>
  )
}

/** Like renderWithProviders but also provides a router (for components with <Link>). */
export function renderWithRouterAndProviders(
  ui: ReactElement,
  options?: RenderOptions,
): RenderResult {
  return render(ui, { wrapper: RouterWrapper, ...options })
}

export function renderHookWithProviders<Result, Props>(
  hook: (initialProps: Props) => Result,
  options?: RenderHookOptions<Props>,
): RenderHookResult<Result, Props> {
  return renderHook(hook, { wrapper: Wrapper, ...options })
}
