<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Log;

use App\GASModel;

class Movement extends Model
{
    use GASModel;

    /*
        Per verificare il corretto salvataggio di un movimento, non consultare
        l'ID dell'oggetto ma il valore di questo attributo dopo aver invocato
        la funzione save()
        Ci sono casi particolari (cfr. il salvataggio del pagamento dei una
        prenotazione per un ordine aggregato) in cui il singolo movimento non
        viene realmente salvato ma elaborato (in questo caso: scomposto in più
        movimenti), dunque l'oggetto in sé non viene riportato sul database
        anche se l'operazione, nel suo complesso, è andata a buon fine.
        Vedasi MovementsKeeper::saving(), MovementsController::store(), o le
        pre-callbacks definite in Movement::types()
    */
    public $saved = false;

    public function sender()
    {
        return $this->morphTo();
    }

    public function target()
    {
        return $this->morphTo();
    }

    public function getPaymentIconAttribute()
    {
        $types = $this->payments();

        foreach ($types as $id => $details) {
            if ($this->method == $id) {
                return $details->icon;
            }
        }

        return 'glyphicon-question-sign';
    }

    public function getTypeMetadataAttribute()
    {
        return self::types($this->type);
    }

    public function getValidPaymentsAttribute()
    {
        $movement_methods = $this->payments();
        $type_metadata = $this->type_metadata;
        $ret = [];

        foreach ($movement_methods as $method_id => $info) {
            if (isset($type_metadata->methods[$method_id])) {
                $ret[$method_id] = $info;
            }
        }

        return $ret;
    }

    public function printableName()
    {
        return sprintf('%s | %f €', $this->printableDate('created_at'), $this->amount);
    }

    public function printableType()
    {
        return $this->type_metadata->name;
    }

    public static function generate($type, $sender, $target, $amount)
    {
        $ret = new self();
        $ret->type = $type;
        $ret->sender_type = get_class($sender);
        $ret->sender_id = $sender->id;
        $ret->target_type = get_class($target);
        $ret->target_id = $target->id;

        $type_descr = self::types($type);
        if ($type_descr->fixed_value != false) {
            $ret->amount = $type_descr->fixed_value;
        } else {
            $ret->amount = $amount;
        }

        return $ret;
    }

    public function parseRequest(Request $request)
    {
        $metadata = $this->type_metadata;
        if (isset($metadata->callbacks['parse'])) {
            $metadata->callbacks['parse']($this, $request);
        }
    }

    public static function payments()
    {
        return [
            'cash' => (object) [
                'name' => 'Contanti',
                'identifier' => false,
                'icon' => 'glyphicon-euro',
            ],
            'credit' => (object) [
                'name' => 'Credito Utente',
                'identifier' => false,
                'icon' => 'glyphicon-ok',
            ],
            'bank' => (object) [
                'name' => 'Conto Corrente',
                'identifier' => true,
                'icon' => 'glyphicon-link',
            ],
        ];
    }

    public static function types($identifier = null)
    {
        $ret = [
            'deposit-pay' => (object) [
                'name' => 'Deposito cauzione socio del GAS',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['cash', 'deposits'], $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['bank', 'deposits'], $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $sender = $movement->sender;
                        $sender->deposit_id = $movement->id;
                        $sender->save();
                    },
                ],
            ],

