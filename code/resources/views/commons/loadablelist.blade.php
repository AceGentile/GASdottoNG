<?php

if(isset($legend) == false)
    $legend = null;
if(isset($filters) == false)
    $filters = [];

?>

@if(!empty($filters) || !is_null($legend))
    <div class="row">
        <div class="col-md-12">
            @if(!empty($filters))
                <div class="btn-group hidden-xs hidden-sm list-filters" role="group" aria-label="Filtri" data-list-target="#{{ $identifier }}">
                    @foreach($filters as $attribute => $info)
                        <button type="button" class="btn btn-default" data-filter-attribute="{{ $attribute }}"><span class="glyphicon glyphicon-{{ $info->icon }}" aria-hidden="true"></span>&nbsp;{{ $info->label }}</button>
                    @endforeach
                </div>
            @endif

            @if(!is_null($legend))
                @include('commons.iconslegend', ['class' => $legend->class, 'target' => '#' . $identifier])
            @endif
        </div>
    </div>
@endif

<div id="wrapper-{{ $identifier }}">
    <div class="alert alert-info {{ count($items) != 0 ? 'hidden' : '' }}" role="alert" id="empty-{{ $identifier }}">
        Non ci sono elementi da visualizzare.
    </div>

    <div class="list-group loadablelist" id="{{ $identifier }}">
        @foreach($items as $item)
            @if(isset($url))
                <?php $u = url($url.'/'.$item->id) ?>
            @else
                <?php $u = $item->getShowURL() ?>
            @endif

            <?php

            $extra_class = '';
            $extra_attributes = '';

            foreach($filters as $attribute => $info) {
                if($item->$attribute == $info->value) {
                    $extra_class = 'hidden';
                    $extra_attributes = 'data-filtered-' . $attribute . '="true"';
                }
            }

            ?>

            <a data-element-id="{{ $item->id }}" {!! $extra_attributes !!} href="{{ $u }}" class="loadable-item list-group-item {{ $extra_class }}">{!! $item->printableHeader() !!}</a>
        @endforeach
    </div>
</div>
