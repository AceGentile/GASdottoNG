@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#profile" role="tab" data-toggle="tab">Anagrafica</a></li>

            @if($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas))
                <li role="presentation"><a href="#accounting" role="tab" data-toggle="tab">Contabilità</a></li>
            @endif

            @if($user->can('users.subusers'))
                <li role="presentation"><a href="#friends" role="tab" data-toggle="tab">Amici</a></li>
            @endif
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="profile">
                <form class="form-horizontal inner-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}">
                    <div class="row">
                        <div class="col-md-6">
                            @include('user.base-edit', ['user' => $user])

                            <hr>

                            @include('commons.contactswidget', ['obj' => $user])
                        </div>
                        <div class="col-md-6">
                            @if($user->isFriend() == false)
                                @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])

                                @if(Gate::check('users.admin', $currentgas))
                                    @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                                    @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                                @else
                                    @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                                    @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                                @endif

                                @if($currentgas->getConfig('annual_fee_amount') != 0)
                                    @include('commons.staticmovementfield', [
                                        'obj' => $user->fee,
                                        'name' => 'fee_id',
                                        'label' => _i('Quota Associativa'),
                                        'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)
                                    ])
                                @endif

                                @if($currentgas->getConfig('deposit_amount') != 0)
                                    @include('commons.staticmovementfield', [
                                        'obj' => $user->deposit,
                                        'name' => 'deposit_id',
                                        'label' => _i('Deposito'),
                                        'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)
                                    ])
                                @endif

                                <?php $places = App\Delivery::orderBy('name', 'asc')->get() ?>
                                @if($places->isEmpty() == false)
                                    @include('commons.selectobjfield', [
                                        'obj' => $user,
                                        'name' => 'preferred_delivery_id',
                                        'objects' => $places,
                                        'label' => _i('Luogo di Consegna'),
                                        'extra_selection' => [
                                            '0' => _i('Nessuno')
                                        ]
                                    ])
                                @endif

                                <hr/>

                                @include('commons.permissionsviewer', ['object' => $user, 'editable' => true])
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group pull-right main-form-buttons" role="group">
                                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas))
                <div role="tabpanel" class="tab-pane" id="accounting">
                    @include('movement.targetlist', ['target' => $user])
                </div>
            @endif

            @if($user->can('users.subusers'))
                <div role="tabpanel" class="tab-pane" id="friends">
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.addingbutton', [
                                'user' => null,
                                'template' => 'friend.base-edit',
                                'typename' => 'friend',
                                'typename_readable' => _i('Amico'),
                                'targeturl' => 'friends'
                            ])
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.loadablelist', [
                                'identifier' => 'friend-list',
                                'items' => $user->friends,
                                'url' => 'friends'
                            ])
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@stack('postponed')

@endsection
