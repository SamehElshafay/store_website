<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);
$data['English'] = 'الإنجليزية';
$data['Arabic'] = 'العربية';
file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated with English/Arabic names.\n";
