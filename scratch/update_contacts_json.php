<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);

$data['Add New Recipient'] = 'إضافة مستلم جديد';
$data['Add New Sender'] = 'إضافة مرسل جديد';
$data['Manage all your parcel recipients'] = 'إدارة جميع مستلمي الطرود';
$data['Manage all your parcel senders'] = 'إدارة جميع مرسلي الطرود';
$data['Search by name, phone or address...'] = 'البحث بالاسم، الهاتف أو العنوان...';
$data['Total Parcels'] = 'إجمالي الطرود';
$data['Phone Number'] = 'رقم الهاتف';
$data['Sender Name'] = 'اسم المرسل';
$data['Recipient Name'] = 'اسم المستلم';
$data['Recipient deleted successfully.'] = 'تم حذف المستلم بنجاح.';
$data['Sender deleted successfully.'] = 'تم حذف المرسل بنجاح.';
$data['Delete Recipient'] = 'حذف المستلم';
$data['Delete Sender'] = 'حذف المرسل';
$data['Are you sure you want to delete'] = 'هل أنت متأكد أنك تريد حذف';

file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated with Contact translation keys.\n";
