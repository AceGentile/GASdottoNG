@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'booking-list',
            'items' => $orders,
            'url' => 'bookings',
            'header_function' => 'printableUserHeader',
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ])
    </div>
</div>

@endsection
