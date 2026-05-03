<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);

// Add specifically needed keys if missing or inconsistent
$data["Default"] = "افتراضي";
$data["Dispatch"] = "إرسال";
$data["Receive"] = "استلام";
$data["Action"] = "إجراء";
$data["Edit"] = "تعديل";
$data["Delete"] = "حذف";
$data["Update"] = "تحديث";
$data["Status"] = "الحالة";
$data["Statuses"] = "الحالات";

file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON deduplicated and updated.\n";
