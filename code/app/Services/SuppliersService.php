<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;
use Hash;

use App\User;
use App\Supplier;
use App\Role;

class SuppliersService extends BaseService
{
    public function list($term = '', $all = false)
    {
        $this->ensureAuth();
        $query = Supplier::orderBy('name', 'asc');

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%$term%");
            });
        }

        if ($all)
            $query->filterEnabled();

        $suppliers = $query->get();
        return $suppliers;
    }

    public function show($id)
    {
        return Supplier::withTrashed()->findOrFail($id);
    }

    public function destroy($id)
    {
        $supplier = DB::transaction(function () use ($id) {
            $supplier = $this->show($id);
            $this->ensureAuth(['supplier.modify' => $supplier]);

            if ($supplier->trashed())
                $supplier->forceDelete();
            else
                $supplier->delete();

            return $supplier;
        });

        return $supplier;
    }

    public function update($id, array $request)
    {
        $supplier = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        DB::transaction(function () use ($supplier, $id, $request) {
            $this->setIfSet($supplier, $request, 'name');
            $this->setIfSet($supplier, $request, 'business_name');
            $this->setIfSet($supplier, $request, 'taxcode');
            $this->setIfSet($supplier, $request, 'vat');
            $this->setIfSet($supplier, $request, 'description');
            $this->setIfSet($supplier, $request, 'payment_method');
            $this->setIfSet($supplier, $request, 'order_method');

            $supplier->restore();
            $supplier->save();
            $supplier->updateContacts($request);
            return $supplier;
        });

        return $supplier;
    }

    public function store(array $request)
    {
        $creator = $this->ensureAuth(['supplier.add' => 'gas']);

        if (!isset($request['payment_method']) || $request['payment_method'] == null)
            $request['payment_method'] = '';
        if (!isset($request['order_method']) || $request['order_method'] == null)
            $request['order_method'] = '';

        $supplier = new Supplier();
        $this->setIfSet($supplier, $request, 'name');
        $this->setIfSet($supplier, $request, 'business_name');
        $this->setIfSet($supplier, $request, 'taxcode');
        $this->setIfSet($supplier, $request, 'vat');
        $this->setIfSet($supplier, $request, 'description');
        $this->setIfSet($supplier, $request, 'payment_method');
        $this->setIfSet($supplier, $request, 'order_method');

        DB::transaction(function () use ($supplier, $creator) {
            $supplier->save();

            $roles = Role::havingAction('supplier.modify');
            foreach($roles as $r) {
                $creator->addRole($r, $supplier);
            }
        });

        return $supplier;
    }

    public function catalogue($id, $format)
    {
        $this->ensureAuth();
        $supplier = $this->show($id);
        $filename = sprintf('Listino %s.%s', $supplier->name, $format);

        if ($format == 'pdf') {
            $html = Theme::view('documents.cataloguepdf', ['supplier' => $supplier])->render();
            PDF::SetTitle(sprintf('Listino %s del %s', $supplier->name, date('d/m/Y')));
            PDF::AddPage();
            PDF::writeHTML($html, true, false, true, false, '');
            PDF::Output($filename, 'D');
        }
        elseif ($format == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            return Theme::view('documents.cataloguecsv', ['supplier' => $supplier]);
        }
    }

    public function plainBalance($id)
    {
        $this->ensureAuth(['movements.view' => 'gas', 'movements.admin' => 'gas']);
        $supplier = $this->show($id);
        return $supplier->current_balance_amount;
    }
}
