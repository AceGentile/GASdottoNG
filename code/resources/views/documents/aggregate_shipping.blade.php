<html>
    <body>
        <h3>Dettaglio Consegne Ordini<br/>
            @foreach($aggregate->orders as $order)
                {{ $order->supplier->name }} {{ $order->internal_number }}<br/>
            @endforeach
        </h3>

        @foreach($aggregate->bookings as $super_booking)
            @if($super_booking->total_value == 0)
                @continue
            @endif

            <table border="1" style="width: 100%" cellpadding="5">
                <thead>
                    <tr>
                        <th colspan="3">{{ $super_booking->user->printableName() }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($super_booking->bookings as $booking)
                        @foreach($booking->products as $product)
                            @if($product->variants->isEmpty() == false)
                                @foreach($product->variants as $variant)
                                    <tr>
                                        <td width="40%">{{ $product->product->printableName() }}</td>
                                        <td width="40%">{{ printableQuantity($variant->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }} {{ $variant->printableName() }}</td>
                                        <td width="20%">{{ printablePrice($variant->quantityValue(), ',') }} €</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td width="40%">{{ $product->product->printableName() }}</td>
                                    <td width="40%">{{ printableQuantity($product->quantity, $product->product->measure->discrete, 2, ',') }} {{ $product->product->printableMeasure(true) }}</td>
                                    <td width="20%">{{ printablePrice($product->quantityValue(), ',') }} €</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totale: {{ printablePrice($super_booking->total_value, ',') }} €</th>
                    </tr>
                </tfoot>
            </table>

            <p>&nbsp;</p>
        @endforeach
    </body>
</html>
