import type { Preview } from '@storybook/react-vite'
// Global theme: Tailwind v4 + design tokens (same entry the app uses).
import '../src/shared/ui/theme/index.css'

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
}

export default preview
