import { describe, it, expect } from 'vitest'
import { formatDate, formatJpy } from './format'

describe('formatJpy', () => {
  it('formats an integer yen amount without minor units', () => {
    expect(formatJpy(1000, 'ja')).toBe('￥1,000')
  })

  it('formats zero', () => {
    expect(formatJpy(0, 'en')).toBe('¥0')
  })

  it('formats with the en locale currency symbol', () => {
    expect(formatJpy(1234567, 'en')).toBe('¥1,234,567')
  })
})

describe('formatDate', () => {
  it('formats an ISO calendar date in ja', () => {
    expect(formatDate('2026-06-14', 'ja')).toBe('2026年6月14日')
  })

  it('formats an ISO calendar date with a time component (date part only)', () => {
    expect(formatDate('2026-01-09T12:34:56Z', 'en')).toBe('Jan 9, 2026')
  })

  it('returns the raw value when the input is not a date', () => {
    expect(formatDate('not-a-date', 'ja')).toBe('not-a-date')
  })
})
