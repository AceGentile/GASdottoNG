<?php

$more_orders = ($aggregate->orders->count() > 1);
$handling_movements = $currentgas->userHas('movements.admin');
$tot_amount = 0;
$tot_delivered = [];
$rand = rand();

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}" data-reference-modal="editMovement-{{ $rand }}">
	@foreach($aggregate->orders as $order)
		@if($more_orders)
			<h3>{{ $order->printableName() }}</h3>
		@endif

		<?php

		$o = $order->userBooking($user->id);
		$now_delivered = $o->delivered;
		$tot_delivered[$o->id] = $now_delivered;
		$tot_amount += $now_delivered;

		?>

		<div class="row">
			<div class="col-md-6">
				@include('commons.staticobjfield', ['obj' => $o, 'name' => 'deliverer', 'label' => 'Consegnato Da'])
				@include('commons.staticdatefield', ['obj' => $o, 'name' => 'delivery', 'label' => 'Data Consegna'])
			</div>
			<div class="col-md-6">
				@include('commons.staticmovementfield', ['obj' => $o->payment, 'label' => 'Pagamento', 'rand' => $rand])
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<table class="table table-striped booking-editor" data-booking-id="{{ $o->id }}" data-order-id="{{ $order->id }}">
					<thead>
						<th width="33%"></th>
						<th width="33%"></th>
						<th width="33%"></th>
					</thead>
					<tbody>
						@foreach($o->products as $product)
							@if($product->variants->isEmpty() == true)

								<tr class="booking-product">
									<td>
										<input type="hidden" name="product-partitioning" value="{{ $product->product->portion_quantity }}" class="skip-on-submit" />
										<input type="hidden" name="product-price" value="{{ $product->product->price + $product->product->transport }}" class="skip-on-submit" />
										<label class="static-label">{{ $product->product->name }}</label>
									</td>

									<td>
										<label class="static-label booking-product-booked">{{ $product->quantity }}</label>
									</td>

									<td>
										<div class="input-group booking-product-quantity">
											<input type="number" step="any" min="0" class="form-control" name="{{ $product->product->id }}" value="{{ $product->delivered }}" />
											<div class="input-group-addon">{{ $product->product->printableMeasure() }}</div>
										</div>
									</td>
								</tr>

							@else

								@foreach($product->variants as $var)
									<?php

									$price = $product->product->price + $product->product->transport;
									foreach($var->components as $comp)
										$price += $comp->value->price_offset;

									?>

									<tr class="booking-product">
										<td>
											<input type="hidden" name="product-partitioning" value="{{ $product->product->portion_quantity }}" class="skip-on-submit" />
											<input type="hidden" name="product-price" value="{{ $price }}" class="skip-on-submit" />

											<label class="static-label">{{ $product->product->name }}: {{ $var->printableName() }}</label>

											<input type="hidden" name="{{ $product->product->id }}" value="1" />
											@foreach($var->components as $comp)
											<input type="hidden" name="variant_selection_{{ $comp->variant->id }}[]" value="{{ $comp->value->id }}" />
											@endforeach
										</td>

										<td>
											<label class="static-label booking-product-booked">{{ $var->quantity }}</label>
										</td>

										<td>
											<div class="input-group booking-product-quantity">
												<input type="number" step="any" min="0" class="form-control" name="variant_quantity_{{ $product->product->id }}[]" value="{{ $var->delivered }}" />
												<div class="input-group-addon">{{ $product->product->printableMeasure() }}</div>
											</div>
										</td>
									</tr>
								@endforeach

							@endif
						@endforeach

						<tr class="hidden booking-product fit-add-product">
							<td>
								<select class="fit-add-product-select form-control">
									<option value="-1">Seleziona un Prodotto</option>
									@foreach($order->products as $product)
									<option value="{{ $product->id }}">{{ $product->name }}</option>
									@endforeach
								</select>
							</td>

							<td>&nbsp;</td>
							<td class="bookable-target">&nbsp;</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th><button class="btn btn-default add-booking-product">Aggiungi Prodotto</button></th>
							<th></th>
							<th class="text-right">Totale: <span class="booking-total">{{ printablePrice($now_delivered) }}</span> €</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	@endforeach

	<div class="row">
		<div class="col-md-12">
			<div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
				<button class="btn btn-default preload-quantities">Carica Quantità Prenotate</button>

				@if($handling_movements)
				<button type="button" class="btn btn-success saving-button" data-toggle="modal" data-target="#editMovement-{{ $rand }}">Salva</button>
				@else
				<button type="submit" class="btn btn-success saving-button">Salva</button>
				@endif
			</div>
		</div>
	</div>
</form>

@if($handling_movements)
	@include('movement.modal', [
		'dom_id' => $rand,
		'obj' => $o->payment,
		'default' => \App\Movement::generate('booking-payment', $user, $aggregate, $tot_amount),
		'extra' => [
			'post-saved-function' => 'submitDeliveryForm',
			'delivering-status' => json_encode($tot_delivered)
		],
	])
@endif
