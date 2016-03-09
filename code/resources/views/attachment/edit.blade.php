<form class="form-horizontal main-form attachment-editor" method="PUT" action="{{ url('attachments/' . $attachment->id) }}">
	<div class="row">
		<div class="col-md-12">
			@include('commons.textfield', ['obj' => $attachment, 'name' => 'name', 'label' => 'Nome'])
			@include('commons.filefield', ['obj' => $attachment, 'name' => 'update', 'label' => 'Sostituisci File'])

			<div class="form-group">
				<label for="download" class="col-sm-{{ $labelsize }} control-label">Scarica</label>

				<div class="col-sm-{{ $fieldsize }}">
					<a class="btn btn-default" href="{{ url('attachments/download/' . $attachment->id) }}">Clicca Qui</a>
				</div>
			</div>

		</div>
	</div>

	@include('commons.formbuttons')
</form>
