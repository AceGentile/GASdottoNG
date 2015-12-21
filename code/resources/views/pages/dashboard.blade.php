@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Ordini Aperti</h2>
			</div>
			<div class="panel-body">
				@if(count($opened) == 0)
				<div class="alert alert-info" role="alert">
					Non ci sono ordini aperti.
				</div>
				@else
				<ul class="list-group">
					@foreach($opened as $open)
					<li class="list-group-item">
						{!! $open->printableHeader() !!}
					</li>
					@endforeach
				</ul>
				@endif
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Ordini in Consegna</h2>
			</div>
			<div class="panel-body">
				@if(count($shipping) == 0)
				<div class="alert alert-info" role="alert">
					Non ci sono ordini in consegna.
				</div>
				@else
				<ul class="list-group">
					@foreach($shipping as $ship)
					<li class="list-group-item">
						{{ $ship->printableHeader() }}
					</li>
					@endforeach
				</ul>
				@endif
			</div>
		</div>
	</div>
</div>

@endsection
