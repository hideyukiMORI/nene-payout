import { useState } from 'react'
import { useGenerateWidgetToken } from '@/entities/widget-token'
import { useTranslation } from '@/shared/i18n'
import { Button, PageHeader, Text } from '@/shared/ui'

/**
 * Settings panel that issues an organization-scoped widget token and shows the
 * ready-to-paste `<script>` embed snippet (Mode A/B). Admin-only — the page is
 * already gated by ManageOrganizationSettings.
 */
export function GenerateWidgetEmbed() {
  const { t } = useTranslation()
  const generate = useGenerateWidgetToken()
  const [copied, setCopied] = useState(false)

  const snippet = generate.data?.embedSnippet ?? null

  async function copy(): Promise<void> {
    if (snippet === null) {
      return
    }
    await navigator.clipboard.writeText(snippet)
    setCopied(true)
    window.setTimeout(() => {
      setCopied(false)
    }, 2000)
  }

  return (
    <section className="px-x-inline-md">
      <PageHeader title={t('admin.widget.title')} />

      <div className="flex max-w-2xl flex-col gap-x-stack-md">
        <Text tone="muted">{t('admin.widget.description')}</Text>

        <div>
          <Button
            onClick={() => {
              generate.mutate()
            }}
            disabled={generate.isPending}
          >
            {generate.isPending ? t('common.actions.saving') : t('admin.widget.generate')}
          </Button>
        </div>

        {generate.isError && (
          <p className="font-sans text-danger">{t('admin.widget.generateFailed')}</p>
        )}

        {snippet !== null && (
          <div className="flex flex-col gap-x-stack-sm">
            <textarea
              readOnly
              aria-label={t('admin.widget.title')}
              value={snippet}
              rows={3}
              className="w-full rounded-x-md border border-border bg-surface-raised p-x-inline-sm font-sans text-text-primary"
            />
            <div>
              <Button
                variant="secondary"
                onClick={() => {
                  void copy()
                }}
              >
                {copied ? t('admin.widget.copied') : t('admin.widget.copy')}
              </Button>
            </div>
          </div>
        )}
      </div>
    </section>
  )
}
