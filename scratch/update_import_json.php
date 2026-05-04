gdi <?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);

$data['Dispatch Mode (2 sheets: IN + OUT)'] = 'وضع الإرسال (ورقتان: استلام + تسليم)';
$data['Receive Mode (1 sheet: IN)'] = 'وضع الاستلام (ورقة واحدة: استلام)';
$data['Drag & Drop your Excel file here'] = 'اسحب وأفلت ملف الإكسيل هنا';
$data['or click to browse'] = 'أو اضغط للتصفح';
$data['Browse File'] = 'تصفح الملفات';
$data['Preview Import'] = 'معاينة الاستيراد';
$data['Analyzing file...'] = 'جاري تحليل الملف...';
$data['Preparing...'] = 'جاري التحضير...';
$data['Reading Excel...'] = 'جاري قراءة الإكسيل...';
$data['Checking barcodes and contacts in the database...'] = 'التحقق من الباركودات وجهات الاتصال في قاعدة البيانات...';
$data['Importing Data...'] = 'جاري استيراد البيانات...';
$data['Writing records to database...'] = 'جاري كتابة السجلات في قاعدة البيانات...';
$data['Processing batch...'] = 'جاري معالجة الدفعة...';
$data['Completed'] = 'مكتمل';
$data['Import completed successfully'] = 'تم الاستيراد بنجاح';
$data['already in DB'] = 'موجود مسبقاً في النظام';
$data['need new contacts'] = 'يحتاج لإنشاء جهات اتصال';
$data['Exists'] = 'موجود';
$data['New'] = 'جديد';
$data['Errors Encountered'] = 'الأخطاء التي تم اكتشافها';
$data['Issue'] = 'المشكلة';

file_put_contents('d:/LaravelUI/store/lang/ar.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "JSON updated with Import translation keys.\n";
