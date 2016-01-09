<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Gas;
use App\User;
use App\Permission;
use App\Category;
use App\Measure;
use App\Notification;

class DatabaseSeeder extends Seeder
{
        public function run()
        {
                Model::unguard();

                DB::table('users')->delete();
                DB::table('password_resets')->delete();
                DB::table('gas')->delete();
                DB::table('suppliers')->delete();
                DB::table('products')->delete();
                DB::table('orders')->delete();
                DB::table('aggregates')->delete();
                DB::table('variants')->delete();
                DB::table('variant_values')->delete();
                DB::table('categories')->delete();
                DB::table('measures')->delete();
                DB::table('deliveries')->delete();
                DB::table('notifications')->delete();
                DB::table('bookings')->delete();
                DB::table('booked_products')->delete();
                DB::table('booked_product_variants')->delete();
                DB::table('movements')->delete();
                DB::table('contacts')->delete();
                DB::table('comments')->delete();
                DB::table('filer_local_files')->delete();
                DB::table('filer_attachments')->delete();
                DB::table('filer_urls')->delete();

                $gas = Gas::create([
                        'id' => str_slug('Senza Nome'),
			'name' => 'Senza Nome',
                        'current_bank_balance' => 0,
                        'current_cash_balance' => 0,
                        'current_orders_balance' => 0,
                        'current_deposit_balance' => 0
		]);

                $admin = User::create([
                        'id' => str_slug('Amministratore Globale'),
                        'gas_id' => $gas->id,
                        'member_since' => date('Y-m-d', time()),
                        'username' => 'root',
			'name' => 'Amministratore',
                        'surname' => 'Globale',
			'password' => Hash::make('root')
		]);

                $permissions = Permission::allPermissions();
                foreach($permissions['Gas'] as $action => $desc) {
                        $perm = new Permission(['user_id' => $admin->id, 'action' => $action]);
                        $gas->permissions()->save($perm);
                }

                $categories = [
                        'Non Specificato',
                        'Frutta',
                        'Verdura',
                        'Cosmesi',
                        'Bevande'
                ];

                foreach ($categories as $cat) {
                        Category::create([
                                'id' => str_slug($cat),
                                'name' => $cat
        		]);
                }

                $measures = [
                        'Non Specificato',
                        'Chili',
                        'Litri',
                        'Pezzi'
                ];

                foreach ($measures as $name) {
                        Measure::create([
                                'id' => str_slug($name),
                                'name' => $name
        		]);
                }

                $notification = Notification::create([
                        'creator_id' => $admin->id,
                        'content' => 'Benvenuto in GASdotto!',
                        'mailed' => false
                ]);

                $notification->users()->attach($admin->id);

                Model::reguard();
        }
}
