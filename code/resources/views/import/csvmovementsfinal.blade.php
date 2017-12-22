<div class="wizard_page">
    <div class="modal-body">
        <p>
            Movimenti importati:
        </p>

        <ul class="list-group">
            @if(empty($movements))
                <li>Nessuno!</li>
            @else
                @foreach($movements as $m)
                    <li class="list-group-item">{!! $m->printableName() !!}</li>
                @endforeach
            @endif
        </ul>

        @if(!empty($errors))
            <hr/>

            <p>
                Errori:
            </p>

            <ul class="list-group">
                @foreach($errors as $e)
                    <li class="list-group-item">{!! $e !!}</li>
                @endforeach
            </ul>
        @endif

        <div class="clearfix"></div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default reloader" data-dismiss="modal">Chiudi</button>
    </div>
</div>
