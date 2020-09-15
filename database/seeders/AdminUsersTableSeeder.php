<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\AdminUser::class, 3)->create()->each(function($user) {
            $user->role()->save(factory(App\Models\Role::class)->make());
        });
    }
}
