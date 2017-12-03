<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use AutoMail\AutoMail;

use Auth;
use DB;
use Theme;

use App\Role;
use App\Gas;

class GasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getLogo']]);

        $this->commonInit([
            'reference_class' => 'App\\Gas'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        return redirect(url('gas/' . $user->gas->id . '/edit'));
    }

    public function getLogo($id)
    {
        $gas = Gas::findOrFail($id);
        if (!empty($gas->logo)) {
            $path = gas_storage_path($gas->logo);
            if (file_exists($path)) {
                return response()->download($path);
            }
            else {
                $gas->logo = '';
                $gas->save();
            }
        }

        return '';
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gas = Gas::findOrFail($id);
        if ($user->can('gas.config', $gas) == false) {
            abort(503);
        }

        return Theme::view('pages.gas', ['gas' => $gas]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $gas = Gas::findOrFail($id);
        $group = $request->input('group');

        switch($group) {
            case 'accounting':
                if ($user->can('movements.admin', $gas) == false) {
                    return $this->errorResponse('Non autorizzato');
                }

                $gas->setConfig('year_closing', decodeDateMonth($request->input('year_closing')));
                $gas->setConfig('annual_fee_amount', $request->input('annual_fee_amount', 0));
                $gas->setConfig('deposit_amount', $request->input('deposit_amount', 0));
                break;

            case 'general':
                if ($user->can('gas.config', $gas) == false) {
                    return $this->errorResponse('Non autorizzato');
                }

                $gas->name = $request->input('name');
                $gas->email = $request->input('email');
                $gas->message = $request->input('message');
                $this->handleDirectFileUpload($request, 'logo', $gas);
                $gas->setConfig('restricted', $request->has('restricted') ? '1' : '0');
                break;

            case 'email':
                if ($user->can('gas.config', $gas) == false) {
                    return $this->errorResponse('Non autorizzato');
                }

                $mailconf = $gas->getConfig('mail_conf');
                if ($mailconf == '') {
                    $old_password = '';
                }
                else {
                    $mail = json_decode($mailconf);
                    $old_password = $mail->password;
                }

                $mail = (object) [
                    'driver' => $request->input('maildriver'),
                    'username' => $request->input('mailusername'),
                    'password' => $request->input('mailpassword') == '' ? $old_password : $request->input('mailpassword'),
                    'host' => $request->input('mailserver'),
                    'port' => $request->input('mailport'),
                    'address' => $request->input('mailaddress'),
                    'encryption' => $request->input('mailssl'),
                ];

                $gas->setConfig('mail_conf', $mail);
                break;

            case 'banking':
                if ($user->can('gas.config', $gas) == false) {
                    return $this->errorResponse('Non autorizzato');
                }

                $rid_info = (object) [
                    'iban' => $request->input('rid->iban'),
                    'id' => $request->input('rid->id'),
                ];

                $gas->setConfig('rid', $rid_info);
                break;

            case 'orders':
                $gas->setConfig('fast_shipping_enabled', $request->has('fast_shipping_enabled') ? '1' : '0');
                break;
        }

        $gas->save();
        return $this->successResponse();
    }

    public function configureMail(Request $request)
    {
        $email = $request->input('email');

        try {
            $conf = AutoMail::discover($email);
        }
        catch(\Exception $e) {
            $conf = null;
        }

        $ret = [];
        if ($conf != null)
            $ret = $conf['outgoing'][0];

        return response()->json($ret);
    }
}
