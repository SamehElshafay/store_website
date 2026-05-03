<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);
$data['dispatch'] = 'إرسال';
$data['receive'] = 'استلام';
$data['default'] = 'افتراضي';
$data['Dispatch'] = 'إرسال';
$data['Receive'] = 'استلام';
$data['Default'] = 'افتراضي';
file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated.\n";
