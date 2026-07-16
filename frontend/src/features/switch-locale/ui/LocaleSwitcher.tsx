import { LOCALES, resolveLocale, SUPPORTED_LOCALE_IDS, useTranslation } from '@/shared/i18n'

/**
 * Locale selector for the app shell. Bound to the i18n context, so switching is
 * instant and persisted (i18n.md). Only depends on shared/i18n.
 */
export function LocaleSwitcher() {
  const { t, locale, setLocale } = useTranslation()

  return (
    <select
      aria-label={t('app.locale.label')}
      value={locale}
      onChange={(event) => {
        setLocale(resolveLocale(event.target.value))
      }}
      className="rounded-x-md border border-border bg-surface-raised px-x-inline-sm py-x-stack-sm font-sans text-text-primary"
    >
      {SUPPORTED_LOCALE_IDS.map((id) => (
        <option key={id} value={id}>
          {LOCALES[id].label}
        </option>
      ))}
    </select>
  )
}
