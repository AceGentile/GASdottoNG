<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\User;
use App\Aggregate;

class BookingUserController extends BookingHandler
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $aggregate_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if ($aggregate->userCan('supplier.shippings') == false) {
            abort(503);
        }

        return view('booking.list', ['aggregate' => $aggregate]);
    }

    public function show(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if (Auth::user()->id != $user_id && $aggregate->userCan('supplier.shippings') == false) {
            abort(503);
        }

        $user = User::findOrFail($user_id);

        return view('booking.edit', ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        return $this->bookingUpdate($request, $aggregate_id, $user_id, false);
    }

    public function destroy($aggregate_id, $user_id)
    {
        DB::beginTransaction();

        $aggregate = Aggregate::findOrFail($aggregate_id);
        if (Auth::user()->id != $user_id && $aggregate->userCan('supplier.shippings') == false) {
            abort(503);
        }

        foreach ($aggregate->orders as $order) {
            $booking = $order->userBooking($user_id);
            $booking->delete();
        }

        return $this->successResponse();
    }
}
