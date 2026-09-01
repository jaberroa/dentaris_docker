<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Usuarios en la base de datos:\n";
$users = App\Models\User::all(['id', 'name', 'email', 'is_active']);

foreach ($users as $user) {
    echo $user->id . ' - ' . $user->name . ' - ' . $user->email . ' - Activo: ' . ($user->is_active ? 'Si' : 'No') . "\n";
}

echo "\nTotal usuarios: " . $users->count() . "\n";





