<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

use Auth;
use URL;
use App\GASModel;
use App\AggregateBooking;

class Aggregate extends Model
{
    use GASModel;

    public function orders()
    {
        return $this->hasMany('App\Order')->with('supplier')->with('products')->orderBy('end', 'desc');
    }

    public function getStatusAttribute()
    {
        $priority = ['suspended', 'open', 'closed', 'shipped', 'archived'];
        $index = 10;

        foreach ($this->orders as $order) {
            $a = array_search($order->status, $priority);
            if ($a < $index) {
                $index = $a;
            }
        }

        return $priority[$index];
    }

    public static function getByStatus($status, $inverse = false)
    {
        $operator = '=';
        if ($inverse) {
            $operator = '!=';
        }

        return self::whereHas('orders', function ($query) use ($status, $operator) {
            $query->where('status', $operator, $status);
        })->get();
    }

    private function computeStrings()
    {
        $names = [];
        $dates = [];

        $orders = $this->orders;

        if ($orders->count() > 3) {
            $start_date = PHP_INT_MAX;
            $end_date = 0;
            $shipping_date = 0;

            foreach ($orders as $order) {
                $names[] = $order->printableName();

                $this_start = strtotime($order->start);
                if ($this_start < $start_date)
                    $start_date = $this_start;

                $this_end = strtotime($order->end);
                if ($this_end > $end_date)
                    $end_date = $this_end;

                if ($this->shipping != null && $this->shipping != '0000-00-00') {
                    $this_shipping = strtotime($order->shipping);
                    if ($this_shipping > $shipping_date)
                        $shipping_date = $this_shipping;
                }
            }

            $date_string = sprintf('da %s a %s', strftime('%A %d %B %G', $start_date), strftime('%A %d %B %G', $end_date));
            if ($shipping_date != 0)
                $date_string .= sprintf(', in consegna %s', strftime('%A %d %B %G', $shipping_date));
            $dates[] = $date_string;
        }
        else {
            foreach ($orders as $order) {
                $names[] = $order->printableName();
                $dates[] = $order->printableDates();
            }
        }

        return [implode(' / ', $names), implode(' / ', $dates)];
    }

    public function printableName()
    {
        return $this->innerCache('names', function($obj) {
            list($name, $date) = $this->computeStrings();
            $this->setInnerCache('dates', $date);
            return $name;
        });
    }

    public function printableDates()
    {
        return $this->innerCache('dates', function($obj) {
            list($name, $date) = $this->computeStrings();
            $this->setInnerCache('names', $name);
            return $date;
        });
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

    public function printableUserHeader()
    {
        $ret = $this->printableHeader();

        $tot = 0;

        foreach($this->orders as $o) {
            $b = $o->userBooking(null, false);
            if ($b != null)
                $tot += $b->total_value;
        }

        if($tot == 0)
            $ret .= '<span class="pull-right">Non hai partecipato a quest\'ordine</span>';
        else
            $ret .= '<span class="pull-right">Hai ordinato ' . printablePrice($tot) . '€</span>';

        return $ret;
    }

    public function getBookingURL()
    {
        return URL::action('BookingController@index').'#' . $this->id;
    }

    public function isActive()
    {
        foreach ($this->orders as $order) {
            if ($order->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function isRunning()
    {
        foreach ($this->orders as $order) {
            if ($order->isRunning()) {
                return true;
            }
        }

        return false;
    }

    public function getBookingsAttribute()
    {
        $ret = [];

        foreach ($this->orders as $order) {
            foreach ($order->bookings as $booking) {
                $booking->setRelation('order', $order);
                $user_id = $booking->user->id;

                if (!isset($ret[$user_id])) {
                    $ret[$user_id] = new AggregateBooking($user_id);
                }

                $ret[$user_id]->add($booking);
            }
        }

        uasort($ret, function($a, $b) {
            $a_status = $a->status;
            $b_status = $b->status;

            if ($a_status == $b_status) {
                return strcmp($a->user->printableName(), $b->user->printableName());
            }
            else {
                if ($a_status == 'pending')
                    return -1;
                if ($b_status == 'pending')
                    return 1;
                if ($a_status == 'saved')
                    return -1;
                if ($b_status == 'saved')
                    return 1;

                return -1;
            }
        });

        return $ret;
    }

    public function getLastNotifyAttribute()
    {
        return $this->innerCache('last_notify', function($obj) {
            return $obj->orders()->first()->last_notify;
        });
    }

    public function getEndAttribute()
    {
        return $this->innerCache('end', function($obj) {
            return $obj->orders->last()->end;
        });
    }

    public function getShippingAttribute()
    {
        return $this->innerCache('shipping', function($obj) {
            return $obj->orders()->min('shipping');
        });
    }

    public function bookingBy($user_id)
    {
        $ret = new AggregateBooking($user_id);

        foreach ($this->orders as $order) {
            $booking = $order->userBooking($user_id);
            $ret->add($booking);
        }

        return $ret;
    }

    public function getPermissionsProxies()
    {
        $suppliers = [];

        foreach($this->orders as $order)
            $suppliers[] = $order->supplier;

        return $suppliers;
    }
}
