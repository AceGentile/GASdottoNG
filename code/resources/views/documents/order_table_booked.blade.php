@include('documents.order_table_master', [
    'selected_bookings' => $order->bookings,
    'get_function' => 'getBookedQuantity',
    'get_total' => 'value',
    'get_function_real' => true
])