            'deposit-return' => (object) [
                'name' => 'Restituzione cauzione socio del GAS',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\User',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['cash', 'deposits'], $movement->amount * -1);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance(['bank', 'deposits'], $movement->amount * -1);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $target = $movement->target;
                        $target->deposit_id = null;
                        $target->save();
                    },
                ],
            ],

            'annual-fee' => (object) [
                'name' => 'Versamento della quota annuale da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'credit' => (object) [
                        'handler' => function (Movement $movement) {
                            $sender = $movement->sender;
                            $sender->balance -= $movement->amount;
                            $sender->save();
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $sender = $movement->sender;
                        $sender->fee_id = $movement->id;
                        $sender->save();
                    },
                ],
            ],

            'booking-payment' => (object) [
                'name' => 'Pagamento di una prenotazione da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Booking',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->gas->alterBalance(['cash', 'suppliers'], $movement->amount);
                            $supplier = $movement->target->order->supplier;
                            $supplier->balance += $movement->amount;
                            $supplier->save();
                        },
                    ],
                    'credit' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->gas->alterBalance('suppliers', $movement->amount);
                            $sender = $movement->sender;
                            $sender->balance -= $movement->amount;
                            $sender->save();
                            $supplier = $movement->target->order->supplier;
                            $supplier->balance += $movement->amount;
                            $supplier->save();
                        },
                    ],
                ],
                'callbacks' => [
                    /*
                        Il problema di fondo è che, a livello utente, un aggregato riceve un solo pagamento, dunque
                        devo a posteriori dividere tale pagamento tra le prenotazioni al suo interno creando
                        movimenti individuali.
                        Qui assumo che l'ammontare pagato per ciascuna prenotazione corrisponda col totale consegnato
                        della prenotazione stessa
                    */
                    'pre' => function (Movement $movement) {
                        if ($movement->target_type == 'App\Aggregate') {
                            $total = $movement->amount;
                            $aggregate = $movement->target;
                            $user = $movement->sender;
                            $m = null;

                            /*
                                'handling_status' è un attributo fittizio allegato all'oggetto solo per determinare lo
                                stato corrente della consegna. Cfr. la callback parse()
                            */
                            $handling_status = $movement->handling_status;
                            unset($movement->handling_status);

                            foreach ($aggregate->orders as $order) {
                                $booking = $order->userBooking($user->id);

                                if (isset($handling_status->{$booking->id})) {
                                    $delivered = $handling_status->{$booking->id};
                                } else {
                                    $delivered = $booking->delivered;
                                }

                                if ($total < $delivered) {
                                    $delivered = $total;
                                }

                                $m = $movement->replicate();
                                $m->target_id = $booking->id;
                                $m->target_type = 'App\Booking';
                                $m->amount = $delivered;

                                /*
                                    Qui devo ricaricare la relazione "target", altrimenti resta in memoria quella precedente
                                    (che faceva riferimento ad un Aggregate, dunque non è corretta e sul salvataggio spacca
                                    tutto)
                                */
                                $m->load('target');

                                $m->save();

                                $total -= $delivered;
                                if ($total <= 0) {
                                    break;
                                }
                            }

                            if ($total > 0 && $m != null) {
                                $m->amount += $total;
                                $m->save();
                            }

                            return 2;
                        }

                        return 1;
                    },
                    'post' => function (Movement $movement) {
                        $target = $movement->target;
                        $target->payment_id = $movement->id;
                        $target->save();
                    },
                    'parse' => function (Movement &$movement, Request $request) {
                        if ($movement->target_type == 'App\Aggregate') {
                            if ($request->has('delivering-status')) {
                                $movement->handling_status = json_decode($request->input('delivering-status'));
                            }
                        }
                    },
                ],
            ],

            'order-payment' => (object) [
                'name' => 'Pagamento dell\'ordine presso il fornitore',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Order',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance(['cash', 'suppliers'], $movement->amount * -1);
                            $supplier = $movement->target->supplier;
                            $supplier->balance -= $movement->amount;
                            $supplier->save();
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance(['bank', 'suppliers'], $movement->amount * -1);
                            $supplier = $movement->target->supplier;
                            $supplier->balance -= $movement->amount;
                            $supplier->save();
                        },
                    ],
                ],
                'callbacks' => [
                    'post' => function (Movement $movement) {
                        $target = $movement->target;
                        $target->payment_id = $movement->id;
                        $target->save();
                    },
                ],
            ],

            'user-credit' => (object) [
                'name' => 'Deposito di credito da parte di un socio',
                'sender_type' => 'App\User',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $sender = $movement->sender;
                            $sender->balance += $movement->amount;
                            $sender->save();
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $sender = $movement->sender;
                            $sender->balance += $movement->amount;
                            $sender->save();
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'internal-transfer' => (object) [
                'name' => 'Trasferimento interno al GAS, dalla cassa al conto o viceversa',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Gas',
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                            $movement->target->alterBalance('bank', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                            $movement->target->alterBalance('cash', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'generic-get' => (object) [
                'name' => 'Prelievo generico',
                'sender_type' => 'App\Gas',
                'target_type' => null,
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'generic-put' => (object) [
                'name' => 'Versamento generico',
                'sender_type' => 'App\Gas',
                'target_type' => null,
                'allow_negative' => false,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount);
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount);
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],

            'supplier-rounding' => (object) [
                'name' => 'Arrotondamento/sconto fornitore',
                'sender_type' => 'App\Gas',
                'target_type' => 'App\Supplier',
                'allow_negative' => true,
                'fixed_value' => false,
                'methods' => [
                    'cash' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('cash', $movement->amount * -1);
                            $target = $movement->target;
                            $target->balance += $movement->amount;
                            $target->save();
                        },
                    ],
                    'bank' => (object) [
                        'handler' => function (Movement $movement) {
                            $movement->sender->alterBalance('bank', $movement->amount * -1);
                            $target = $movement->target;
                            $target->balance += $movement->amount;
                            $target->save();
                        },
                    ],
                ],
                'callbacks' => [
                ],
            ],
        ];

        if ($identifier) {
            return $ret[$identifier];
        } else {
            return $ret;
        }
    }
}
