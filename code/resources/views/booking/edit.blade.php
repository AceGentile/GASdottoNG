<?php

$more_orders = ($aggregate->orders->count() > 1);
$grand_total = 0;

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('booking/' . $aggregate->id . '/user/' . $user->id) }}">
	@foreach($aggregate->orders as $order)
		@if($more_orders)
			<h3>{{ $order->printableName() }}</h3>
		@endif

		<?php $o = $order->userBooking($user->id) ?>

		<table class="table table-striped booking-editor">
			<thead>
				<th width="25%"></th>
				<th width="35%"></th>
				<th width="25%"></th>
				<th width="15%"></th>
			</thead>
			<tbody>
				@foreach($order->products as $product)
				<tr class="booking-product">
					<td>
						<label class="static-label">
							{{ $product->name }}

							@if(!empty($product->description))
								<button type="button" class="btn btn-xs btn-default" data-container="body" data-toggle="popover" data-placement="right" data-trigger="hover" data-content="{{ str_replace('"', '\"', $product->description) }}">
									<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
								</button>
							@endif
						</label>
					</td>

					<td>
						@include('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => true])
					</td>

					<td>
						<label class="static-label">{{ $product->printableDetails($order) }}</label>
					</td>

					<td class="text-right">
						<label class="static-label">{!! $product->printablePrice($order) !!}</label>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th class="text-right">Totale: <span class="booking-total">{{ printablePrice($o->value) }}</span> €</th>
				</tr>
			</tfoot>
		</table>

		<?php $grand_total += $o->value ?>
	@endforeach

	@if($more_orders)
		<table class="table">
			<tfoot>
				<tr>
					<th>
						<div class="pull-right">
							<strong>Totale Complessivo: <span class="all-bookings-total">{{ printablePrice($grand_total) }}</span> €</strong>
						</div>
					</th>
				</tr>
			</tfoot>
		</table>
	@endif

	<div class="row">
		<div class="col-md-12">
			<div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
				<button type="button" class="btn btn-danger delete-booking">Annulla Prenotazione</button>
				<button type="submit" class="btn btn-success saving-button">Salva</button>
			</div>
		</div>
	</div>
</form>
