<?php

namespace App;

use Auth;
use URL;

trait GASModel
{
    public function printableName()
    {
        return $this->name;
    }

    public function getPrintableNameAttribute()
    {
        return $this->printableName();
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

    /*
        Questa va all'occorrenza sovrascritta
    */
    public static function commonClassName()
    {
        return 'Oggetto';
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function getPermissionsProxies()
    {
        return null;
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function scopeEnabled($query)
    {
        return $query->whereNotNull('id');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('name', 'asc');
    }

    public static function iconsMap()
    {
        static $icons = null;

        if ($icons == null) {
            $user = Auth::user();

            /*
                La chiave di ogni array interno è il nome dell'icona FontAwesome
                da usare per la relativa icona. Usare un nome non esistente per
                avere il filtro ma non l'icona (lasciare un punto '.' davanti
                per indicare che è una azione deliberata)
            */
            $icons = [
                'Supplier' => [
                    'pencil' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.modify', $obj);
                        },
                        'text' => 'Puoi modificare il fornitore',
                    ],
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => 'Puoi aprire nuovi ordini per il fornitore',
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
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
                    '.on' => (object) [
                        'test' => function ($obj) {
                            return $obj->active == true;
                        },
                        'text' => 'Attivo',
                    ],
                ],
                'Aggregate' => [
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => 'Puoi modificare l\'ordine',
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
                        },
                        'text' => 'Gestisci le consegne per l\'ordine',
                    ],
                    'play' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'open';
                        },
                        'text' => 'Prenotazioni Aperte',
                    ],
                    'pause' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'suspended';
                        },
                        'text' => 'In Sospeso',
                    ],
                    'stop' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'closed';
                        },
                        'text' => 'Prenotazioni Chiuse',
                    ],
                    'step-forward' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'shipped';
                        },
                        'text' => 'Consegnato',
                    ],
                    'eject' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'archived';
                        },
                        'text' => 'Archiviato',
                    ],
                ],
                'Order' => [
                    'th-list' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.orders', $obj);
                        },
                        'text' => 'Puoi modificare l\'ordine',
                    ],
                    'arrow-down' => (object) [
                        'test' => function ($obj) use ($user) {
                            return $user->can('supplier.shippings', $obj);
                        },
                        'text' => 'Gestisci le consegne per l\'ordine',
                    ],
                    'play' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'open';
                        },
                        'text' => 'Prenotazioni Aperte',
                    ],
                    'pause' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'suspended';
                        },
                        'text' => 'In Sospeso',
                    ],
                    'stop' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'closed';
                        },
                        'text' => 'Prenotazioni Chiuse',
                    ],
                    'step-forward' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'shipped';
                        },
                        'text' => 'Consegnato',
                    ],
                    'eject' => (object) [
                        'test' => function ($obj) {
                            return $obj->status == 'archived';
                        },
                        'text' => 'Archiviato',
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
                'User' => [
                ],
            ];

            /*
                Se la gestione delle quote di iscrizione è abilitata, viene
                attivata la relativa icona per distinguere gli utenti che non
                l'hanno pagata o rinnovata
            */
            if (Auth::user()->gas->getConfig('annual_fee_amount') != 0) {
                $icons['User']['euro'] = (object) [
                    'test' => function ($obj) {
                        return $obj->fee_id == 0;
                    },
                    'text' => 'Quota non Pagata',
                ];
            }

            /*
                Questo è per generare le icone dei ruoli degli utenti
            */
            $roles = Role::where('always', false)->get();
            foreach($roles as $index => $role) {
                $icons['User']['king' . $index] = (object) [
                    'test' => function($obj) use ($role) {
                        foreach($obj->roles as $r)
                            if ($r->id == $role->id)
                                return true;
                        return false;
                    },
                    'text' => $role->name
                ];
            }
        }

        return $icons;
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
