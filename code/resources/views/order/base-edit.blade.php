<?php

$user = Auth::user();

if ($user->gas->userCan('gas.super')) {
    $suppliers = App\Supplier::orderBy('name', 'asc')->get();
} else {
    $suppliers = App\Supplier::whereHas('permissions', function ($query) {
        $query->where('action', '=', 'supplier.orders')->where(function ($query) {
            $query->where('user_id', '=', Auth::user()->id)->orWhere('user_id', '=', '*');
        });
    })->orderBy('name', 'asc')->get();
}

?>

@include('commons.selectobjfield', [
    'obj' => $order,
    'name' => 'supplier_id',
    'label' => 'Fornitore',
    'mandatory' => true,
    'objects' => $suppliers
])

@include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])
@include('commons.datefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura', 'mandatory' => true])
@include('commons.datefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])

@include('commons.orderstatus', ['order' => $order])
