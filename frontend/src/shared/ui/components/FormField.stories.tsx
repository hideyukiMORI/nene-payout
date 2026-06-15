import type { Meta, StoryObj } from '@storybook/react-vite'
import { FormField } from './FormField'
import { Input } from '../primitives/Input'

const meta = {
  title: 'Components/FormField',
  component: FormField,
  args: {
    id: 'email',
    label: 'Email address',
    children: <Input id="email" type="email" placeholder="admin@payout.test" />,
  },
} satisfies Meta<typeof FormField>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const WithError: Story = {
  args: { error: 'Enter a valid email address.' },
}
