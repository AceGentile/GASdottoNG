@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userHas('supplier.orders'))
			@include('commons.addingbutton', [
				'template' => 'order.base-edit',
				'typename' => 'order',
				'typename_readable' => 'Ordine',
				'targeturl' => 'orders',
				'extra' => [
					'post-saved-refetch' => '#aggregable-list'
				]
			])

			<button type="button" class="btn btn-default" data-toggle="modal" data-target="#orderAggregator">Aggrega Ordini</button>

			<div class="modal fade" id="orderAggregator" tabindex="-1" role="dialog" aria-labelledby="orderAggregator">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<form class="form-horizontal" method="POST" action="{{ url('aggregates') }}" data-toggle="validator">
							<input type="hidden" name="update-select" value="category_id">

							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">Aggrega Ordini</h4>
							</div>
							<div class="modal-body">
								<p>
									Clicca e trascina gli ordini nella stessa cella per aggregarli.
								</p>
								<p>
									Una volta aggregati gli ordini verranno visualizzati come uno solo pur mantenendo ciascuno i suoi attributi. Questa funzione è consigliata per facilitare l'amministrazione di ordini che, ad esempio, vengono consegnati nella stessa data.
								</p>

								<hr/>

								<div id="aggregable-list" data-fetch-url="{{ url('aggregates/create') }}">
									@include('order.aggregable', ['orders' => $orders])
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
								<button type="submit" class="btn btn-success">Salva</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endif
	</div>

	<div class="clearfix"></div>
	<hr/>
</div>

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'order-list', 'items' => $orders])
	</div>
</div>

@endsection
