<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Hash;
use Theme;
use Artisan;

use App\User;
use App\Aggregate;

class CommonsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex()
    {
        $user = Auth::user();
        $user->last_login = date('Y-m-d G:i:s');
        $user->save();

        /*
            In mancanza d'altro, eseguo qui lo scheduling delle operazioni
            periodiche
        */
        Artisan::call('check:fees');
        Artisan::call('check:orders');
        Artisan::call('check:system_notices');

        $data['notifications'] = $user->notifications;

        $opened = Aggregate::getByStatus('open');
        $opened = $opened->sort(function($a, $b) {
            return strcmp($a->end, $b->end);
        });
        $data['opened'] = $opened;

        $shipping = Aggregate::whereHas('orders', function ($query) use ($user) {
            $query->where('status', 'closed')->whereHas('bookings', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', '!=', 'shipped');
            });
        })->get();

        $shipping = $shipping->sort(function($a, $b) {
            return strcmp($a->shipping, $b->shipping);
        });

        $data['shipping'] = $shipping;

        return Theme::view('pages.dashboard', $data);
    }

    public function postVerify(Request $request)
    {
        $password = $request->input('password');
        $user = $request->user();
        $test = Auth::attempt(['username' => $user->username, 'password' => $password]);
        if ($test)
            return 'ok';
        else
            return 'ko';
    }
}
