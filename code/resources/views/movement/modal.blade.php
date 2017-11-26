<?php

if ($obj == null)
    $obj = $default;

if (!isset($dom_id))
    $dom_id = rand();

if (!isset($editable))
    $editable = false;
if (!isset($amount_editable))
    $amount_editable = false;
if (!isset($amount_label))
    $amount_label = 'Valore';

?>

<div class="modal fade movement-modal" id="editMovement-{{ $dom_id }}" tabindex="-1" role="dialog" aria-labelledby="editMovement-{{ $dom_id }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ url('movements') }}" data-toggle="validator">
                <input type="hidden" name="update-field" value="movement-id-{{ $dom_id }}">
                <input type="hidden" name="update-field" value="movement-date-{{ $dom_id }}">
                <input type="hidden" name="close-modal" value="">
                <input type="hidden" name="post-saved-function" value="refreshFilter">
                @include('commons.extrafields')

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modifica Movimento</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="{{ $obj->id }}">
                    <input type="hidden" name="type" value="{{ $obj->type }}" />
                    <input type="hidden" name="sender_type" value="{{ $obj->sender_type }}" />
                    <input type="hidden" name="sender_id" value="{{ $obj->sender_id }}" />
                    <input type="hidden" name="target_type" value="{{ $obj->target_type }}" />
                    <input type="hidden" name="target_id" value="{{ $obj->target_id }}" />

                    @include('commons.decimalfield', [
                        'obj' => $obj,
                        'name' => 'amount',
                        'label' => $amount_label,
                        'is_price' => true,
                        'fixed_value' => $amount_editable ? false : ($editable ? false : $obj->amount)
                    ])

                    <div class="col-sm-{{ $fieldsize }} col-sm-offset-{{ $labelsize }}">
                        @if($obj->sender && array_search('App\CreditableTrait', class_uses($obj->sender)) !== false && count($obj->sender->balanceFields()) == 1)
                            <p class="sender-credit-status alert alert-{{ $obj->amount < $obj->sender->current_balance_amount ? 'success' : 'danger' }}">
                                Credito Attuale {{ $obj->sender->printableName() }}: <span class="current-sender-credit">{{ $obj->sender->current_balance_amount }}</span> €
                            </p>
                        @endif

                        @if($obj->target && array_search('App\CreditableTrait', class_uses($obj->target)) !== false && count($obj->target->balanceFields()) == 1)
                            <p class="alert alert-success">
                                {{ $obj->target->printableName() }}: {{ $obj->target->current_balance_amount }} €
                            </p>
                        @endif

                        <br/>
                    </div>

                    @include('commons.radios', [
                        'name' => 'method',
                        'label' => 'Metodo',
                        'values' => $obj->valid_payments
                    ])

                    @include('commons.datefield', [
                        'obj' => $obj,
                        'name' => 'date',
                        'label' => 'Data',
                        'defaults_now' => true
                    ])

                    @include('commons.textfield', [
                        'obj' => $obj,
                        'name' => 'identifier',
                        'label' => 'Identificativo',
                        'extra_wrap_class' => 'when-method-bank' . ($obj->method != 'bank' ? ' hidden' : '')
                    ])

                    @include('commons.textarea', [
                        'obj' => $obj,
                        'name' => 'notes',
                        'label' => 'Note'
                    ])
                </div>

                <div class="modal-footer">
                    @if($editable)
                        <button type="button" class="btn btn-danger spare-modal-delete-button" data-delete-url="{{ url('movements/' . $obj->id) }}">Elimina</button>
                    @endif

                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
