import { GenerateWidgetEmbed } from '@/features/generate-widget-embed'
import { OrganizationSettingsForm } from '@/features/manage-organization-settings'

export function SettingsPage() {
  return (
    <div className="flex flex-col gap-stack-lg">
      <OrganizationSettingsForm />
      <GenerateWidgetEmbed />
    </div>
  )
}
