<table class="table order-summary">
	<thead>
		<tr>
			@if($order->status != 'archived')
				<th width="2%"></th>
				<th width="15%">Prodotto</th>
				<th width="7%">Prezzo</th>
				<th width="7%">Trasporto</th>
				<th width="5%">Sconto Prodotto</th>
				<th width="12%">Unità di Misura</th>
				<th width="9%">Quantità Ordinata</th>
				<th width="9%">Totale Prezzo</th>
				<th width="9%">Totale Trasporto</th>
				<th width="9%">Quantità Consegnata</th>
				<th width="9%">Totale Consegnato</th>
				<th width="9%">Note</th>
			@else
				<th width="25%">Prodotto</th>
				<th width="25%">Unità di Misura</th>
				<th width="25%">Quantità Consegnata</th>
				<th width="25%">Totale Consegnato</th>
			@endif
		</tr>
	</thead>
	<tbody>
		@foreach($order->supplier->products as $product)
			<?php $enabled = $order->hasProduct($product) ?>

			@if($enabled == false)
			<tr class="product-disabled hidden-sm hidden-xs">
			@else
			<tr>
			@endif
				@if($order->status != 'archived')
				<td>
					<input class="enabling-toggle" type="checkbox" name="enabled[]" value="{{ $product->id }}" <?php if($enabled) echo 'checked' ?> />
				</td>
				@endif

				<td>
					<input type="hidden" name="productid[]" value="{{ $product->id }}" />
					<label>{{ $product->printableName() }}</label>
				</td>

				@if($order->status != 'archived')
				<td class="product-price">
					<label class="full-price <?php if(!empty($product->discount) && $enabled && $product->pivot->discount_enabled) echo 'hidden' ?>">{{ printablePrice(applyPercentage($product->price, $order->discount)) }} €</label>
					<label class="product-discount-price <?php if(empty($product->discount) || !$enabled || ($enabled && !$product->pivot->discount_enabled)) echo 'hidden' ?>">{{ printablePrice(applyPercentage($product->discount_price, $order->discount)) }} €</label>
				</td>
				<td>
					<label>{{ printablePrice($product->transport) }} €</label>
				</td>
				<td>
					@if(!empty($product->discount))
						<input class="discount-toggle" type="checkbox" name="discounted[]" value="{{ $product->id }}" <?php if($enabled && $product->pivot->discount_enabled) echo 'checked' ?> />
					@endif
				</td>
				@endif

				<td>
					<label>{{ $product->measure->printableName() }}</label>
				</td>

				@if($order->status != 'archived')
				<td>
					<label>{{ $summary->products[$product->id]['quantity'] }}</label>
				</td>
				<td>
					<label>{{ printablePrice($summary->products[$product->id]['price']) }} €</label>
				</td>
				<td>
					<label>{{ printablePrice($summary->products[$product->id]['transport']) }} €</label>
				</td>
				@endif

				<td>
					<label>{{ $summary->products[$product->id]['delivered'] }}</label>
				</td>
				<td>
					<label>{{ printablePrice($summary->products[$product->id]['price_delivered']) }} €</label>
				</td>

				@if($order->status != 'archived')
				<td>
					@if($summary->products[$product->id]['notes'])
						<?php $random_identifier = rand(); ?>

						<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#fix-{{ $random_identifier }}">
							<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
						</button>

						@push('postponed')
							<div class="modal fade" id="fix-{{ $random_identifier }}" tabindex="-1" role="dialog">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<form method="POST" action="{{ url('orders/fixes/' . $order->id) }}">
											<input type="hidden" name="product" value="{{ $product->id }}" />

											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												<h4 class="modal-title" id="fix-{{ $random_identifier }}-label">Sistema Quantità</h4>
											</div>
											<div class="modal-body">
												<p>
													Dimensione confezione: {{ $product->package_size }}
												</p>

												<hr/>

												<table class="table table-striped">
													@foreach($product->bookingsInOrder($order) as $po)
													<tr>
														<td>
															<label>{{ $po->user->printableName() }}</label>
														</td>
														<td>
															<input type="hidden" name="booking[]" value="{{ $po->id }}" />

															<div class="input-group">
																<input type="number" class="form-control" name="quantity[]" value="{{ $po->getBookedQuantity($product) }}" />
																<div class="input-group-addon">{{ $product->printableMeasure() }}</div>
															</div>
														</td>
													</tr>
													@endforeach
												</table>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
												<button type="submit" class="btn btn-primary reloader" data-reload-target="#order-list">Salva</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						@endpush
					@endif
				</td>
				@endif
			</tr>
		@endforeach
	</tbody>
	<thead>
		<tr>
			@if($order->status != 'archived')
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>{{ printablePrice($summary->price) }} €</th>
				<th>{{ printablePrice($summary->transport) }} €</th>
				<th></th>
				<th>{{ printablePrice($summary->price_delivered) }} €</th>
				<th></th>
			@else
				<th></th>
				<th></th>
				<th></th>
				<th>{{ printablePrice($summary->price_delivered) }} €</th>
			@endif
		</tr>
	</thead>
</table>
