import type { MessageCatalog } from './en'

/**
 * Japanese message catalog. Must define every key in `en.ts` (enforced by the
 * parity test in `locales.test.ts`) so language switching never shows a gap.
 */
export const ja: Partial<MessageCatalog> = {
  // ── App shell ─────────────────────────────────────────────────────────────
  'app.name': 'NeNe Payout',
  'app.nav.label': 'メイン',
  'app.locale.label': '言語',

  // ── Common: actions ───────────────────────────────────────────────────────
  'common.actions.create': '作成',
  'common.actions.save': '変更を保存',
  'common.actions.saving': '保存中…',
  'common.actions.cancel': 'キャンセル',
  'common.actions.edit': '編集',
  'common.actions.deactivate': '無効化',
  'common.actions.void': '無効化',
  'common.actions.retry': '再試行',
  'common.actions.confirm': '確定',
  'common.actions.search': '検索',
  'common.actions.upload': 'アップロード',
  'common.actions.back': '戻る',
  'common.actions.next': '次へ',
  'common.actions.previous': '前へ',
  'common.actions.signIn': 'ログイン',
  'common.actions.signOut': 'ログアウト',

  // ── Common: fields ────────────────────────────────────────────────────────
  'common.field.name': '名称',
  'common.field.email': 'メールアドレス',
  'common.field.password': 'パスワード',
  'common.field.amount': '金額',
  'common.field.dueDate': '支払期限',
  'common.field.status': 'ステータス',
  'common.field.createdAt': '作成日時',
  'common.field.updatedAt': '更新日時',
  'common.field.actions': '操作',

  // ── Common: errors ────────────────────────────────────────────────────────
  'common.error.unknown': '予期しないエラーが発生しました。',
  'common.error.unauthorized': '認証が必要です。ログインしてください。',
  'common.error.forbidden': 'この操作を行う権限がありません。',
  'common.error.notFound': '要求されたリソースが見つかりませんでした。',
  'common.error.conflict': '競合が発生しました。既に存在するか使用中の可能性があります。',
  'common.error.validation': '入力内容が正しくありません。フォームを確認してください。',
  'common.error.payloadTooLarge': 'アップロードされたファイルが大きすぎます。',
  'common.error.serverError': 'サーバーエラーが発生しました。しばらくして再試行してください。',

  // ── Common: dialog / states ───────────────────────────────────────────────
  'common.dialog.close': '閉じる',
  'common.state.loading': '読み込み中…',
  'common.state.empty': '表示する項目がありません。',
  'common.state.error': 'データを読み込めませんでした。',

  // ── Common: pagination ────────────────────────────────────────────────────
  'common.pagination.summary': '{{total}}件中 {{from}}–{{to}}',
  'common.pagination.page': '{{page}} ページ',

  // ── Common: invoice status labels ─────────────────────────────────────────
  'common.invoiceStatus.pending': '未払い',
  'common.invoiceStatus.processing': '決済中',
  'common.invoiceStatus.paid': '支払い完了',
  'common.invoiceStatus.failed': '失敗',
  'common.invoiceStatus.voided': '無効',

  // ── Common: payment status labels ─────────────────────────────────────────
  'common.paymentStatus.initiated': '開始済み',
  'common.paymentStatus.succeeded': '成功',
  'common.paymentStatus.failed': '失敗',
  'common.paymentStatus.refunded': '返金済み',
  'common.paymentStatus.charged_back': 'チャージバック',

  // ── Common: account types ─────────────────────────────────────────────────
  'common.accountType.普通': '普通',
  'common.accountType.当座': '当座',

  // ── Common: roles ─────────────────────────────────────────────────────────
  'common.role.superadmin': 'スーパー管理者',
  'common.role.admin': '管理者',
  'common.role.operator': 'オペレーター',

  // ── Auth ──────────────────────────────────────────────────────────────────
  'auth.login.title': 'NeNe Payout にログイン',
  'auth.login.emailLabel': 'メールアドレス',
  'auth.login.passwordLabel': 'パスワード',
  'auth.login.submit': 'ログイン',
  'auth.login.failed': 'メールアドレスまたはパスワードが正しくありません。',
  'auth.login.error.emailRequired': 'メールアドレスを入力してください。',
  'auth.login.error.emailInvalid': 'メールアドレスの形式が正しくありません。',
  'auth.login.error.passwordRequired': 'パスワードを入力してください。',

  // ── Admin navigation ──────────────────────────────────────────────────────
  'admin.nav.dashboard': 'ダッシュボード',
  'admin.nav.receivedInvoices': '受取請求書',
  'admin.nav.vendors': '仕入先',
  'admin.nav.payments': '決済',
  'admin.nav.settings': '設定',
  'admin.nav.users': 'ユーザー',
  'admin.nav.auditLogs': '監査ログ',

  // ── Admin: dashboard ──────────────────────────────────────────────────────
  'admin.dashboard.pageTitle': 'ダッシュボード',
  'admin.dashboard.pendingInvoices': '未払いの請求書',
  'admin.dashboard.recentPayments': '最近の決済',

  // ── Admin: received invoices ──────────────────────────────────────────────
  'admin.receivedInvoices.pageTitle': '受取請求書',
  'admin.receivedInvoices.newTitle': '受取請求書を登録',
  'admin.receivedInvoices.editTitle': '受取請求書を編集',
  'admin.receivedInvoices.detailTitle': '請求書詳細',
  'admin.receivedInvoices.empty': '受取請求書はまだありません。',
  'admin.receivedInvoices.actions.new': '請求書を新規登録',
  'admin.receivedInvoices.field.vendor': '仕入先',
  'admin.receivedInvoices.field.vaultDocumentUrl': '書類URL',
  'admin.receivedInvoices.field.taxRate': '税率',
  'admin.receivedInvoices.field.taxableAmount': '課税対象額',
  'admin.receivedInvoices.field.taxAmount': '税額',
  'admin.receivedInvoices.field.registrationNumber': '登録番号',
  'admin.receivedInvoices.taxBreakdown.title': '税区分の内訳',
  'admin.receivedInvoices.taxBreakdown.add': '税区分の行を追加',
  'admin.receivedInvoices.taxBreakdown.remove': '削除',
  'admin.receivedInvoices.taxBreakdown.rate10': '10%',
  'admin.receivedInvoices.taxBreakdown.rate8': '8%（軽減税率）',
  'admin.receivedInvoices.form.error.vendorRequired': '仕入先は必須です。',
  'admin.receivedInvoices.form.error.amount': '金額は正の整数で入力してください。',
  'admin.receivedInvoices.form.error.dueDate': '支払期限は有効な日付で入力してください。',
  'admin.receivedInvoices.form.error.registrationNumber':
    '登録番号は T と13桁の数字で入力してください。',
  'admin.receivedInvoices.form.error.taxAmount': '金額は0以上の整数で入力してください。',
  'admin.receivedInvoices.form.saveFailed':
    '請求書を保存できませんでした。入力内容を確認して再試行してください。',
  'admin.receivedInvoices.status.pending': '支払前',
  'admin.receivedInvoices.status.processing': '処理中',
  'admin.receivedInvoices.status.paid': '支払済',
  'admin.receivedInvoices.status.failed': '失敗',
  'admin.receivedInvoices.status.voided': '無効',
  'admin.receivedInvoices.filter.status': 'ステータスで絞り込む',
  'admin.receivedInvoices.uploadPdf': 'PDF をアップロード',
  'admin.receivedInvoices.pdf.selectFile': 'PDF ファイル',
  'admin.receivedInvoices.pdf.success': 'PDF をアップロードしました。',
  'admin.receivedInvoices.pdf.failed':
    'PDF をアップロードできませんでした。もう一度お試しください。',
  'admin.receivedInvoices.pdf.error.required': 'PDF ファイルを選択してください。',
  'admin.receivedInvoices.pdf.error.type': 'ファイルは PDF 形式である必要があります。',
  'admin.receivedInvoices.pdf.error.tooLarge': 'ファイルサイズが大きすぎます。',
  'admin.receivedInvoices.void.confirmTitle': 'この請求書を無効化しますか？',
  'admin.receivedInvoices.void.confirmBody': '請求書が無効化されます。この操作は取り消せません。',
  'admin.receivedInvoices.paymentHistory': '決済履歴',

  // ── Admin: vendors ────────────────────────────────────────────────────────
  'admin.vendors.pageTitle': '仕入先',
  'admin.vendors.newTitle': '仕入先を登録',
  'admin.vendors.editTitle': '仕入先を編集',
  'admin.vendors.detailTitle': '仕入先詳細',
  'admin.vendors.empty': '仕入先はまだありません。',
  'admin.vendors.actions.new': '仕入先を新規登録',
  'admin.vendors.field.name': '名称',
  'admin.vendors.field.bankCode': '銀行コード',
  'admin.vendors.field.branchCode': '支店コード',
  'admin.vendors.field.accountType': '口座種別',
  'admin.vendors.field.accountNumber': '口座番号',
  'admin.vendors.field.accountName': '口座名義（カナ）',
  'admin.vendors.field.registrationNumber': '登録番号',
  'admin.vendors.accountType.ordinary': '普通',
  'admin.vendors.accountType.checking': '当座',
  'admin.vendors.form.error.nameRequired': '名称は必須です。',
  'admin.vendors.form.error.bankCode': '銀行コードは4桁で入力してください。',
  'admin.vendors.form.error.branchCode': '支店コードは3桁で入力してください。',
  'admin.vendors.form.error.accountNumber': '口座番号は7桁以内で入力してください。',
  'admin.vendors.form.error.accountNameRequired': '口座名義は必須です。',
  'admin.vendors.form.error.registrationNumber': '登録番号は T と13桁の数字で入力してください。',
  'admin.vendors.form.saveFailed':
    '仕入先を保存できませんでした。入力内容を確認して再試行してください。',
  'admin.vendors.deactivate.confirmTitle': '仕入先「{{name}}」を無効化しますか？',
  'admin.vendors.deactivate.confirmBody': '仕入先が無効化され、新規の決済対象から除外されます。',

  // ── Admin: payments ───────────────────────────────────────────────────────
  'admin.payments.pageTitle': '決済履歴',
  'admin.payments.detailTitle': '決済詳細',
  'admin.payments.empty': '決済はまだありません。',
  'admin.payments.field.gateway': 'ゲートウェイ',
  'admin.payments.field.gatewayReference': 'ゲートウェイ参照',
  'admin.payments.field.chargeAmount': '請求金額',
  'admin.payments.field.processingFee': '決済手数料',
  'admin.payments.field.initiatedAt': '開始日時',
  'admin.payments.field.completedAt': '完了日時',
  'admin.payments.field.receivedInvoice': '受取請求書',
  'admin.payments.status.initiated': '開始',
  'admin.payments.status.succeeded': '成功',
  'admin.payments.status.failed': '失敗',
  'admin.payments.status.refunded': '返金済',
  'admin.payments.status.chargedBack': 'チャージバック',
  'admin.payments.initiate': 'カードで支払う',
  'admin.payments.amountDue': '支払金額: {{amount}}',
  'admin.payments.gateway.stripe': 'Stripe',
  'admin.payments.gateway.gmoPg': 'GMO ペイメントゲートウェイ',
  'admin.payments.pay.title': 'カードで支払う',
  'admin.payments.pay.notPayable': 'この請求書は現在のステータスでは支払いできません。',
  'admin.payments.pay.noRedirect': '決済を開始しました。決済画面で続行してください。',
  'admin.payments.pay.failed': '決済を開始できませんでした。もう一度お試しください。',

  // ── Admin: gateway settings ───────────────────────────────────────────────
  'admin.gatewaySettings.pageTitle': '決済ゲートウェイ',
  'admin.gatewaySettings.activeGateway': '使用中のゲートウェイ',
  'admin.gatewaySettings.credentials': '認証情報',
  'admin.gatewaySettings.verify': '疎通確認',
  'admin.gatewaySettings.verifySuccess': '接続に成功しました。',
  'admin.gatewaySettings.verifyFailure': '接続に失敗しました。',

  // ── Admin: organization ───────────────────────────────────────────────────
  'admin.organization.pageTitle': '組織設定',
  'admin.organization.field.name': '組織名',
  'admin.organization.field.slug': 'スラッグ',

  // ── Admin: users ──────────────────────────────────────────────────────────
  'admin.users.pageTitle': 'ユーザー',
  'admin.users.newTitle': 'ユーザーを招待',
  'admin.users.empty': 'ユーザーはまだいません。',
  'admin.users.field.role': '権限',

  // ── Admin: audit logs ─────────────────────────────────────────────────────
  'admin.auditLogs.pageTitle': '監査ログ',
  'admin.auditLogs.empty': '監査ログはありません。',
  'admin.auditLogs.field.actor': '操作者',
  'admin.auditLogs.field.action': '操作',
  'admin.auditLogs.field.entity': '対象',

  // ── Widget ────────────────────────────────────────────────────────────────
  'widget.pay.title': '請求書の支払い',
  'widget.pay.amount': '金額: {{amount}}',
  'widget.pay.submit': '支払う',
  'widget.pay.processing': '決済処理中…',
  'widget.complete.success': '支払いが完了しました。',
  'widget.complete.failure': '支払いに失敗しました。',
}
