<form class="form-horizontal creating-form movement-modal" method="POST" action="{{ route('movements.store') }}" data-toggle="validator">
    <input type="hidden" name="update-list" value="movement-list">
    <input type="hidden" name="post-saved-function[]" value="refreshFilter">
    <input type="hidden" name="post-saved-function[]" value="refreshBalanceView">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">{{ _i('Crea Nuovo Movimento') }}</h4>
    </div>
    <div class="modal-body">
        @include('movement.base-edit', ['movement' => null])
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
        <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
    </div>
</form>
