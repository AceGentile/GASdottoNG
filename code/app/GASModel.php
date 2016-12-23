<?php

namespace App;

use URL;

trait GASModel
{
    public function printableName()
    {
        return $this->name;
    }

    public function printableHeader()
    {
        $ret = $this->printableName();
        $icons = $this->icons();

        if (!empty($icons)) {
            $ret .= '<div class="pull-right">';

            foreach ($icons as $i) {
                $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
            }

            $ret .= '</div>';
        }

        return $ret;
    }

    public function printableDate($name)
    {
        $t = strtotime($this->$name);

        return ucwords(strftime('%A %d %B %G', $t));
    }

    private function relatedController()
    {
        $class = get_class($this);
        list($namespace, $class) = explode('\\', $class);

        return str_plural($class).'Controller';
    }

    public function getDisplayURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@index', $controller);

        return URL::action($action).'#'.$this->id;
    }

    public function getShowURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@show', $controller);

        return URL::action($action, $this->id);
    }

    public static function iconsMap()
    {
        return [
            'User' => [
                'king' => (object) [
                    'test' => function ($obj) {
                        return $obj->gas->userHas('gas.super', $obj);
                    },
                    'text' => 'Utente amministratore',
                ],
            ],
            'Supplier' => [
                'pencil' => (object) [
                    'test' => function ($obj) {
                        return $obj->userCan('supplier.modify');
                    },
                    'text' => 'Puoi modificare il fornitore',
                ],
                'th-list' => (object) [
                    'test' => function ($obj) {
                        return $obj->userCan('supplier.orders');
                    },
                    'text' => 'Puoi aprire nuovi ordini per il fornitore',
                ],
                'arrow-down' => (object) [
                    'test' => function ($obj) {
                        return $obj->userCan('supplier.shippings');
                    },
                    'text' => 'Gestisci le consegne per il fornitore',
                ],
            ],
            'Product' => [
                'star' => (object) [
                    'test' => function ($obj) {
                        return !empty($obj->discount) && $obj->discount != 0;
                    },
                    'text' => 'Scontato',
                ],
                'off' => (object) [
                    'test' => function ($obj) {
                        return $obj->active == false;
                    },
                    'text' => 'Disabilitato',
                ],
            ],
            'Aggregate' => [
                'th-list' => (object) [
                    'test' => function ($obj) {
                        return $obj->userCan('supplier.orders');
                    },
                    'text' => 'Puoi modificare l\'ordine',
                ],
                'arrow-down' => (object) [
                    'test' => function ($obj) {
                        return $obj->userCan('supplier.shippings');
                    },
                    'text' => 'Gestisci le consegne per l\'ordine',
                ],
                'play' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'open';
                    },
                    'text' => 'Ordine aperto',
                ],
                'pause' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'suspended';
                    },
                    'text' => 'Ordine sospeso',
                ],
                'stop' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'closed';
                    },
                    'text' => 'Ordine chiuso',
                ],
                'step-forward' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'shipped';
                    },
                    'text' => 'Ordine consegnato',
                ],
                'eject' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'archived';
                    },
                    'text' => 'Ordine archiviato',
                ],
            ],
            'Order' => [
                'th-list' => (object) [
                    'test' => function ($obj) {
                        return $obj->supplier->userCan('supplier.orders');
                    },
                    'text' => 'Puoi modificare l\'ordine',
                ],
                'arrow-down' => (object) [
                    'test' => function ($obj) {
                        return $obj->supplier->userCan('supplier.shippings');
                    },
                    'text' => 'Gestisci le consegne per l\'ordine',
                ],
                'play' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'open';
                    },
                    'text' => 'Ordine aperto',
                ],
                'pause' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'suspended';
                    },
                    'text' => 'Ordine sospeso',
                ],
                'stop' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'closed';
                    },
                    'text' => 'Ordine chiuso',
                ],
                'step-forward' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'shipped';
                    },
                    'text' => 'Ordine consegnato',
                ],
                'eject' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'archived';
                    },
                    'text' => 'Ordine archiviato',
                ],
            ],
            'AggregateBooking' => [
                'time' => (object) [
                    'test' => function ($obj) {
                        return $obj->status != 'shipped';
                    },
                    'text' => 'Da consegnare',
                ],
                'ok' => (object) [
                    'test' => function ($obj) {
                        return $obj->status == 'shipped';
                    },
                    'text' => 'Consegnato',
                ],
            ],
        ];
    }

    public function icons()
    {
        $class = get_class($this);
        list($namespace, $class) = explode('\\', $class);

        $map = self::iconsMap();
        $ret = [];

        if (isset($map[$class])) {
            foreach ($map[$class] as $icon => $condition) {
                $t = $condition->test;
                if ($t($this)) {
                    $ret[] = $icon;
                }
            }
        }

        return $ret;
    }

    public static function iconsLegend($class)
    {
        $map = self::iconsMap();
        $ret = [];

        if (isset($map[$class])) {
            foreach ($map[$class] as $icon => $condition) {
                $ret[$icon] = $condition->text;
            }
        }

        return $ret;
    }
}
