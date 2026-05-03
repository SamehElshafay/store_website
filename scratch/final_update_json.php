<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);

// Add/Update keys from Status Management page
$data['Status Management'] = 'إدارة الحالات';
$data['Drag and drop statuses to reorder your workflow'] = 'اسحب وأفلت الحالات لإعادة ترتيب سير العمل';
$data['Add New Status'] = 'إضافة حالة جديدة';
$data['Default'] = 'افتراضي';
$data['Action'] = 'إجراء';
$data['Dispatch'] = 'إرسال';
$data['Receive'] = 'استلام';
$data['Edit'] = 'تعديل';
$data['Delete'] = 'حذف';
$data['Edit Status'] = 'تعديل الحالة';
$data['Create New Status'] = 'إنشاء حالة جديدة';
$data['Name (Arabic)'] = 'الاسم (بالعربية)';
$data['Name (English)'] = 'الاسم (بالإنجليزية)';
$data['Status Color'] = 'لون الحالة';
$data['Select Status Icon'] = 'اختر أيقونة الحالة';
$data['Dialog Modal Type'] = 'نوع نافذة الحوار';
$data['Receive Modal (Incoming)'] = 'نافذة الاستلام (الوارد)';
$data['Dispatch Modal (Outgoing)'] = 'نافذة الإرسال (الصادر)';
$data['Save Status'] = 'حفظ الحالة';
$data['Update Status'] = 'تحديث الحالة';
$data['Are you sure you want to delete status'] = 'هل أنت متأكد أنك تريد حذف الحالة';

// From Settings page
$data['Settings'] = 'الإعدادات';
$data['Manage your application preferences and system configuration.'] = 'إدارة تفضيلات التطبيق وإعدادات النظام.';
$data['Default Contacts'] = 'جهات الاتصال الافتراضية';
$data['Set default senders and recipients to speed up parcel registration.'] = 'حدد المرسلين والمستلمين الافتراضيين لتسريع تسجيل الطرود.';
$data['Default Sender (Source)'] = 'المرسل الافتراضي (المصدر)';
$data['Default Recipient (Destination)'] = 'المستلم الافتراضي (الوجهة)';
$data['Save Default Settings'] = 'حفظ الإعدادات الافتراضية';
$data['Parcel Statuses'] = 'حالات الطرود';
$data['Configure parcel workflow, colors, and modal types.'] = 'تكوين سير العمل، الألوان، وأنواع النوافذ.';
$data['Manage Statuses'] = 'إدارة الحالات';
$data['Language & Region'] = 'اللغة والمنطقة';
$data['Choose your preferred language and timezone.'] = 'اختر لغتك المفضلة والمنطقة الزمنية.';
$data['System Cleanup'] = 'تنظيف النظام';
$data['Delete All Parcels'] = 'حذف جميع الطرود';
$data['Are you absolutely sure?'] = 'هل أنت متأكد تماماً؟';
$data['This action will permanently delete ALL parcels from the system. This process cannot be undone.'] = 'هذا الإجراء سيقوم بحذف جميع الطرود من النظام نهائياً. لا يمكن التراجع عن هذه العملية.';
$data['Yes, Delete Everything'] = 'نعم، امسح كل شيء';
$data['No, Keep Data'] = 'لا، احتفظ بالبيانات';

// Add lowercase variants
$data['dispatch'] = 'إرسال';
$data['receive'] = 'استلام';
$data['default'] = 'افتراضي';

file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated comprehensively.\n";
