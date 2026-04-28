<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Config Timezone: " . config('app.timezone') . "\n";
echo "Current Time (now()): " . now()->toDateTimeString() . "\n";
echo "Current Time (date): " . date('Y-m-d H:i:s') . "\n";
echo "Carbon Default Timezone: " . \Carbon\Carbon::now()->timezoneName . "\n";
