import type { MessageCatalog, MessageKey } from './ja'

/**
 * English message catalog — the reference locale and runtime fallback.
 *
 * Authority note (規約 04 I18N-8): the message key set is owned by `ja.ts`
 * (`MessageKey = keyof typeof ja`). This catalog is checked against
 * `Record<MessageKey, string>` (規約 04 I18N-9), so it must mirror `ja`
 * exactly — a key added to or removed from `ja` becomes a compile error here.
 *
 * Key naming: `common.*` | `auth.*` | `admin.{feature}.{element}` | `widget.*`.
 * Param interpolation: `{{paramName}}`.
 *
 * Every locale falls back to these English values at runtime (see `translate`).
 *
 * Only static UI text lives here. Data fetched from the API (vendor names,
 * amounts, etc.) is never translated.
 */

export const en = {
  // ── App shell ─────────────────────────────────────────────────────────────
  'app.name': 'NeNe Payout',
  'app.nav.label': 'Primary',
  'app.locale.label': 'Language',

  // ── Common: actions ───────────────────────────────────────────────────────
  'common.actions.create': 'Create',
  'common.actions.save': 'Save changes',
  'common.actions.saving': 'Saving…',
  'common.actions.cancel': 'Cancel',
  'common.actions.edit': 'Edit',
  'common.actions.deactivate': 'Deactivate',
  'common.actions.void': 'Void',
  'common.actions.retry': 'Retry',
  'common.actions.confirm': 'Confirm',
  'common.actions.search': 'Search',
  'common.actions.upload': 'Upload',
  'common.actions.back': 'Back',
  'common.actions.next': 'Next',
  'common.actions.previous': 'Previous',
  'common.actions.signIn': 'Sign in',
  'common.actions.signOut': 'Sign out',

  // ── Common: fields ────────────────────────────────────────────────────────
  'common.field.name': 'Name',
  'common.field.email': 'Email',
  'common.field.password': 'Password',
  'common.field.amount': 'Amount',
  'common.field.dueDate': 'Due date',
  'common.field.status': 'Status',
  'common.field.createdAt': 'Created',
  'common.field.updatedAt': 'Updated',
  'common.field.actions': 'Actions',

  // ── Common: errors (mapped from Problem Details status) ───────────────────
  'common.error.unknown': 'An unexpected error occurred.',
  'common.error.unauthorized': 'Authentication required. Please sign in.',
  'common.error.forbidden': 'You do not have permission to perform this action.',
  'common.error.notFound': 'The requested resource was not found.',
  'common.error.conflict': 'A conflict occurred. The resource may already exist or be in use.',
  'common.error.validation': 'The submitted data is invalid. Please check the form.',
  'common.error.payloadTooLarge': 'The uploaded file is too large.',
  'common.error.serverError': 'A server error occurred. Please try again later.',

  // ── Common: dialog / states ───────────────────────────────────────────────
  'common.dialog.close': 'Close',
  'common.state.loading': 'Loading…',
  'common.state.empty': 'No items to display.',
  'common.state.error': 'Could not load data.',

  // ── Common: pagination ────────────────────────────────────────────────────
  'common.pagination.summary': '{{from}}–{{to}} of {{total}}',
  'common.pagination.page': 'Page {{page}}',

  // ── Common: roles ─────────────────────────────────────────────────────────
  'common.role.superadmin': 'Superadmin',
  'common.role.admin': 'Admin',
  'common.role.operator': 'Operator',

  // ── Auth ──────────────────────────────────────────────────────────────────
  'auth.login.title': 'Sign in to NeNe Payout',
  'auth.login.emailLabel': 'Email address',
  'auth.login.passwordLabel': 'Password',
  'auth.login.submit': 'Sign in',
  'auth.login.failed': 'Invalid email or password.',
  'auth.login.error.emailRequired': 'Enter your email address.',
  'auth.login.error.emailInvalid': 'Enter a valid email address.',
  'auth.login.error.passwordRequired': 'Enter your password.',

  // ── Admin navigation ──────────────────────────────────────────────────────
  'admin.nav.dashboard': 'Dashboard',
  'admin.nav.receivedInvoices': 'Received invoices',
  'admin.nav.vendors': 'Vendors',
  'admin.nav.payments': 'Payments',
  'admin.nav.settings': 'Settings',
  'admin.nav.users': 'Users',
  'admin.nav.organizations': 'Organizations',
  'admin.nav.auditLogs': 'Audit logs',

  // ── Admin: dashboard ──────────────────────────────────────────────────────
  'admin.dashboard.pageTitle': 'Dashboard',
  'admin.dashboard.pendingInvoices': 'Pending invoices',
  'admin.dashboard.recentPayments': 'Recent payments',
  'admin.dashboard.totalInvoices': 'Received invoices',
  'admin.dashboard.vendors': 'Vendors',
  'admin.dashboard.payments': 'Payments',
  'admin.dashboard.view': 'View list',

  // ── Admin: received invoices ──────────────────────────────────────────────
  'admin.receivedInvoices.pageTitle': 'Received invoices',
  'admin.receivedInvoices.newTitle': 'Register received invoice',
  'admin.receivedInvoices.editTitle': 'Edit received invoice',
  'admin.receivedInvoices.detailTitle': 'Invoice detail',
  'admin.receivedInvoices.empty': 'No received invoices yet.',
  'admin.receivedInvoices.actions.new': 'New invoice',
  'admin.receivedInvoices.field.vendor': 'Vendor',
  'admin.receivedInvoices.field.vaultDocumentUrl': 'Document URL',
  'admin.receivedInvoices.field.taxRate': 'Tax rate',
  'admin.receivedInvoices.field.taxableAmount': 'Taxable amount',
  'admin.receivedInvoices.field.taxAmount': 'Tax amount',
  'admin.receivedInvoices.field.registrationNumber': 'Registration number',
  'admin.receivedInvoices.taxBreakdown.title': 'Tax breakdown',
  'admin.receivedInvoices.taxBreakdown.add': 'Add tax line',
  'admin.receivedInvoices.taxBreakdown.remove': 'Remove',
  'admin.receivedInvoices.taxBreakdown.rate10': '10%',
  'admin.receivedInvoices.taxBreakdown.rate8': '8% (reduced)',
  'admin.receivedInvoices.form.error.vendorRequired': 'Vendor is required.',
  'admin.receivedInvoices.form.error.amount': 'Amount must be a positive integer.',
  'admin.receivedInvoices.form.error.dueDate': 'Due date must be a valid date.',
  'admin.receivedInvoices.form.error.registrationNumber':
    'Registration number must match T followed by 13 digits.',
  'admin.receivedInvoices.form.error.taxAmount': 'Amounts must be non-negative integers.',
  'admin.receivedInvoices.form.saveFailed':
    'Could not save the invoice. Please check the form and try again.',
  'admin.receivedInvoices.status.pending': 'Pending',
  'admin.receivedInvoices.status.processing': 'Processing',
  'admin.receivedInvoices.status.paid': 'Paid',
  'admin.receivedInvoices.status.failed': 'Failed',
  'admin.receivedInvoices.status.voided': 'Voided',
  'admin.receivedInvoices.filter.status': 'Filter by status',
  'admin.receivedInvoices.uploadPdf': 'Upload PDF',
  'admin.receivedInvoices.pdf.selectFile': 'PDF file',
  'admin.receivedInvoices.pdf.success': 'PDF uploaded.',
  'admin.receivedInvoices.pdf.failed': 'Could not upload the PDF. Please try again.',
  'admin.receivedInvoices.pdf.error.required': 'Please choose a PDF file.',
  'admin.receivedInvoices.pdf.error.type': 'The file must be a PDF.',
  'admin.receivedInvoices.pdf.error.tooLarge': 'The file is too large.',
  'admin.receivedInvoices.void.confirmTitle': 'Void this invoice?',
  'admin.receivedInvoices.void.confirmBody': 'The invoice will be voided. This cannot be undone.',
  'admin.receivedInvoices.paymentHistory': 'Payment history',

  // ── Admin: vendors ────────────────────────────────────────────────────────
  'admin.vendors.pageTitle': 'Vendors',
  'admin.vendors.newTitle': 'Register vendor',
  'admin.vendors.editTitle': 'Edit vendor',
  'admin.vendors.detailTitle': 'Vendor detail',
  'admin.vendors.empty': 'No vendors yet.',
  'admin.vendors.actions.new': 'New vendor',
  'admin.vendors.field.name': 'Name',
  'admin.vendors.field.bankCode': 'Bank code',
  'admin.vendors.field.branchCode': 'Branch code',
  'admin.vendors.field.accountType': 'Account type',
  'admin.vendors.field.accountNumber': 'Account number',
  'admin.vendors.field.accountName': 'Account holder (kana)',
  'admin.vendors.field.registrationNumber': 'Registration number',
  'admin.vendors.accountType.ordinary': 'Ordinary',
  'admin.vendors.accountType.checking': 'Checking',
  'admin.vendors.form.error.nameRequired': 'Name is required.',
  'admin.vendors.form.error.bankCode': 'Bank code must be 4 digits.',
  'admin.vendors.form.error.branchCode': 'Branch code must be 3 digits.',
  'admin.vendors.form.error.accountNumber': 'Account number must be up to 7 digits.',
  'admin.vendors.form.error.accountNameRequired': 'Account holder is required.',
  'admin.vendors.form.error.registrationNumber':
    'Registration number must match T followed by 13 digits.',
  'admin.vendors.form.saveFailed':
    'Could not save the vendor. Please check the form and try again.',
  'admin.vendors.deactivate.confirmTitle': 'Deactivate vendor "{{name}}"?',
  'admin.vendors.deactivate.confirmBody':
    'The vendor will be deactivated and hidden from new payments.',

  // ── Admin: payments ───────────────────────────────────────────────────────
  'admin.payments.pageTitle': 'Payment history',
  'admin.payments.detailTitle': 'Payment detail',
  'admin.payments.empty': 'No payments yet.',
  'admin.payments.field.gateway': 'Gateway',
  'admin.payments.field.gatewayReference': 'Gateway reference',
  'admin.payments.field.chargeAmount': 'Charged amount',
  'admin.payments.field.processingFee': 'Processing fee',
  'admin.payments.field.initiatedAt': 'Initiated at',
  'admin.payments.field.completedAt': 'Completed at',
  'admin.payments.field.receivedInvoice': 'Received invoice',
  'admin.payments.status.initiated': 'Initiated',
  'admin.payments.status.succeeded': 'Succeeded',
  'admin.payments.status.failed': 'Failed',
  'admin.payments.status.refunded': 'Refunded',
  'admin.payments.status.chargedBack': 'Charged back',
  'admin.payments.initiate': 'Pay by card',
  'admin.payments.amountDue': 'Amount due: {{amount}}',
  'admin.payments.gateway.stripe': 'Stripe',
  'admin.payments.gateway.gmoPg': 'GMO Payment Gateway',
  'admin.payments.pay.title': 'Pay by card',
  'admin.payments.pay.notPayable': 'This invoice is not payable in its current status.',
  'admin.payments.pay.noRedirect': 'Payment initiated. Continue in the payment window.',
  'admin.payments.pay.failed': 'Could not start the payment. Please try again.',

  // ── Admin: gateway settings ───────────────────────────────────────────────
  'admin.gatewaySettings.pageTitle': 'Payment gateway',
  'admin.gatewaySettings.activeGateway': 'Active gateway',
  'admin.gatewaySettings.credentials': 'Credentials',
  'admin.gatewaySettings.verify': 'Test connection',
  'admin.gatewaySettings.verifySuccess': 'Connection succeeded.',
  'admin.gatewaySettings.verifyFailure': 'Connection failed.',

  // ── Admin: organization ───────────────────────────────────────────────────
  'admin.organization.pageTitle': 'Organization settings',
  'admin.organization.field.name': 'Organization name',
  'admin.organization.field.slug': 'Slug',
  'admin.organization.field.customDomain': 'Custom domain',
  'admin.organization.form.error.nameRequired': 'Organization name is required.',
  'admin.organization.form.error.nameTooLong': 'Organization name must be at most 255 characters.',
  'admin.organization.form.saveFailed': 'Could not save changes. Please try again later.',
  'admin.organization.form.saved': 'Organization settings saved.',

  // ── Admin: organizations (superadmin, cross-tenant) ───────────────────────
  'admin.organizations.pageTitle': 'Organizations',
  'admin.organizations.newTitle': 'Create organization',
  'admin.organizations.editTitle': 'Edit organization',
  'admin.organizations.detailTitle': 'Organization details',
  'admin.organizations.empty': 'No organizations yet.',
  'admin.organizations.actions.new': 'New organization',
  'admin.organizations.field.slug': 'Slug',
  'admin.organizations.field.name': 'Name',
  'admin.organizations.field.customDomain': 'Custom domain',
  'admin.organizations.field.status': 'Status',
  'admin.organizations.status.active': 'Active',
  'admin.organizations.status.inactive': 'Inactive',
  'admin.organizations.form.error.slugRequired': 'Slug is required.',
  'admin.organizations.form.error.slug':
    'Slug must be lowercase letters, digits, and hyphens (max 100).',
  'admin.organizations.form.error.nameRequired': 'Name is required.',
  'admin.organizations.form.error.nameTooLong': 'Name must be at most 255 characters.',
  'admin.organizations.form.error.customDomain': 'Enter a valid hostname.',
  'admin.organizations.form.conflict': 'That slug or custom domain is already in use.',
  'admin.organizations.form.saveFailed': 'Could not save the organization. Please try again.',
  'admin.organizations.deactivate.confirm':
    'Deactivate this organization? Its users will no longer be able to sign in.',
  'admin.organizations.deactivate.failed':
    'Could not deactivate the organization. Please try again.',

  // ── Admin: users ──────────────────────────────────────────────────────────
  'admin.users.pageTitle': 'Users',
  'admin.users.newTitle': 'Invite user',
  'admin.users.editTitle': 'Change role',
  'admin.users.detailTitle': 'User details',
  'admin.users.empty': 'No users yet.',
  'admin.users.actions.invite': 'Invite user',
  'admin.users.field.email': 'Email',
  'admin.users.field.role': 'Role',
  'admin.users.field.status': 'Status',
  'admin.users.status.active': 'Active',
  'admin.users.status.invited': 'Invited',
  'admin.users.status.deactivated': 'Deactivated',
  'admin.users.form.error.emailRequired': 'Email is required.',
  'admin.users.form.error.emailInvalid': 'Enter a valid email address.',
  'admin.users.form.inviteFailed': 'Could not send the invite. Please try again later.',
  'admin.users.form.saveFailed': 'Could not save changes. Please try again later.',
  'admin.users.deactivate.confirm': 'Deactivate this user? They will no longer be able to sign in.',
  'admin.users.deactivate.failed': 'Could not deactivate the user. Please try again later.',

  // ── Admin: audit logs ─────────────────────────────────────────────────────
  'admin.auditLogs.pageTitle': 'Audit logs',
  'admin.auditLogs.empty': 'No audit entries.',
  'admin.auditLogs.field.actor': 'Actor',
  'admin.auditLogs.field.action': 'Action',
  'admin.auditLogs.field.entity': 'Entity',
  'admin.auditLogs.field.createdAt': 'Timestamp',

  // ── Admin: embeddable widget (settings) ───────────────────────────────────
  'admin.widget.title': 'Embeddable widget',
  'admin.widget.description':
    'Paste this snippet into your own system to accept card payments. Keep the token private — anyone with it can manage this organization’s invoices.',
  'admin.widget.generate': 'Generate embed code',
  'admin.widget.copy': 'Copy',
  'admin.widget.copied': 'Copied',
  'admin.widget.generateFailed': 'Could not generate the embed code. Please try again.',

  // ── Widget ────────────────────────────────────────────────────────────────
  'widget.pay.title': 'Pay invoice',
  'widget.pay.amount': 'Amount: {{amount}}',
  'widget.pay.submit': 'Pay',
  'widget.pay.processing': 'Processing payment…',
  'widget.pay.payee': 'Payee',
  'widget.pay.account': 'Payee account',
  'widget.pay.redirecting': 'Redirecting to the payment page…',
  'widget.complete.success': 'Payment complete.',
  'widget.complete.failure': 'Payment failed.',
  'widget.manage.title': 'Pay invoices',
  'widget.manage.empty': 'No invoices to pay.',
} satisfies Record<MessageKey, string>

// `MessageCatalog`/`MessageKey` authority now lives in `ja.ts` (規約 04 I18N-8).
// Re-exported here so existing `./en` type imports keep resolving unchanged.
export type { MessageCatalog, MessageKey }
