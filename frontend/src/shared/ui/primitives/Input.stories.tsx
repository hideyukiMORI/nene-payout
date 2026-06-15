import type { Meta, StoryObj } from '@storybook/react-vite'
import { Input } from './Input'

const meta = {
  title: 'Primitives/Input',
  component: Input,
  args: { placeholder: 'Enter a value', defaultValue: '' },
} satisfies Meta<typeof Input>

export default meta
type Story = StoryObj<typeof meta>

export const Empty: Story = {}
export const Filled: Story = { args: { defaultValue: 'admin@payout.test' } }
export const Disabled: Story = { args: { defaultValue: 'admin@payout.test', disabled: true } }
