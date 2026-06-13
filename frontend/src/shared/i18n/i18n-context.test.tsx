import { describe, it, expect, beforeEach } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import { I18nProvider } from './i18n-context'
import { useTranslation } from './use-translation'

function Probe() {
  const { t, setLocale } = useTranslation()

  return (
    <div>
      <span data-testid="title">{t('admin.nav.dashboard')}</span>
      <button type="button" onClick={() => setLocale('en')}>
        to-en
      </button>
      <button type="button" onClick={() => setLocale('ja')}>
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

    expect(screen.getByTestId('title').textContent).toBe('ダッシュボード')

    fireEvent.click(screen.getByText('to-en'))
    expect(screen.getByTestId('title').textContent).toBe('Dashboard')

    fireEvent.click(screen.getByText('to-ja'))
    expect(screen.getByTestId('title').textContent).toBe('ダッシュボード')
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
