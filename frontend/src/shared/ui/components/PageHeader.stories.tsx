import type { Meta, StoryObj } from '@storybook/react-vite'
import { PageHeader } from './PageHeader'
import { Button } from '../primitives/Button'

const meta = {
  title: 'Components/PageHeader',
  component: PageHeader,
  args: { title: 'Received invoices' },
} satisfies Meta<typeof PageHeader>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const WithAction: Story = {
  args: { actions: <Button>New</Button> },
}
