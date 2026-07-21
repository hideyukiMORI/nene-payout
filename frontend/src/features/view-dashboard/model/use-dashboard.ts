import { useReceivedInvoiceList } from '@/entities/received-invoice'
import { useVendorList } from '@/entities/vendor'
import { usePaymentExecutionList } from '@/entities/payment-execution'
import type { MessageKey } from '@/shared/i18n'

export interface DashboardCard {
  key: string
  labelKey: MessageKey
  count: number | null
  to: string
}

// [nene2-exemplar:union-page-state]
// 規約 第2部 UI-1: container hook は判別ユニオンの page state を返す。status 語彙は
// 3値固定（'loading'/'error'/'success'）— 'empty'/'idle' 等の第4値の発明 MUST NOT。
// 空状態は success のデータから導出する（UI-4）。
export type DashboardState =
  | { status: 'loading' }
  | { status: 'error'; retry: () => void }
  | { status: 'success'; cards: DashboardCard[] }

const COUNT_ONLY = { limit: 1, offset: 0 } as const

/**
 * Aggregates per-resource totals (via each list query's `total`) into summary
 * cards. Counts use limit=1 to keep payloads small; the value shown is the
 * server-reported total, not the page length.
 */
export function useDashboard(): DashboardState {
  const pendingInvoices = useReceivedInvoiceList({ ...COUNT_ONLY, status: 'pending' })
  const totalInvoices = useReceivedInvoiceList({ ...COUNT_ONLY, status: null })
  const vendors = useVendorList({ ...COUNT_ONLY, q: null })
  const payments = usePaymentExecutionList({ ...COUNT_ONLY, status: null, receivedInvoiceId: null })

  const queries = [pendingInvoices, totalInvoices, vendors, payments]

  if (queries.some((query) => query.isPending)) {
    return { status: 'loading' }
  }

  if (queries.some((query) => query.isError)) {
    return {
      status: 'error',
      retry: () => {
        queries.forEach((query) => void query.refetch())
      },
    }
  }

  const cards: DashboardCard[] = [
    {
      key: 'pendingInvoices',
      labelKey: 'admin.dashboard.pendingInvoices',
      count: pendingInvoices.data?.total ?? null,
      to: '/received-invoices',
    },
    {
      key: 'totalInvoices',
      labelKey: 'admin.dashboard.totalInvoices',
      count: totalInvoices.data?.total ?? null,
      to: '/received-invoices',
    },
    {
      key: 'vendors',
      labelKey: 'admin.dashboard.vendors',
      count: vendors.data?.total ?? null,
      to: '/vendors',
    },
    {
      key: 'payments',
      labelKey: 'admin.dashboard.payments',
      count: payments.data?.total ?? null,
      to: '/payments',
    },
  ]

  return { status: 'success', cards }
}
