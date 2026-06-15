import type { Meta, StoryObj } from '@storybook/react-vite'
import { DetailList } from './DetailList'

const meta = {
  title: 'Components/DetailList',
  component: DetailList,
  args: {
    rows: [
      { label: 'Vendor', value: 'Acme Corp' },
      { label: 'Amount', value: '¥103,300' },
      { label: 'Status', value: 'Paid' },
    ],
  },
} satisfies Meta<typeof DetailList>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
