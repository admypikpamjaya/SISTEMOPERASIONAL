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
