<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);

$data['Master Excel Import'] = 'استيراد إكسل شامل';
$data['Import parcels from Excel with dynamic status recognition. New statuses will be created automatically.'] = 'استيراد الطرود من إكسل مع التعرف التلقائي على الحالات. سيتم إنشاء الحالات الجديدة تلقائياً.';
$data['Open Importer'] = 'فتح المستورد';
$data['Master Import from Excel'] = 'استيراد شامل من إكسل';
$data['Dynamic Status Creation enabled. New statuses will be created automatically.'] = 'تم تفعيل إنشاء الحالات تلقائياً. سيتم إنشاء أي حالة غير موجودة في النظام.';
$data['Upload Master Excel File'] = 'رفع ملف الإكسيل الشامل';
$data['Status in File'] = 'الحالة في الملف';
$data['Master Import Complete'] = 'اكتمل الاستيراد الشامل';

file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated with Master Import keys.\n";
