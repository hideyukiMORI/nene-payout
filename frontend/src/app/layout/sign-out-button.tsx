import { useNavigate } from 'react-router-dom'
import { authToken } from '@/shared/api/auth-token'
import { Button } from '@/shared/ui'
import { useTranslation } from '@/shared/i18n'

/**
 * Clears the local session token and returns to login. Lives in the app layer
 * because the token store is in shared/api, which features may not import
 * (FSD import boundaries). The API remains the source of truth for auth.
 */
export function SignOutButton() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  return (
    <Button
      variant="secondary"
      onClick={() => {
        authToken.clear()
        void navigate('/login', { replace: true })
      }}
    >
      {t('common.actions.signOut')}
    </Button>
  )
}
