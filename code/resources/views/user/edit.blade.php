<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
            @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
            @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => _i('Persone in Famiglia')])
            @include('commons.contactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @if($currentuser->can('users.admin', $currentgas))
                @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @else
                @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @endif

            @include('user.movements', ['editable' => true])

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])

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

            <div class="form-group">
                <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Stato') }}</label>

                <div class="col-sm-{{ ceil($fieldsize / 2) }}">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default {{ $user->deleted_at == null ? 'active' : '' }}">
                            <input type="radio" name="status" value="active" {{ $user->deleted_at == null ? 'checked' : '' }}> {{ _i('Attivo') }}
                        </label>
                        <label class="btn btn-default {{ $user->suspended == true && $user->deleted_at != null ? 'active' : '' }}">
                            <input type="radio" name="status" value="suspended" {{ $user->suspended == true && $user->deleted_at != null ? 'checked' : '' }}> {{ _i('Sospeso') }}
                        </label>
                        <label class="btn btn-default {{ $user->suspended == false && $user->deleted_at != null ? 'active' : '' }}">
                            <input type="radio" name="status" value="deleted" {{ $user->suspended == false && $user->deleted_at != null ? 'checked' : '' }}> {{ _i('Cessato') }}
                        </label>
                    </div>
                </div>
                <div class="user-status-date col-sm-{{ floor($fieldsize / 2) }} {{ $user->deleted_at == null ? 'hidden' : '' }}">
                    @include('commons.datefield', ['obj' => $user, 'name' => 'deleted_at', 'label' => _i('Data'), 'squeeze' => true])
                </div>
            </div>

            @if(!empty($currentgas->rid['iban']))
                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Configurazione SEPA') }}</label>

                    <div class="col-sm-{{ $fieldsize }}">
                        @include('commons.textfield', ['obj' => $user, 'name' => 'rid->iban', 'label' => _i('IBAN'), 'squeeze' => true])

                        <div class="form-group">
                            <div class="col-sm-5">
                                @include('commons.textfield', ['obj' => $user, 'name' => 'rid->id', 'label' => _i('Identificativo Mandato SEPA'), 'squeeze' => true])
                            </div>
                            <div class="col-sm-7">
                                @include('commons.datefield', ['obj' => $user, 'name' => 'rid->date', 'label' => _i('Data Mandato SEPA'), 'squeeze' => true])
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user, 'editable' => true])
        </div>
    </div>

    @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas))
        @include('movement.targetlist', ['target' => $user])
    @endif

    @include('commons.formbuttons', ['obj' => $user, 'no_delete' => true])
</form>

@stack('postponed')
