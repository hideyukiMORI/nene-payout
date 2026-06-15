import type { Meta, StoryObj } from '@storybook/react-vite'
import { ErrorState } from './ErrorState'

const meta = {
  title: 'Components/ErrorState',
  component: ErrorState,
  args: {
    message: 'Could not load the data.',
    retryLabel: 'Retry',
    onRetry: () => undefined,
  },
} satisfies Meta<typeof ErrorState>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
