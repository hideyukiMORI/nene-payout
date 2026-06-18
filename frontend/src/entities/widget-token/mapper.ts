import type { WidgetTokenResponseDto } from './api-types'
import type { WidgetToken } from './model'

export function mapWidgetTokenDtoToModel(dto: WidgetTokenResponseDto): WidgetToken {
  return {
    token: dto.token,
    expiresAt: dto.expires_at,
    embedSnippet: dto.embed_snippet,
  }
}
