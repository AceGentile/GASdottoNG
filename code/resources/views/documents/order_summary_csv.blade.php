<?php $summary = $order->calculateSummary(); ?>Nome;Quantità Totale;Unità Misura;Prezzo Totale;Trasporto
@foreach($order->supplier->products as $product)
@if($order->hasProduct($product))
@if(isset($summary->by_variant[$product->id]))
@foreach($summary->by_variant[$product->id] as $name => $variant)
@if($variant['quantity'] != 0)
{{ $product->printableName() }} {{ $name }};{{ printableQuantity($variant['quantity'], $product->measure->discrete, 2, ',') }};{{ $product->printableMeasure(true) }};{{ printablePrice($variant['price'], ',') }};{{ printablePrice($summary->products[$product->id]['transport'], ',') }}
@endif
@endforeach
@else
@if($summary->products[$product->id]['quantity_pieces'])
{{ $product->printableName() }};{{ printableQuantity($summary->products[$product->id]['quantity_pieces'], $product->measure->discrete, 2, ',') }};{{ $product->printableMeasure(true) }};{{ printablePrice($summary->products[$product->id]['price'], ',') }};{{ printablePrice($summary->products[$product->id]['transport'], ',') }}
@endif
@endif
@endif
@endforeach
