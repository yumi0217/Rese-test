<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // .env から上書きできるように（無ければデフォルト）
        $name  = env('ADMIN_NAME',  'Admin');
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $pass  = env('ADMIN_PASSWORD', 'password123'); // 初回後に必ず変更！

        // 既に同メールがあれば更新、無ければ新規
        $user = User::firstOrNew(['email' => $email]);

        $user->name              = $name;
        $user->password          = Hash::make($pass);
        $user->role              = 'admin';
        $user->email_verified_at = now();

        $user->save();

        $this->command?->info("Admin user ready: {$email} (role=admin)");
    }
}
