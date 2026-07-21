import { describe, it, expect, beforeEach } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import { I18nProvider } from './i18n-context'
import { useTranslation } from './use-translation'

function Probe() {
  const { t, setLocale } = useTranslation()

  return (
    <div>
      <h1>{t('admin.nav.dashboard')}</h1>
      <button
        type="button"
        onClick={() => {
          setLocale('en')
        }}
      >
        to-en
      </button>
      <button
        type="button"
        onClick={() => {
          setLocale('ja')
        }}
      >
        to-ja
      </button>
    </div>
  )
}

describe('I18nProvider language switching', () => {
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'ja')
  })

  it('renders the persisted locale and switches without reload', () => {
    render(
      <I18nProvider>
        <Probe />
      </I18nProvider>,
    )

    expect(screen.getByRole('heading', { level: 1 }).textContent).toBe('ダッシュボード')

    fireEvent.click(screen.getByText('to-en'))
    expect(screen.getByRole('heading', { level: 1 }).textContent).toBe('Dashboard')

    fireEvent.click(screen.getByText('to-ja'))
    expect(screen.getByRole('heading', { level: 1 }).textContent).toBe('ダッシュボード')
  })

  it('persists the chosen locale and reflects it on the document element', () => {
    render(
      <I18nProvider>
        <Probe />
      </I18nProvider>,
    )

    fireEvent.click(screen.getByText('to-en'))

    expect(localStorage.getItem('nene-payout-locale')).toBe('en')
    expect(document.documentElement.lang).toBe('en')
  })
})
