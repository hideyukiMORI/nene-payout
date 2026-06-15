import type { Meta, StoryObj } from '@storybook/react-vite'
import { Select } from './Select'

const meta = {
  title: 'Primitives/Select',
  component: Select,
  args: {
    children: (
      <>
        <option value="stripe">Stripe</option>
        <option value="gmo_pg">GMO PG</option>
      </>
    ),
  },
} satisfies Meta<typeof Select>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Disabled: Story = { args: { disabled: true } }
