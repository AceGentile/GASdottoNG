<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Theme;

use App\Services\SuppliersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class SuppliersController extends BackedController
{
    public function __construct(SuppliersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'endpoint' => 'suppliers',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $suppliers = $this->service->list('', true);
            return Theme::view('pages.suppliers', ['suppliers' => $suppliers]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $supplier = $this->service->show($id);

            if ($request->user()->can('supplier.modify', $supplier))
                return Theme::view('supplier.edit', ['supplier' => $supplier]);
            else
                return Theme::view('supplier.show', ['supplier' => $supplier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function catalogue(Request $request, $id, $format)
    {
        try {
            return $this->service->catalogue($id, $format);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function plainBalance(Request $request, $id)
    {
        try {
            return $this->service->plainBalance($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }
}
