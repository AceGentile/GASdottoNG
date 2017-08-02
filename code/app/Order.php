<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

use Auth;
use DB;
use URL;

use App\GASModel;
use App\SluggableID;
use App\BookedProduct;

class Order extends Model
{
    use AttachableTrait, GASModel, SluggableID, PayableTrait;

    public $incrementing = false;

    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }

    public function aggregate()
    {
        return $this->belongsTo('App\Aggregate');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product')->with('measure')->with('category')->with('variants')->withPivot('discount_enabled');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking')->with('user');
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier->id, str_slug(strftime('%d %B %G', strtotime($this->start))));
    }

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (!empty($this->comment))
            $ret .= ' - ' . $this->comment;

        return $ret;
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

        $ret .= sprintf('<br/><small>%s</small>', $this->printableDates());

        return $ret;
    }

    public function printableDates()
    {
        $start = strtotime($this->start);
        $end = strtotime($this->end);
        $string = sprintf('da %s a %s', strftime('%A %d %B %G', $start), strftime('%A %d %B %G', $end));
        if ($this->shipping != null && $this->shipping != '0000-00-00') {
            $shipping = strtotime($this->shipping);
            $string .= sprintf(', in consegna %s', strftime('%A %d %B %G', $shipping));
        }

        return $string;
    }

    public function getBookingURL()
    {
        return URL::action('BookingController@index').'#' . $this->aggregate->id;
    }

    public function userBooking($userid = null)
    {
        if ($userid == null) {
            $userid = Auth::user()->id;
        }

        $ret = $this->hasMany('App\Booking')->whereHas('user', function ($query) use ($userid) {
            $query->where('id', '=', $userid);
        })->first();

        if ($ret == null) {
            $b = new Booking();
            $b->user_id = $userid;
            $b->order_id = $this->id;
            $b->status = 'pending';

            return $b;
        } else {
            return $ret;
        }
    }

    public function getInternalNumberAttribute()
    {
        $year = date('Y', strtotime($this->start));
        return (Order::where('supplier_id', $this->supplier_id)->where(DB::raw('YEAR(start)'), $year)->where('start', '<', $this->start)->count() + 1) . '/' . $year;
    }

    /*
        Se il prodotto è contenuto nell'ordine la funzione ritorna TRUE
        e la referenza a $product viene sostituita con quella interna
        all'ordine stesso, per poter accedere ai valori nella tabella
        pivot
    */
    public function hasProduct(&$product)
    {
        foreach ($this->products as $p) {
            if ($p->id == $product->id) {
                $product = $p;

                return true;
            }
        }

        return false;
    }

    public function isActive()
    {
        return $this->status != 'shipped' && $this->status != 'archived';
    }

    public function isRunning()
    {
        return $this->status == 'open';
    }

    public function calculateSummary($products = null)
    {
        $summary = (object) [
            'order' => $this->id,
            'price' => 0,
            'products' => [],
            'by_variant' => [],
        ];

        $order = $this;

        if ($products == null) {
            $products = $order->supplier->products;
            $external_products = false;
        }
        else {
            $external_products = true;
        }

        $total_price = 0;
        $total_price_delivered = 0;
        $total_transport = 0;

        foreach ($products as $product) {
            $q = BookedProduct::with('variants')->with('product')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $quantity = $q->sum('quantity');
            if(!$quantity)
                $quantity = 0;

            $delivered = $q->sum('delivered');
            if(!$delivered)
                $delivered = 0;

            $transport = $quantity * $product->transport;

            $booked = $q->get();
            $price = 0;
            $price_delivered = 0;

            foreach ($booked as $b) {
                if ($external_products)
                    $b->setRelation('product', $product);

                $price += $b->quantityValue();
                $price_delivered += $b->deliveredValue();

                if($b->variants->isEmpty() == false) {
                    $variants = [];

                    foreach($b->variants as $v) {
                        $name = $v->printableName();
                        if(isset($variants[$name]) == false) {
                            $variants[$name] = [
                                'quantity' => 0,
                                'price' => 0
                            ];
                        }

                        $variants[$name]['quantity'] += $v->quantity;
                        $variants[$name]['price'] += $v->quantityValue();
                    }

                    $summary->by_variant[$product->id] = $variants;
                }
            }

            if ($product->portion_quantity > 0) {
                $quantity = $quantity * $product->portion_quantity;
                $delivered = $delivered * $product->portion_quantity;
            }

            $summary->products[$product->id]['quantity'] = $quantity;
            $summary->products[$product->id]['price'] = $price;
            $summary->products[$product->id]['transport'] = $transport;
            $summary->products[$product->id]['delivered'] = $delivered;
            $summary->products[$product->id]['price_delivered'] = $price_delivered;

            $total_price += $price;
            $total_price_delivered += $price_delivered;
            $total_transport += $transport;

            $summary->products[$product->id]['notes'] = false;
            if ($product->package_size != 0 && $quantity != 0) {
                if ($product->portion_quantity <= 0) {
                    $test = $product->package_size;
                } else {
                    $test = round($product->portion_quantity * $product->package_size, 2);
                }

                $test = round($quantity % $test);
                if ($test != 0) {
                    $summary->products[$product->id]['notes'] = true;
                }
            }
        }

        $summary->price = $total_price;
        $summary->price_delivered = $total_price_delivered;
        $summary->transport = $total_transport;

        return $summary;
    }

    protected function defaultAttachments()
    {
        $ret = [];

        /*
            Documento con i prodotti e le relative quantità totali.
            Solitamente destinato al fornitore, come riassunto dell'ordine
            complessivo
        */
        $summary = new Attachment();
        $summary->name = 'Riassunto Prodotti (CSV)';
        $summary->url = url('orders/document/'.$this->id.'/summary/csv');
        $summary->internal = true;
        $ret[] = $summary;

        $summary = new Attachment();
        $summary->name = 'Riassunto Prodotti (PDF)';
        $summary->url = url('orders/document/'.$this->id.'/summary/pdf');
        $summary->internal = true;
        $ret[] = $summary;

        /*
            Rappresentazione strutturata delle prenotazioni
            effettuate, da usare in fase di consegna
        */
        $shipping = new Attachment();
        $shipping->name = 'Dettaglio Consegne';
        $shipping->url = url('orders/document/'.$this->id.'/shipping');
        $shipping->internal = true;
        $ret[] = $shipping;

        /*
            CVS completo dei prodotti, degli utenti e delle quantità (ordinate e
            consegnate)
        */
        $table = new Attachment();
        $table->name = 'Tabella Complessiva Prodotti Ordinati';
        $table->url = url('orders/document/'.$this->id.'/table/booked');
        $table->internal = true;
        $ret[] = $table;

        $table = new Attachment();
        $table->name = 'Tabella Complessiva Prodotti Consegnati';
        $table->url = url('orders/document/'.$this->id.'/table/shipped');
        $table->internal = true;
        $ret[] = $table;

        return $ret;
    }

    public function getPermissionsProxies()
    {
        return [$this->supplier];
    }
}
