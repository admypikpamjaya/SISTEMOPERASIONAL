<style>
    .finance-bulk-category-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px;
        margin-bottom: 1rem;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 14px;
        background: rgba(248, 250, 252, .64);
    }
    .pl-section-card .finance-bulk-category-form,
    .fs-section-card .finance-bulk-category-form {
        margin-bottom: 0;
        border-width: 1px 0 0;
        border-radius: 0;
    }
    .finance-bulk-category-form .bulk-field {
        min-width: min(100%, 260px);
    }
    .finance-bulk-category-form .bulk-help {
        color: var(--fs-muted, var(--pl-muted, var(--gl-muted, #64748b)));
        font-size: .78rem;
        font-weight: 600;
        line-height: 1.45;
    }
    .finance-bulk-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #2563eb;
    }
    body.dark-mode .finance-bulk-category-form {
        background: rgba(15, 23, 42, .45);
        border-color: rgba(148, 163, 184, .18);
    }
</style>

<script>
    (function () {
        const text = {
            noRows: @json(__('app.finance.bulk_category_select_rows_first')),
            noCategory: @json(__('app.finance.bulk_category_select_category_first')),
        };

        function resolveCheckboxes(selector) {
            return selector
                ? Array.from(document.querySelectorAll(selector))
                : [];
        }

        function updateMasterState(selector) {
            const checkboxes = resolveCheckboxes(selector);
            const masters = Array.from(document.querySelectorAll(`.js-bulk-check-all[data-checkbox-selector="${selector}"]`));
            const checkedCount = checkboxes.filter(checkbox => checkbox.checked).length;

            masters.forEach(master => {
                master.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
                master.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            });
        }

        document.querySelectorAll('.js-bulk-check-all').forEach(master => {
            const selector = master.getAttribute('data-checkbox-selector');
            master.addEventListener('change', function () {
                resolveCheckboxes(selector).forEach(checkbox => {
                    checkbox.checked = master.checked;
                });
                updateMasterState(selector);
            });
            updateMasterState(selector);
        });

        document.querySelectorAll('.js-bulk-row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateMasterState(checkbox.getAttribute('data-checkbox-group'));
            });
        });

        document.querySelectorAll('.js-bulk-category-form').forEach(form => {
            const selector = form.getAttribute('data-checkbox-selector');
            form.addEventListener('submit', function (event) {
                const checkedRows = resolveCheckboxes(selector).filter(checkbox => checkbox.checked);
                const category = form.querySelector('[name="category_id"]');

                if (checkedRows.length === 0) {
                    event.preventDefault();
                    alert(text.noRows);
                    return;
                }

                if (!category || !category.value) {
                    event.preventDefault();
                    alert(text.noCategory);
                }
            });
        });
    })();
</script>
