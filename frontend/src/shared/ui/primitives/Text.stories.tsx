import type { Meta, StoryObj } from '@storybook/react-vite'
import { Text } from './Text'

const meta = {
  title: 'Primitives/Text',
  component: Text,
  args: { children: 'The quick brown fox jumps over the lazy dog.' },
} satisfies Meta<typeof Text>

export default meta
type Story = StoryObj<typeof meta>

export const Primary: Story = { args: { tone: 'primary' } }
export const Muted: Story = { args: { tone: 'muted' } }
export const Inline: Story = { args: { as: 'span', tone: 'primary' } }
