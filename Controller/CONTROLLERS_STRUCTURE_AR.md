# تنظيم ملفات Controller

تم إلغاء ملف `test.php` نهائياً لأن كل الأكشنات كانت متجمعة فيه بشكل مؤقت.

الآن كل جزء له Controller مسؤول عنه مباشرة:

- `AuthController.php`: تسجيل حساب، تسجيل دخول، تسجيل خروج، نسيت كلمة المرور.
- `AccountController.php`: تعديل بيانات الحساب، العنوان، كلمة المرور.
- `AdminController.php`: المنتجات، الكوبونات، الطلبات، المستخدمين.
- `CartController.php`: إضافة للسلة، تعديل الكمية، حذف من السلة.
- `WishlistController.php`: إضافة/حذف من المفضلة.
- `OrderController.php`: إنشاء الطلب، إلغاء الطلب.
- `PaymentController.php`: الدفع.
- `ReviewController.php`: إضافة مراجعة.
- `BaseController.php`: الاتصال بالداتا بيز، السيشن، الحماية، والتحويل بين الصفحات.

## مثال على الربط الجديد
الفورم يذهب مباشرة إلى الكنترولر المناسب:

```php
action="../Controller/AuthController.php"
action="../Controller/CartController.php"
action="../Controller/AdminController.php"
```

هذا يجعل المشروع أوضح وأسهل في الشرح والتسليم، وكل ملف مسؤول عن جزء محدد فقط.
