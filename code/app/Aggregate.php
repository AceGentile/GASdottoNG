<?php

namespace app;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\GASModel;
use App\AggregateBooking;

class Aggregate extends Model
{
    use GASModel;

    private $names_string = null;
    private $dates_string = null;

    public function orders()
    {
        return $this->hasMany('App\Order')->with('products')->orderBy('end', 'desc');
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

        foreach ($this->orders as $order) {
            $names[] = $order->printableName();
            $dates[] = $order->printableDates();
        }

        $this->names_string = implode(' / ', $names);
        $this->dates_string = implode(' / ', $dates);
    }

    public function printableName()
    {
        if ($this->names_string == null) {
            $this->computeStrings();
        }

        return $this->names_string;
    }

    public function printableDates()
    {
        if ($this->dates_string == null) {
            $this->computeStrings();
        }

        return $this->dates_string;
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

    public function userCan($action, $user = null)
    {
        if ($user == null) {
            $user = Auth::user();
        }

        foreach ($this->orders as $order) {
            if ($order->supplier->userCan($action, $user)) {
                return true;
            }
        }

        return false;
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

    public function getBookingsAttribute()
    {
        $ret = [];

        foreach ($this->orders as $order) {
            foreach ($order->bookings as $booking) {
                $user_id = $booking->user->id;

                if (!isset($ret[$user_id])) {
                    $ret[$user_id] = new AggregateBooking($user_id);
                }

                $ret[$user_id]->add($booking);
            }
        }

        return $ret;
    }
}
