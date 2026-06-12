<div id="shared-modal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div id="modal-body" class="modal-body">
                <p>Modal body text goes here.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">{{ __('app.common.close') }}</button>
                <div id="modal-footer-buttons-container">

                </div>
            </div>
        </div>
    </div>
</div>

@push('component_js')
<script>
    const modal = $('#shared-modal');
    const defaultDialogClass = 'modal-dialog modal-dialog-centered';
    const defaultContentClass = 'modal-content';
    const defaultHeaderClass = 'modal-header';
    const defaultBodyClass = 'modal-body';
    const defaultFooterClass = 'modal-footer';

    modal.show = (title, body, buttons, options = {}) => {
        modal.find('.modal-title').text(title);
        modal.find('.modal-dialog').attr('class', `${defaultDialogClass}${options.dialogClass ? ' ' + options.dialogClass : ''}`);
        modal.find('.modal-content').attr('class', `${defaultContentClass}${options.contentClass ? ' ' + options.contentClass : ''}`);
        modal.find('.modal-header').attr('class', `${defaultHeaderClass}${options.headerClass ? ' ' + options.headerClass : ''}`);
        modal.find('#modal-body').attr('class', `${defaultBodyClass}${options.bodyClass ? ' ' + options.bodyClass : ''}`);
        modal.find('.modal-footer').attr('class', `${defaultFooterClass}${options.footerClass ? ' ' + options.footerClass : ''}`);

        modal.find('#modal-body').html(body || '');
        modal.find('#modal-footer-buttons-container').html(buttons || '');

        modal.modal('show');
    }

    modal.hide = () => {
        modal.modal('hide');
    }

    modal.on('hidden.bs.modal', () => {
        modal.find('.modal-dialog').attr('class', defaultDialogClass);
        modal.find('.modal-content').attr('class', defaultContentClass);
        modal.find('.modal-header').attr('class', defaultHeaderClass);
        modal.find('#modal-body').attr('class', defaultBodyClass);
        modal.find('.modal-footer').attr('class', defaultFooterClass);
        modal.find('#modal-footer-buttons-container').html('');
    });
</script>
@endpush
