import { useEffect, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { useTranslation } from '@/shared/i18n'
import { Button } from '@/shared/ui'

/**
 * Dev-only host-page demo: simulates an operator's own system embedding the
 * widget. It opens the same-origin `/widget` page in a modal iframe and speaks
 * the same postMessage protocol as the real `widget.js` loader, so a developer
 * can try both modes with a live token straight from the settings link.
 */

const SAMPLE_PAYLOAD: Record<string, unknown> = {
  amount: 132000,
  due_date: '2026-09-30',
  gateway: 'stripe',
  vendor: {
    name: 'デモ仕入先株式会社',
    bank_code: '0001',
    branch_code: '001',
    account_type: '普通',
    account_number: '1234567',
    account_name: 'デモシイレサキ',
  },
}

export function WidgetDemoPage() {
  const { t } = useTranslation()
  const [params] = useSearchParams()
  const token = params.get('token') ?? ''

  const [mode, setMode] = useState<'quickpay' | 'manage' | null>(null)
  const frameRef = useRef<HTMLIFrameElement | null>(null)
  const payloadRef = useRef<Record<string, unknown> | null>(null)

  useEffect(() => {
    function onMessage(event: MessageEvent): void {
      const frame = frameRef.current
      if (frame === null || event.source !== frame.contentWindow) {
        return
      }

      const data = event.data as { type?: string }
      if (data.type === 'nenepayout:ready') {
        const win = frame.contentWindow
        if (payloadRef.current !== null && win !== null) {
          win.postMessage(
            { type: 'nenepayout:payload', payload: payloadRef.current },
            window.location.origin,
          )
        }
      } else if (
        data.type === 'nenepayout:success' ||
        data.type === 'nenepayout:failure' ||
        data.type === 'nenepayout:close'
      ) {
        setMode(null)
      }
    }

    window.addEventListener('message', onMessage)
    return () => {
      window.removeEventListener('message', onMessage)
    }
  }, [])

  function openQuickPay(): void {
    payloadRef.current = SAMPLE_PAYLOAD
    setMode('quickpay')
  }

  function openManage(): void {
    payloadRef.current = null
    setMode('manage')
  }

  if (token === '') {
    return (
      <div className="min-h-screen bg-surface p-inline-md">
        <p className="font-sans text-body text-danger">{t('admin.widget.demo.noToken')}</p>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-surface p-inline-md">
      <div className="mx-auto flex max-w-2xl flex-col gap-stack-md">
        <h1 className="font-sans text-heading font-medium text-primary">
          {t('admin.widget.demo.title')}
        </h1>
        <p className="font-sans text-body text-muted">{t('admin.widget.demo.description')}</p>

        <div className="flex flex-wrap gap-inline-sm">
          <Button
            onClick={() => {
              openQuickPay()
            }}
          >
            {t('admin.widget.demo.pay')}
          </Button>
          <Button
            variant="secondary"
            onClick={() => {
              openManage()
            }}
          >
            {t('admin.widget.demo.manage')}
          </Button>
        </div>
      </div>

      {mode !== null && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center"
          style={{ backgroundColor: 'rgba(15, 23, 42, 0.55)' }}
          role="dialog"
          aria-modal="true"
        >
          <div className="flex flex-col gap-stack-sm">
            <iframe
              ref={frameRef}
              title="NeNe Payout"
              src={`/widget?token=${encodeURIComponent(token)}&mode=${mode}`}
              style={{ width: '460px', height: '680px', maxHeight: '92vh', maxWidth: '100%' }}
              className="rounded-md border-0 bg-surface-raised shadow-sm"
            />
            <div className="text-center">
              <Button
                variant="secondary"
                onClick={() => {
                  setMode(null)
                }}
              >
                {t('common.dialog.close')}
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
