import { useContext } from 'react'
import { I18nContext, type I18nContextValue } from './i18n-context-ref'

/**
 * Primary i18n hook. All user-facing strings come from `t(key)` — never hardcode
 * ja/en text in components (i18n.md).
 *
 * @example
 * const { t, locale, setLocale } = useTranslation()
 * return <h1>{t('admin.receivedInvoices.pageTitle')}</h1>
 */
export function useTranslation(): I18nContextValue {
  const ctx = useContext(I18nContext)

  if (ctx === null) {
    throw new Error('useTranslation must be called inside <I18nProvider>')
  }

  return ctx
}
