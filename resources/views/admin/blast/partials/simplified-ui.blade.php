<style id="blast-simplified-ui">
    body .content-wrapper {
        background: var(--app-bg, #f8fafc) !important;
    }

    .blast-menu-page,
    .wa-page,
    .eb-wrap,
    .general-page,
    .recipient-page,
    .template-page {
        --blast-primary: var(--app-accent, #2563eb);
        --blast-primary-strong: var(--app-accent-strong, #1d4ed8);
        --blast-bg: var(--app-bg, #f8fafc);
        --blast-surface: var(--app-surface, #ffffff);
        --blast-soft: var(--app-surface-soft, #eff6ff);
        --blast-border: var(--app-border, #dbeafe);
        --blast-text: var(--app-text, #0f172a);
        --blast-muted: var(--app-text-muted, #64748b);
        --blast-danger: var(--danger-color, #dc2626);
        --blast-success: var(--success-color, #16a34a);
        background: var(--blast-bg) !important;
        color: var(--blast-text);
    }

    .blast-menu-card,
    .wa-card,
    .eb-card,
    .general-card,
    .recipient-card,
    .template-card {
        border: 1px solid var(--blast-border) !important;
        border-radius: 8px !important;
        box-shadow: 0 1px 8px rgba(15, 23, 42, .06) !important;
        background: var(--blast-surface) !important;
    }

    .wa-page-header,
    .eb-page-header {
        border-radius: 8px !important;
        box-shadow: none !important;
        background: var(--blast-primary-strong) !important;
        padding: 16px 18px !important;
        margin-bottom: 14px !important;
    }

    .wa-page-header::before,
    .wa-page-header::after,
    .eb-page-header::before,
    .eb-page-header::after {
        content: none !important;
    }

    .wa-header-icon,
    .eb-app-icon,
    .stat-icon-box,
    .eb-stat-icon {
        display: none !important;
    }

    .wa-header-title,
    .eb-header-title {
        font-size: 20px !important;
        letter-spacing: 0 !important;
    }

    .wa-header-sub,
    .eb-header-sub {
        color: rgba(255, 255, 255, .78) !important;
    }

    .wa-stats-grid,
    .eb-stats-grid {
        gap: 10px !important;
        margin-bottom: 14px !important;
    }

    .wa-stat-card,
    .eb-stat-card {
        border-radius: 8px !important;
        padding: 14px 16px !important;
        box-shadow: none !important;
    }

    .wa-stat-card:hover,
    .eb-stat-card:hover,
    .blast-btn:hover {
        transform: none !important;
        box-shadow: 0 1px 8px rgba(15, 23, 42, .08) !important;
    }

    .stat-value,
    .eb-stat-value {
        font-size: 24px !important;
        letter-spacing: 0 !important;
    }

    .wa-top-row,
    .eb-top-row {
        grid-template-columns: minmax(300px, 360px) minmax(0, 1fr) !important;
        gap: 12px !important;
    }

    .wa-recipient-card,
    .wa-message-card,
    .wa-activity-card,
    .eb-recipient-card,
    .eb-message-card,
    .eb-activity-card {
        padding: 16px !important;
    }

    .wa-tips,
    .eb-tips,
    .blast-advanced-hidden {
        display: none !important;
    }

    .blast-menu-card {
        max-width: 760px !important;
        text-align: left !important;
        padding: 24px !important;
    }

    .blast-menu-title {
        background: none !important;
        -webkit-text-fill-color: initial !important;
        color: var(--blast-text) !important;
        font-size: 24px !important;
    }

    .blast-menu-actions {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 12px !important;
    }

    .blast-btn {
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .activity-table {
        border-radius: 8px !important;
        border-color: var(--blast-border) !important;
        overflow: auto !important;
    }

    .activity-retry-note {
        margin-right: auto;
        color: var(--blast-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .activity-table-header {
        background: #111827 !important;
        color: #fff !important;
        border-radius: 0 !important;
    }

    .activity-row {
        border-bottom: 1px solid var(--blast-border) !important;
    }

    .activity-action-btn.retry {
        background: var(--blast-primary) !important;
        border-color: var(--blast-primary) !important;
        color: #fff !important;
        padding: 6px 11px !important;
        font-weight: 800 !important;
    }

    .activity-action-btn.delete {
        background: #fff !important;
        color: var(--blast-danger) !important;
        border-color: #fecaca !important;
    }

    .status-badge {
        border-radius: 999px !important;
    }

    .message-override-item,
    .recipient-db-item,
    .recipient-item,
    .email-sender-panel,
    .recipient-db-section,
    .wa-active-device,
    .wa-qr-box {
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .wa-add-btn,
    .wa-send-btn,
    .eb-send-btn,
    .campaign-btn.info,
    .campaign-btn.success,
    .campaign-btn.warning,
    .campaign-btn.danger {
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    @media (max-width: 1100px) {
        .wa-top-row,
        .eb-top-row,
        .blast-menu-actions {
            grid-template-columns: 1fr !important;
        }
    }
</style>
