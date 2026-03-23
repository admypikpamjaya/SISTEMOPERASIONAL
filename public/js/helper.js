$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content')
    }
});

const Http = {
    get: function (url, data) {
        return $.ajax({
            type: 'GET',
            url,
            data
        })
    },
    post: function (url, data) {
        const isFormData = data instanceof FormData;

        return $.ajax({
            type: 'POST',
            url,
            data,
            processData: !isFormData,
            contentType: isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
        });
    },
    delete: function (url, data) {
        return $.ajax({
            type: 'DELETE',
            url,
            data
        })
    },

    postWithFile: function (url, data) {
        return $.ajax({
            type: 'POST',
            url,
            processData: false,
            contentType: false,
            data
        })
    }
}

const Notification = {
    success: function (message = "", html = "", title="Success!") {
        return Swal.fire({
            title,
            text: message,
            html,
            icon: 'success',
            width: '28em'
        })
    },
    error: function (error = '', timer = 60000, title= "Error!") {
        Swal.fire({
            title: 'Error!',
            text: typeof error === 'object' ? getErrorMessage(error) : error,
            icon: 'error',
            timer,
            width: '28em'
        })
    },
    warning: function (message = '', timer = 60000, title= "Warning!") {
        Swal.fire({
            title: title,
            text: typeof message === 'object' ? getErrorMessage(message) : message,
            icon: 'warning',
            timer,
            width: '28em'
        })
    },
    confirmation: async function(message = '', title = 'Confirmation!') {
        return Swal.fire({
            title: 'Confirmation!',
            text: typeof message === 'object' ? getErrorMessage(message) : message,
            icon: 'warning',
            showCancelButton: true,
            width: '28em'
        })
    }
}

const Loading = {
    show: () => $('#loading-overlay').css('display', 'flex'),
    hide: () => $('#loading-overlay').css('display', 'none')
}

const ThemeManager = (function () {
    const storageKey = 'soy-ypik-theme';
    let initialized = false;

    function normalizeTheme(value) {
        return value === 'dark' ? 'dark' : 'light';
    }

    function getStoredTheme() {
        try {
            return normalizeTheme(localStorage.getItem(storageKey));
        } catch (error) {
            return normalizeTheme(document.documentElement.dataset.theme);
        }
    }

    function getTheme() {
        if (document.body && document.body.dataset.theme) {
            return normalizeTheme(document.body.dataset.theme);
        }

        return normalizeTheme(document.documentElement.dataset.theme || getStoredTheme());
    }

    function updateControls(theme) {
        const label = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
        const iconClass = theme === 'dark' ? 'fa-moon' : 'fa-sun';

        document.querySelectorAll('[data-theme-label]').forEach((element) => {
            element.textContent = label;
        });

        document.querySelectorAll('[data-theme-icon]').forEach((element) => {
            element.classList.remove('fa-sun', 'fa-moon');
            element.classList.add(iconClass);
        });

        document.querySelectorAll('[data-theme-value]').forEach((element) => {
            const isActive = element.getAttribute('data-theme-value') === theme;
            element.classList.toggle('active', isActive);
            element.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function applyTheme(theme, options = {}) {
        const nextTheme = normalizeTheme(theme);
        const persist = Boolean(options.persist);
        const dispatchEvent = options.dispatch !== false;

        document.documentElement.dataset.theme = nextTheme;
        document.documentElement.style.colorScheme = nextTheme;

        if (document.body) {
            document.body.dataset.theme = nextTheme;
            document.body.classList.toggle('dark-mode', nextTheme === 'dark');
        }

        if (persist) {
            try {
                localStorage.setItem(storageKey, nextTheme);
            } catch (error) {
                // Ignore storage restriction and still apply theme in current tab.
            }
        }

        updateControls(nextTheme);

        if (dispatchEvent) {
            window.dispatchEvent(new CustomEvent('app:theme-change', {
                detail: {
                    theme: nextTheme,
                    isDark: nextTheme === 'dark'
                }
            }));
        }

        return nextTheme;
    }

    function bindControls() {
        document.querySelectorAll('[data-theme-value]').forEach((element) => {
            if (element.dataset.themeBound === '1') {
                return;
            }

            element.dataset.themeBound = '1';
            element.addEventListener('click', function (event) {
                event.preventDefault();
                applyTheme(element.getAttribute('data-theme-value'), { persist: true });
            });
        });
    }

    function init() {
        const theme = getStoredTheme();

        if (!initialized) {
            initialized = true;

            window.addEventListener('storage', function (event) {
                if (event.key !== storageKey) {
                    return;
                }

                applyTheme(normalizeTheme(event.newValue), { dispatch: true });
            });
        }

        bindControls();
        applyTheme(theme, { dispatch: false });

        return theme;
    }

    return {
        applyTheme,
        getTheme,
        init,
        isDark: function () {
            return getTheme() === 'dark';
        }
    };
})();

window.ThemeManager = ThemeManager;

const TableSelectionManager = (function () {
    let initialized = false;
    const enqueue = typeof window.queueMicrotask === 'function'
        ? window.queueMicrotask.bind(window)
        : function (callback) { window.setTimeout(callback, 0); };

    function getCheckboxRows() {
        return Array.from(document.querySelectorAll('tbody tr input[type="checkbox"]'));
    }

    function syncRow(checkbox) {
        const row = checkbox.closest('tr');

        if (!row || !row.closest('tbody')) {
            return;
        }

        row.classList.toggle('is-selected', checkbox.checked && !checkbox.disabled);
    }

    function syncAllRows() {
        getCheckboxRows().forEach(syncRow);
    }

    function queueSync() {
        enqueue(syncAllRows);
    }

    function handleCheckboxEvent(event) {
        const target = event.target;

        if (!(target instanceof HTMLInputElement) || target.type !== 'checkbox') {
            return;
        }

        if (!target.closest('tbody tr')) {
            return;
        }

        queueSync();
    }

    function init() {
        if (initialized) {
            syncAllRows();
            return;
        }

        initialized = true;

        document.addEventListener('click', handleCheckboxEvent, true);
        document.addEventListener('change', handleCheckboxEvent, true);
        window.addEventListener('pageshow', syncAllRows);
        window.addEventListener('load', syncAllRows);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', syncAllRows, { once: true });
            return;
        }

        syncAllRows();
    }

    return {
        init,
        sync: syncAllRows
    };
})();

window.TableSelectionManager = TableSelectionManager;
TableSelectionManager.init();

function getErrorMessage(res) {
    let errorMsg = res.responseJSON ? res.responseJSON.message : res

    if (res.status && res.status == 413) errorMsg = 'File upload terlalu besar.'

    if (res.responseJSON && res.responseJSON.errors) {
        let errorFields = Object.keys(res.responseJSON.errors)[0]
        errorMsg = res.responseJSON.errors[errorFields][0]
    }

    return errorMsg
}

function refreshUI(delay = 0) {
    setTimeout(() => location.reload(), delay);
}

function chunkArray(array, size) {
    const chunkedArray = [];
    if (size <= 0) {
        return [array];
    }

    for (let i = 0; i < array.length; i += size) {
        const chunk = array.slice(i, i + size);
        chunkedArray.push(chunk);
    }

    return chunkedArray;
}

function formatDateForInput(date) 
{
    return new Date(date).toISOString().split('T')[0];
}
