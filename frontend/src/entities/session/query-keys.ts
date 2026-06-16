export const sessionKeys = {
  all: ['session'] as const,
  currentUser: () => [...sessionKeys.all, 'me'] as const,
}
