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
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                <div id="modal-footer-buttons-container">

                </div>
            </div>
        </div>
    </div>
</div>

@push('component_js')
<script>
    const modal = $('#shared-modal');

    modal.show = (title, body, buttons) => {
        modal.find('.modal-title').text(title);

        modal.find('#modal-body').html(body || '');
        modal.find('#modal-footer-buttons-container').html(buttons || '');

        modal.modal('show');
    }

    modal.hide = () => {
        modal.modal('hide');
    }
</script>
@endpush