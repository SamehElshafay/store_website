<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'admin@smartstore.com')->first();
if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    echo "Password for admin@smartstore.com has been set to: password\n";
} else {
    echo "User not found.\n";
}
