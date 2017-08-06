<?php

$classes = App\CreditableTrait::acceptedClasses();
$target_classes = [];

$target_classes[] = [
    'value' => null,
    'label' => 'Nessuno',
];

foreach($classes as $class => $name) {
    $target_classes[] = [
        'value' => $class,
        'label' => $name,
    ];
}

?>

@include('commons.textfield', ['obj' => $movementtype, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
@include('commons.boolfield', ['obj' => $movementtype, 'name' => 'allow_negative', 'label' => 'Accetta Valori Negativi'])
@include('commons.decimalfield', ['obj' => $movementtype, 'name' => 'fixed_value', 'label' => 'Valore Default', 'postlabel' => '€'])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'sender_type',
    'label' => 'Pagante',
    'values' => $target_classes
])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'target_type',
    'label' => 'Pagato',
    'values' => $target_classes
])
