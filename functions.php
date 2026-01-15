<?php
require_once 'config.php';
require_once 'user_handlers.php';
// النصوص العربية
// النصوص العربية
function getLang($key) {
    $texts = [
        'welcome' => "👋 *أهلاً بك عزيزي المستخدم*\n*في بوت خدمات الرشق الموثوقه*\n\n*اختر طلبك من القائمة:*\n*رصيدك الحالي:* {balance}",
        'admin_buttons' => [
            'manage_services' => "🛠 إدارة الخدمات",
            'manage_categories' => "📁 إدارة الأقسام", 
            'manage_balance' => "💰 إدارة الرصيد",
            'list_services' => "📋 قائمة الخدمات",
            'generate_card' => "🎟 إنشاء كرت شحن",
            'set_welcome' => "👋 تعيين ترحيب",
'select_service' => "☑️ *يرجى اختيار الخدمه المناسبه لك 👇*\n\n*ملاحظة: يتم عرض الخدمات بالتنسيق التالي: اسم الخدمه > السعر.*",
            'manage_currencies' => "💱 إدارة العملات",
            'manage_bans' => "🚫 إدارة الحظر",
            'statistics' => "📊 الإحصائيات",
            'set_instructions' => "📖 تعليمات",
            'set_recharge_text' => "💳 تعيين شحن الرصيد",
            'transfer_settings' => "🔄 إعدادات التحويل",
            'referral_settings' => "📢 إعدادات الإحالة",
            'add_service_button' => "➕ إضافة خدمة",
            'edit_delete_service_button' => "📝 تعديل/حذف خدمة",
            'add_category_button' => "➕ إضافة قسم",
            'delete_category_button' => "🗑 حذف قسم",
            'add_balance_button' => "➕ إضافة رصيد",
            'subtract_balance_button' => "➖ خصم رصيد",
            'add_currency_button' => "➕ إضافة عملة",
            'edit_currency_button' => "✏️ تعديل عملة", 
            'delete_currency_button' => "🗑 حذف عملة",
            'manage_sites' => "🌐 إعدادات المواقع",
            'manage_channels' => "📢 إعدادات القنوات",
            'support_messages' => "💬 رسائل الدعم",
            'forced_subscription' => "🔔 الاشتراك الإجباري",
            'manage_admins' => "👑 إدارة الأدمن"
        ],
        'user_buttons' => [
            'list_services' => "🛍️ قائمة الخدمات",
            'recharge' => "💰 شحن الرصيد", 
            'redeem_card' => "💳 شحن كرت",
            'change_currency' => "💱 تغيير العملة",
            'statistics' => "📊 الإحصائيات",
            'transfer_balance' => "🔄 تحويل رصيد",
            'referral' => "👥 رابط الإحالة",
            'instructions' => "❓ التعليمات",
            'bot_channel' => "📢 قناه البوت",
            'orders_channel' => "🛒 قناه الطلبات", 
            'support' => "👨‍💻 الدعم الفني"
        ],
        'back_button' => "↪️ رجوع",
        'no_services' => "❌ *لا توجد خدمات متاحة حالياً.*",
        'select_category' => "*قم بزيادة متابعين حساباتك من هنا 🙋🏻‍♂.

🤖︙إختر مايناسبك من الأسفل ، كل قسم يحتوي على عدة خدمات ( متابعين 👥 - مشاهدات 👁 - لايكات 👍 ) وخدمات أخرى 🔥.*",
        'select_service' => "*✅︙جميع الخدمات المتوفره في هذا القسم 👇.

☑️︙يرجى اختيار الخدمه المناسبه لك 👇
⚠️︙ملاحظة يتم عرض الخدمات بالتنسيق التالي : اسم الخدمه > السعر.*",
        'recharge_text' => "💳 *لشحن الرصيد تواصل معنا عبر:* @haamadh
        
        💵 *السعر: كل 1$ = 1$ بدون عمولة.*",
        'send_card_code' => "🎟 *أرسل كود كرت الشحن:*",
        'invalid_card' => "❌ *كود غير صالح أو تم استخدامه مسبقاً.*",
        'card_redeemed' => "✅ *تم شحن رصيدك بـ *",
        'current_balance' => "\n💰 *رصيدك الآن:* ",
        'service_not_found' => "❌ *الخدمة غير موجودة.*",
        'send_link' => "*خدمة:* *%s*\n*السعر:* %s\n*الحد الأدنى:* %s\n*الحد الأقصى:* %s\n%s\n🔗 *أرسل رابط الطلب:*",
        'send_quantity' => "🔢 *أرسل الكمية المطلوبة:*",
        'invalid_quantity' => "❌ *الكمية يجب أن تكون بين %s و %s.*",
        'insufficient_balance' => "❌ *رصيدك الحالي %s غير كافٍ. السعر المطلوب: %s*",
        'confirm_order' => "✅ *تأكيد الطلب:*\n\n🔹 *الخدمة:* %s (%s)\n🔗 *الرابط:* %s\n📦 *الكمية:* %s\n💰 *السعر الكلي:* %s",
        'confirm_button' => "✅ تنفيذ الطلب",
        'cancel_button' => "❌ إلغاء",
        'no_order_to_confirm' => "❌ *لا يوجد طلب لتأكيده.*",
        'order_placed' => "🎉 *تم تنفيذ الطلب!*\n🧾 *رقم الطلب:* %s\n💰 *رصيدك:* %s",
        'order_failed' => "❌ *فشل تنفيذ الطلب:*\n%s",
        'order_canceled' => "❌ *تم إلغاء الطلب.*",
        'manage_services' => "🛠 *إدارة الخدمات:*",
        'add_service_button' => "➕ إضافة خدمة",
        'edit_delete_service_button' => "📝 تعديل/حذف خدمة",
        'send_service_category' => "📁 *اختر القسم للخدمة:*",
        'send_smm_id' => "✅ *أدخل رقم الخدمة في موقع SMM:*",
        'send_service_name' => "✅ *أدخل اسم الخدمة:*",
        'send_service_price' => "💰 *أدخل سعر الخدمة بالدولار لكل 1000 (بدون علامة $):*\n\n*مثال: إذا كان سعر 1000 خدمة = 2$ ادخل 2*",
        'send_service_min' => "✅ *أدخل الحد الأدنى للكمية:*",
        'send_service_max' => "✅ *أدخل الحد الأقصى للكمية:*",
        'send_service_desc' => "✅ *أدخل وصف الخدمة (أرسل 'لا' لتخطي هذه الخطوة):*",
        'send_service_link_format' => "🔗 *اختر صيغة الرابط المطلوبة للخدمة:*\n1. *@username (مثل: haamadh)*\n2. *رابط كامل (مثل: http://link.com)*",
        'service_added' => "✅ *تم إضافة الخدمة بنجاح!*",
        'no_services_to_delete' => "🚫 *لا توجد خدمات لحذفها.*",
        'select_service_to_delete' => "🗑 *اختر الخدمة التي تريد حذفها:*",
        'service_deleted' => "✅ *تم حذف خدمة* *%s* *بنجاح.*",
        'service_not_found_delete' => "❌ *الخدمة غير موجودة.*",
        'manage_categories' => "📁 *إدارة الأقسام:*",
        'add_category_button' => "➕ إضافة قسم",
        'delete_category_button' => "🗑 حذف قسم",
        'send_category_name' => "📁 *أدخل اسم القسم الجديد:*",
        'category_added' => "✅ *تم إضافة القسم بنجاح!*",
        'no_categories' => "🚫 *لا توجد أقسام حالياً.*",
        'select_category_to_delete' => "🗑 *اختر القسم الذي تريد حذفه:*",
        'category_deleted' => "✅ *تم حذف القسم* *%s* *بنجاح.*",
        'manage_balance' => "🛠 *إدارة رصيد المستخدمين:*",
        'add_balance_button' => "➕ إضافة رصيد",
        'subtract_balance_button' => "➖ خصم رصيد",
        'send_user_id_add' => "➕ *أرسل معرف المستخدم (ID) الذي تريد إضافة رصيد له:*",
        'send_amount_add' => "✅ *أدخل مبلغ الرصيد الذي تريد إضافته:*",
        'balance_added' => "✅ *تم إضافة %s$ لرصيد المستخدم %s بنجاح!*\n*رصيده الجديد:* %s",
        'send_user_id_subtract' => "➖ *أرسل معرف المستخدم (ID) الذي تريد خصم رصيد منه:*",
        'send_amount_subtract' => "✅ *أدخل مبلغ الرصيد الذي تريد خصمه:*",
        'balance_subtracted' => "✅ *تم خصم %s$ من رصيد المستخدم %s بنجاح!*\n*رصيده الجديد:* %s",
        'send_card_amount' => "💲 *أرسل قيمة كرت الشحن:*",
        'card_created' => "✅ *تم إنشاء الكرت!*\n🔐 *الكود:* `%s`\n💵 *القيمة:* %s$",
      // في دالة getLang، تحديث نص set_welcome_text:
'set_welcome_text' => "👋 *أرسل نص رسالة الترحيب الجديدة:*  

*يمكنك استخدام المتغيرات التالية:*  

1 - `{balance}` *لعرض رصيد المستخدم*  
2 - `{user_id}` *لعرض آيدي المستخدم*  
3 - `{username}` *لعرض اسم المستخدم*  
4 - `{account_number}` *لعرض رقم حساب المستخدم*  
5 - `{spent_balance}` *لعرض الرصيد المصروف*  
6 - `{user_currency}` *لعرض نوع عملة المستخدم*  
7 - `{user_level}` *لعرض مستوى حساب المستخدم*  
8 - `{user_link}` *لعرض رابط حساب المستخدم*",
        'welcome_updated' => "✅ *تم تحديث رسالة الترحيب بنجاح!*",
        'manage_currencies' => "💱 *إدارة أسعار العملات:*",
        'add_currency_button' => "➕ إضافة عملة",
        'edit_currency_button' => "✏️ تعديل عملة", 
        'delete_currency_button' => "🗑 حذف عملة",
        'send_currency_code' => "*✅ - أدخل اسم العملة 

( مثال: ريال سعودي🇸🇦, دولار🇺🇸, جنية مصري 🇪🇬):*",
        'send_currency_rate' => "*💰 أدخل سعر العملة مقابل الدولار:

🌝 - مثال الريال السعودي سعرها مقابل الدولار ( 3.5 )*",
        'send_currency_symbol' => "*🔣 أدخل رمز العملة (مثل: USD🇺🇸, ر.س🇸🇦,ر.ي🇾🇪):*",
        'currency_added' => "✅ *تم إضافة العملة بنجاح!*",
        'currency_updated' => "✅ *تم تحديث سعر العملة بنجاح!*",
        'currency_deleted' => "✅ *تم حذف العملة بنجاح!*",
        'change_currency' => "💲 *اختر عملتك:*",
        'currency_changed' => "✅ *تم تغيير العملة بنجاح إلى *",
        'manage_bans_button' => "🚫 *حظر/فك حظر*",
        'send_user_id_ban' => "🚫 *أرسل معرف المستخدم (ID) الذي تريد حظره:*",
        'send_user_id_unban' => "✅ *أرسل معرف المستخدم (ID) الذي تريد فك حظره:*",
        'user_banned' => "🚫 *تم حظر المستخدم %s بنجاح.*",
        'user_unbanned' => "✅ *تم فك حظر المستخدم %s بنجاح.*",
        'user_already_banned' => "⚠️ *المستخدم %s محظور بالفعل.*",
        'user_not_banned' => "⚠️ *المستخدم %s غير محظور.*",
        'you_are_banned' => "🚫 *أنت محظور من استخدام هذا البوت. يرجى التواصل مع المدير.*",
        'link_format_1' => "اسم المستخدم (@)",
        'link_format_2' => "رابط كامل (http)",
        'invalid_link_format' => "❌ *الرابط الذي أرسلته لا يطابق الصيغة المطلوبة. يرجى إرسال رابط صحيح.*",
        'statistics_title' => "🛍︙*مشترياتك وتفاصيل حسابك في البوت 🔰*",
        'instructions_title' => "📖 *التعليمات*",
        'transfer_balance' => "🔄 *تحويل الرصيد*",
        'send_transfer_user_id' => "👤 *أرسل معرف المستخدم (ID) الذي تريد تحويل الرصيد له:*",
        'send_transfer_amount' => "💰 *أرسل المبلغ الذي تريد تحويله:*",
        'transfer_success' => "✅ *تم تحويل %s$ إلى المستخدم %s*\n💸 *عمولة التحويل:* %s$\n💰 *رصيدك الآن:* %s",
        'transfer_insufficient_balance' => "❌ *رصيدك غير كافي للتحويل*",
        'transfer_invalid_user' => "❌ *معرف المستخدم غير صحيح*",
        'referral_title' => "📢 *رابط الإحالة*",
        'referral_text' => "🔗 *رابط الإحالة الخاص بك:*\n`https://t.me/yourbot?start=ref_%s`\n\n💰 *ستحصل على %s$ لكل مستخدم جديد يسجل عبر رابطك!*",
        'referral_bonus_received' => "🎉 *حصلت على مكافأة إحالة بقيمة %s$!*",
        'math_problem' => "🔢 *قم بحل المسألة التالية:*\n%s\n\n*أرسل الإجابة:*",
        'math_correct' => "✅ *إجابة صحيحة! تم تفعيل حسابك.*",
        'math_incorrect' => "❌ *إجابة خاطئة، حاول مرة أخرى:*",
        'set_instructions_text' => "📖 *أرسل نص التعليمات الجديدة:*",
        'instructions_updated' => "✅ *تم تحديث التعليمات بنجاح!*",
        'set_recharge_text_msg' => "💳 *أرسل نص رسالة شحن الرصيد الجديدة:*",
        'recharge_text_updated' => "✅ *تم تحديث رسالة شحن الرصيد بنجاح!*",
        'order_confirmed' => "✅ *تم تنفيذ الطلب بنجاح!*\n\n🔹 *الخدمة:* %s\n🔗 *الرابط:* %s\n📦 *الكمية:* %s\n💰 *السعر الكلي:* %s\n🧾 *رقم الطلب:* %s\n\n*سيتم البدء في التنفيذ قريباً*",
        'set_transfer_fee' => "🔄 *أدخل نسبة عمولة التحويل (بدون %):*",
        'transfer_fee_updated' => "✅ *تم تحديث عمولة التحويل إلى %s%%*",
        'set_referral_bonus' => "📢 *أدخل مكافأة الإحالة ($):*",
        'referral_bonus_updated' => "✅ *تم تحديث مكافأة الإحالة إلى %s$*",
        
        // نصوص التحقق الرياضي والإحالة الجديدة
        'math_verification_required' => "🔢 *مرحبا بك!*\n\n*لتفعيل حسابك والبدء في استخدام البوت، يرجى حل المسألة الرياضية التالية:*\n\n%s\n\n*أرسل الإجابة الآن:*",
        'start_using_bot' => "🚀 بدء استخدام البوت",
        'verification_completed' => "🎉 *تم تفعيل حسابك بنجاح!*\n\n*يمكنك الآن استخدام جميع ميزات البوت.*",
        'referral_success' => "✅ *تم تسجيل الإحالة بنجاح!*\n\n*لقد انضم مستخدم جديد عبر رابطك.*",
        'welcome_after_verification' => "👋 *أهلاً وسهلاً بك!*\n\n*تم تفعيل حسابك بنجاح.*\n*رصيدك الحالي:* {balance}\n\n*اختر من القائمة:*",
        'referral_math_title' => "🎯 *نظام الإحالة*\n\n*لتفعيل حسابك والحصول على المكافأة، يرجى حل المسألة الرياضية التالية:*\n\n%s\n\n*اختر الإجابة الصحيحة:*",
        'referral_welcome' => "🎉 *أهلاً بك في نظام الإحالة!*\n\n*تم تفعيل حسابك بنجاح وتم منح المكافأة لصاحب رابط الإحالة.*\n\n💰 *رصيدك الحالي:* {balance}\n\n*اختر من القائمة:*",
        
        // نصوص الإشعارات الجديدة
        'new_user_notification' => "☆ *تم دخول شخص جديد إلى البوت الخاص بك 👾*\n────────────────────\n*• معلومات العضو الجديد.*\n\n*• الاسم :* {name}\n*• معرف :* {username}\n*• الايدي :* `{user_id}`\n────────────────────\n*• عدد الأعضاء الكلي :* {total_users}",
        'card_recharge_notification' => "*تم شحن كرت جديد  🎟*\n────────────────────\n*• المستخدم:* {user_id}\n*• الكرت:* `{card_code}`\n*• المبلغ:* {amount}$ دولار 💰",
        'support_notification' => "📩 *رسالة دعم جديدة*\n────────────────────\n*👤 من:* {user_id}\n*💬 الرسالة:* {message}\n────────────────────\n*يمكنك الرد على هذه الرسالة بالرد عليها مباشرة*",
        
        // نصوص المواقع والقنوات
        'manage_sites_text' => "🌐 *إعدادات المواقع*\n\n*الموقع 1 الحالي:* {site1}\n*الموقع 2 الحالي:* {site2}\n*الموقع 3 الحالي:* {site3}\n\n*مفتاح ApiKey1:* {key1}\n*مفتاح ApiKey2:* {key2}\n*مفتاح ApiKey3:* {key3}",
        'manage_channels_text' => "📢 *إعدادات القنوات*\n\n*قناة البوت:* {main_channel}\n*قناة الطلبات:* {orders_channel}\n*قناة الدعم:* {support_channel}",
        'select_site_for_service' => "🌐 *اختر الموقع الذي تريد إضافة الخدمة منه:*",
        
        // نصوص الاشتراك الإجباري وإدارة الأدمن
        'forced_subscription_title' => "🔔 *إدارة الاشتراك الإجباري*",
        'manage_admins_title' => "👑 *إدارة الأدمن*",
        'add_channel_button' => "➕ إضافة قناة",
        'delete_channel_button' => "➖ حذف قناة",
        'view_channels_button' => "📋 عرض القنوات",
        'add_admin_button' => "➕ إضافة أدمن",
        'remove_admin_button' => "➖ حذف أدمن",
        'view_admins_button' => "📋 عرض الأدمن",
        'subscription_required' => "*♻️︙عذراً عزيزي، يجب عليك الاشتراك في القنوات التالية لاستخدام البوت:

🎬︙إشترك اولاً ثم اضغط زر ✅تحقق من الاشتراك*",
        'check_subscription_button' => "✅ تحقق من الاشتراك",
        'channel_added_success' => "✅ تم إضافة القناة بنجاح",
        'channel_deleted_success' => "✅ تم حذف القناة بنجاح",
        'admin_added_success' => "✅ تم إضافة الأدمن بنجاح",
        'admin_removed_success' => "✅ تم حذف الأدمن بنجاح",
        'admin_already_exists' => "❌ هذا المستخدم مسجل بالفعل كأدمن",
        'cannot_remove_main_admin' => "❌ لا يمكن حذف الأدمن الأساسي"
    ];
    
    return $texts[$key] ?? $key;
}

// الدوال الأساسية
function bot($method, $data) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/$method";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $result ? json_decode($result, true) : false;
}

function handleApiError($result, $method) {
    if (!$result || !$result['ok']) {
        error_log("API Error in $method: " . ($result['description'] ?? 'Unknown error'));
        return false;
    }
    return true;
}

function sendMessage($chat_id, $text, $keyboard = null, $options = []) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $options['parse_mode'] ?? 'Markdown',
        'disable_web_page_preview' => $options['disable_web_page_preview'] ?? false
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    }
    
    return bot('sendMessage', $data);
}

function editMessage($chat_id, $message_id, $text, $buttons = null) {
    $data = [
        'chat_id' => $chat_id, 
        'message_id' => $message_id, 
        'text' => $text, 
        'parse_mode' => 'markdown'
    ];
    if ($buttons) {
        $data['reply_markup'] = ['inline_keyboard' => $buttons];
    }
    $result = bot("editMessageText", $data);
    return handleApiError($result, "editMessage");
}

function answerCallback($callback_id, $text = null, $show_alert = false) {
    $data = ['callback_query_id' => $callback_id];
    if ($text) {
        $data['text'] = $text;
        $data['show_alert'] = $show_alert;
    }
    $result = bot("answerCallbackQuery", $data);
    return handleApiError($result, "answerCallbackQuery");
}

function smmRequest($params, $site_id = 1) {
    global $smm_sites;
    
    if (!isset($smm_sites[$site_id]) || !$smm_sites[$site_id]['enabled']) {
        error_log("SMM Site $site_id is not enabled or doesn't exist");
        return ['error' => 'الموقع غير مفعل أو غير موجود'];
    }
    
    $site = $smm_sites[$site_id];
    $params['key'] = $site['api_key'];
    
    error_log("SMM API Request to: " . $site['url']);
    error_log("SMM API Params: " . json_encode($params));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $site['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    error_log("SMM API Response - HTTP Code: $http_code");
    error_log("SMM API Response: " . $res);
    if ($error) {
        error_log("SMM API Error: " . $error);
    }
    
    if (!$res) {
        return ['error' => 'فشل في الاتصال بالموقع'];
    }
    
    $decoded = json_decode($res, true);
    if (!$decoded) {
        return ['error' => 'استجابة غير صالحة من الموقع'];
    }
    
    return $decoded;
}
// إرسال إشعار للمالك
function sendAdminNotification($message) {
    sendMessage(ADMIN_ID, $message);
}

// إشعار دخول عضو جديد
function sendNewUserNotification($user_id, $username, $first_name) {
    global $settings, $users;
    
    if ($settings['new_user_notifications'] ?? true) {
        $total_users = count($users);
        
        // هروب النصوص للMarkdown
        $name = escapeMarkdown($first_name ?: 'غير معروف');
        $username_display = $username ? "@" . escapeMarkdown($username) : 'لا يوجد';
        $user_id_escaped = escapeMarkdown($user_id);
        $total_users_escaped = escapeMarkdown($total_users);
        
        $message = "☆ *تم دخول شخص جديد إلى البوت الخاص بك 👾*\n────────────────────\n*• معلومات العضو الجديد.*\n\n*• الاسم :* {$name}\n*• معرف :* {$username_display}\n*• الايدي :* `{$user_id_escaped}`\n────────────────────\n*• عدد الأعضاء الكلي :* {$total_users_escaped}";
        
        sendAdminNotification($message);
    }
}

// إشعار شحن كرت
function sendCardRechargeNotification($user_id, $card_code, $amount) {
    global $settings;
    
    if ($settings['card_recharge_notifications'] ?? true) {
        $message = str_replace(
            ['{user_id}', '{card_code}', '{amount}'],
            [$user_id, $card_code, $amount],
            getLang('card_recharge_notification')
        );
        
        sendAdminNotification($message);
    }
}

// إشعار رسالة دعم
function sendSupportNotification($user_id, $message_text) {
    $message = str_replace(
        ['{user_id}', '{message}'],
        [$user_id, $message_text],
        getLang('support_notification')
    );
    
    sendAdminNotification($message);
}

// دالة مساعدة لعرض النصوص بشكل جميل
function formatText($text) {
    // إضافة تنسيق للنصوص لجعلها أكثر جاذبية
    $lines = explode("\n", $text);
    $formatted = "";
    
    foreach ($lines as $line) {
        if (trim($line) === '') {
            $formatted .= "\n";
        } elseif (strpos($line, '─') !== false || strpos($line, '═') !== false || strpos($line, '=') !== false) {
            $formatted .= "────────────────────\n";
        } else {
            $formatted .= $line . "\n";
        }
    }
    
    return trim($text); // إرجاع النص الأصلي بدون تعديل لتجنب المشاكل
}

// باقي الدوال تبقى كما هي مع إضافة التحسينات
function is_subscribed($channel_id, $user_id) {
    $res = bot('getChatMember', ['chat_id' => $channel_id, 'user_id' => $user_id]);
    if (!$res || !$res['ok']) return false;
    
    $status = $res['result']['status'] ?? 'left';
    return $status != 'left';
}

function isUserBanned($user_id) {
    global $banned;
    return in_array($user_id, $banned);
}


function convertCurrency($amount, $user_id) {
    global $userCurrencies, $exchangeRates;
    $currency = $userCurrencies[$user_id] ?? 'USD';
    $rate = $exchangeRates[$currency]['rate'] ?? 1;
    $symbol = $exchangeRates[$currency]['symbol'] ?? '$';
    
    $converted_amount = $amount * $rate;
    
    // تحديد عدد الخانات العشرية بناءً على حجم المبلغ
    if ($converted_amount < 0.001) {
        return number_format($converted_amount, 6) . $symbol;
    } elseif ($converted_amount < 0.01) {
        return number_format($converted_amount, 5) . $symbol;
    } elseif ($converted_amount < 0.1) {
        return number_format($converted_amount, 4) . $symbol;
    } elseif ($converted_amount < 1) {
        return number_format($converted_amount, 3) . $symbol;
    } else {
        return number_format($converted_amount, 2) . $symbol;
    }
}
function getBalance($user_id) {
    global $balances;
    return $balances[$user_id] ?? 0;
}

function setBalance($user_id, $amount) {
    global $balances, $balancesFile;
    $balances[$user_id] = max(0, $amount);
    file_put_contents($balancesFile, json_encode($balances, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function addBalance($user_id, $amount) {
    setBalance($user_id, getBalance($user_id) + $amount);
}

function subtractBalance($user_id, $amount) {
    setBalance($user_id, max(0, getBalance($user_id) - $amount));
}

function generateCard($amount) {
    global $cards, $cardsFile;
    $code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
    $cards[$code] = $amount;
    file_put_contents($cardsFile, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $code;
}

function redeemCard($user_id, $code) {
    global $cards, $cardsFile;
    if (!isset($cards[$code])) return false;
    $amount = $cards[$code];
    addBalance($user_id, $amount);
    unset($cards[$code]);
    file_put_contents($cardsFile, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // إرسال إشعار شحن الكرت
    sendCardRechargeNotification($user_id, $code, $amount);
    
    return $amount;
}

function processWelcomeText($user_id, $username = "") {
    global $welcome, $userCurrencies, $exchangeRates;
    $text = $welcome['text'] ?? getLang('welcome');
    
    $balance = getBalance($user_id);
    $currency = $userCurrencies[$user_id] ?? 'USD';
    $rate = $exchangeRates[$currency]['rate'] ?? 1;
    $symbol = $exchangeRates[$currency]['symbol'] ?? '$';
    $converted_balance = round($balance * $rate, 2) . $symbol;
    
    // الرصيد المصروف
    $spent_balance = getSpentBalance($user_id);
    $converted_spent = round($spent_balance * $rate, 2) . $symbol;
    
    // استبدال المتغيرات
    $text = str_replace('{balance}', $converted_balance, $text);
    $text = str_replace('{user_id}', $user_id, $text);
    $text = str_replace('{username}', $username ?: "زائر", $text);
    
    // المتغيرات الجديدة
    $text = str_replace('{account_number}', generateUserAccountNumber($user_id), $text);
    $text = str_replace('{spent_balance}', $converted_spent, $text);
    $text = str_replace('{user_currency}', getUserCurrency($user_id), $text);
    $text = str_replace('{user_level}', getUserLevel($user_id), $text);
    $text = str_replace('{user_link}', getUserLink($user_id, $username), $text);
    
    return $text;
}

function getUserData($user_id) {
    global $users, $usersFile;
    
    if (!isset($users[$user_id])) {
        $users[$user_id] = [
            'joined_date' => date('Y-m-d H:i:s'),
            'total_charged' => 0,
            'total_spent' => 0,
            'total_orders' => 0,
            'referral_code' => substr(md5($user_id . time()), 0, 8),
            'referred_by' => null,
            'referral_bonus' => 0,
            'referral_count' => 0,
            'math_verified' => false,
            'referral_joined' => null,
            'is_new' => true // علامة للمستخدم الجديد
        ];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $users[$user_id];
}

function updateUserData($user_id, $data) {
    global $users, $usersFile;
    $users[$user_id] = array_merge(getUserData($user_id), $data);
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// التحقق إذا كان المستخدم مسجل بالإحالة
function isUserReferred($user_id) {
    global $users;
    $user_data = getUserData($user_id);
    return !empty($user_data['referred_by']);
}

// نظام التحقق الرياضي مع الأزرار للإحالة
function generateMathProblemWithButtons() {
    $operations = ['+', '-', '*'];
    $operation = $operations[array_rand($operations)];
    
    switch($operation) {
        case '+':
            $num1 = rand(1, 10);
            $num2 = rand(1, 10);
            $answer = $num1 + $num2;
            $problem = "$num1 + $num2";
            break;
        case '-':
            $num1 = rand(2, 10);
            $num2 = rand(1, $num1 - 1);
            $answer = $num1 - $num2;
            $problem = "$num1 - $num2";
            break;
        case '*':
            $num1 = rand(1, 5);
            $num2 = rand(1, 5);
            $answer = $num1 * $num2;
            $problem = "$num1 × $num2";
            break;
    }
    
    return ['problem' => $problem, 'answer' => $answer];
}
    
    // إنشاء 3 خيارات (إجابة صحيحة + إجابتين خاطئتين)
    $options = [$answer];
    while (count($options) < 3) {
        $wrong_answer = $answer + rand(-5, 5);
        if ($wrong_answer != $answer && $wrong_answer > 0 && !in_array($wrong_answer, $options)) {
            $options[] = $wrong_answer;
        }
    }
    
    shuffle($options);
    
    return [
        'problem' => $problem,
        'answer' => $answer,
        'options' => $options,
        'correct_index' => array_search($answer, $options)
    ];


// نظام التحقق الرياضي العادي
function generateMathProblem() {
    $operations = ['+', '-', '*'];
    $operation = $operations[array_rand($operations)];
    
    switch($operation) {
        case '+':
            $num1 = rand(1, 10);
            $num2 = rand(1, 10);
            $answer = $num1 + $num2;
            $problem = "$num1 + $num2";
            break;
        case '-':
            $num1 = rand(2, 10);
            $num2 = rand(1, $num1 - 1);
            $answer = $num1 - $num2;
            $problem = "$num1 - $num2";
            break;
        case '*':
            $num1 = rand(1, 5);
            $num2 = rand(1, 5);
            $answer = $num1 * $num2;
            $problem = "$num1 × $num2";
            break;
    }
    
    return [
        'problem' => $problem,
        'answer' => $answer,
        'attempts' => 0
    ];
}

function saveMathVerification($user_id, $math_data) {
    global $mathVerification, $mathVerificationFile;
    $mathVerification[$user_id] = $math_data;
    file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getMathVerification($user_id) {
    global $mathVerification;
    return $mathVerification[$user_id] ?? null;
}

function clearMathVerification($user_id) {
    global $mathVerification, $mathVerificationFile;
    if (isset($mathVerification[$user_id])) {
        unset($mathVerification[$user_id]);
        file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// نظام الإحالة المباشرة بعد التحقق الرياضي
function handleDirectReferral($user_id, $referral_code) {
    global $users, $usersFile, $settings, $referrals, $referralsFile;
    
    // البحث عن صاحب كود الإحالة
    $referrer_id = null;
    foreach ($users as $id => $user_data) {
        if (isset($user_data['referral_code']) && $user_data['referral_code'] == $referral_code) {
            $referrer_id = $id;
            break;
        }
    }
    
    if ($referrer_id && $referrer_id != $user_id && !isUserReferred($user_id)) {
        $bonus = $settings['referral_bonus'] ?? 10;
        
        // إضافة المكافأة للمُحيل
        addBalance($referrer_id, $bonus);
        
        // تحديث بيانات المُحيل
        if (!isset($users[$referrer_id]['referral_count'])) {
            $users[$referrer_id]['referral_count'] = 0;
        }
        if (!isset($users[$referrer_id]['referral_bonus'])) {
            $users[$referrer_id]['referral_bonus'] = 0;
        }
        
        $users[$referrer_id]['referral_count']++;
        $users[$referrer_id]['referral_bonus'] += $bonus;
        
        // تحديث بيانات المستخدم الجديد
        $users[$user_id]['referred_by'] = $referrer_id;
        $users[$user_id]['referral_joined'] = date('Y-m-d H:i:s');
        $users[$user_id]['math_verified'] = true; // تفعيل الحساب
        
        // حفظ السجل
        $referral_record = [
            'referrer_id' => $referrer_id,
            'referred_id' => $user_id,
            'bonus' => $bonus,
            'date' => date('Y-m-d H:i:s')
        ];
        $referrals[] = $referral_record;
        
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($referralsFile, json_encode($referrals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // إرسال إشعار للمُحيل
        $referrer_balance = convertCurrency(getBalance($referrer_id), $referrer_id);
        sendMessage($referrer_id, 
            "*🎊 مبروك\\!*\n\n*لقد حصلت على مكافأة إحالة بقيمة {$bonus}\\$*\n*👤 المستخدم الجديد:* $user_id\n\n*💰 رصيدك الحالي:* {$referrer_balance}",
            [[['text' => getLang('back_button'), 'callback_data' => "back_home"]]]
        );
        
        return [
            'success' => true,
            'referrer_id' => $referrer_id,
            'bonus' => $bonus
        ];
    }
    
    return ['success' => false];
}

// الإحصائيات
function getUserStatistics($user_id) {
    global $users, $balances, $settings;
    
    $user_data = getUserData($user_id);
    $user_balance = getBalance($user_id);
    
    // حساب إحصائيات المستخدم
    $us_charge2 = $user_data['total_charged'];
    $us_coin2 = $user_balance;
    $us_spent2 = $user_data['total_spent'];
    $us_all = $user_data['total_orders'];
    
    // حساب إحصائيات جميع المستخدمين
    $coin_all_Aymn = array_sum($balances);
    $coin_spent_Aymn = 0;
    $all_order = 0;
    foreach ($users as $user) {
        $coin_spent_Aymn += $user['total_spent'];
        $all_order += $user['total_orders'];
    }
    $all = count($users);
    
    // مستوى VIP
    $vip_level = "عادي";
    $vip_bonus = 0;
    if ($user_data['total_spent'] > 100) {
        $vip_level = "فضي";
        $vip_bonus = 5;
    }
    if ($user_data['total_spent'] > 500) {
        $vip_level = "ذهبي";
        $vip_bonus = 10;
    }
    
    $DataTimeG = date('Y-m-d', strtotime($user_data['joined_date']));
    $coin_name = convertCurrency(1, $user_id);
    
    return [
        'user_charged' => $us_charge2,
        'user_balance' => $us_coin2,
        'user_spent' => $us_spent2,
        'user_orders' => $us_all,
        'total_balance' => $coin_all_Aymn,
        'total_spent' => $coin_spent_Aymn,
        'total_orders' => $all_order,
        'total_users' => $all,
        'vip_level' => $vip_level,
        'vip_bonus' => $vip_bonus,
        'join_date' => $DataTimeG,
        'currency' => $coin_name
    ];
}

// التحويل بين المستخدمين
function transferBalance($from_user, $to_user, $amount) {
    global $settings, $users, $usersFile;
    
    $fee_percent = $settings['transfer_fee'] ?? 5;
    $fee = ($amount * $fee_percent) / 100;
    $net_amount = $amount - $fee;
    
    if (getBalance($from_user) < $amount) {
        return ['success' => false, 'error' => 'insufficient_balance'];
    }
    
    if (!isset($users[$to_user])) {
        return ['success' => false, 'error' => 'invalid_user'];
    }
    
    subtractBalance($from_user, $amount);
    addBalance($to_user, $net_amount);
    
    // تحديث إحصائيات التحويل
    $users[$from_user]['total_spent'] += $amount;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return [
        'success' => true,
        'net_amount' => $net_amount,
        'fee' => $fee
    ];
}

// تنظيف الخطوات القديمة
function cleanupOldSteps() {
    global $steps, $stepsFile;
    $current_time = time();
    $cleaned = false;
    
    foreach ($steps as $user_id => $step_data) {
        if (isset($step_data['timestamp']) && ($current_time - $step_data['timestamp']) > 3600) {
            unset($steps[$user_id]);
            $cleaned = true;
        }
    }
    
    if ($cleaned) {
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// إرسال رسالة الاشتراك الإجباري
function sendSubscriptionMessage($chat_id, $missing_channels) {
    $text = getLang('subscription_required') . "\n\n";
    
    $buttons = [];
    foreach ($missing_channels as $channel) {
        $buttons[] = [
            ['text' => "انضم إلى {$channel['name']}", 'url' => $channel['link']]
        ];
    }
    
    $buttons[] = [
        ['text' => getLang('check_subscription_button'), 'callback_data' => "verify_sub"]
    ];
    
    sendMessage($chat_id, $text, $buttons);
}

// معالجة التحقق من الاشتراك
function handleSubscriptionVerification($chat_id, $message_id, $user_id) {
    $subscription = checkSubscription($user_id);
    
    if ($subscription['subscribed']) {
        // تم الاشتراك في جميع القنوات
        
        // التحقق من وجود إحالة معلقة ومعالجتها
        if (handleReferralAfterSubscription($chat_id, $user_id)) {
            // إذا تمت معالجة الإحالة، لا نعرض القائمة الرئيسية
            return;
        }
        
        editMessage($chat_id, $message_id, "✅ *تم التحقق من الاشتراك بنجاح!*\n\nيمكنك الآن استخدام البوت.", [[
            ['text' => "🚀 بدء الاستخدام", 'callback_data' => "back_home"]
        ]]);
        
        // إظهار القائمة الرئيسية
        showMainMenu($chat_id, $user_id);
    } else {
        // ... الكود الحالي ...
        // لم يتم الاشتراك في جميع القنوات
        editMessage($chat_id, $message_id, "❌*︙عذرار عزيزي المستخدم , لم تنضم بعد لجميع القنوات المطلوبة*

*♻️︙يرجى الانضمام للقنوات ثم اضغط على زر ✅التحقق:*", 
            array_merge(
                array_map(function($channel) {
                    return [['text' => "انضم إلى {$channel['name']}", 'url' => $channel['link']]];
                }, $subscription['missing_channels']),
                [[['text' => getLang('check_subscription_button'), 'callback_data' => "verify_sub"]]]
            )
        );
    }
}

// دالة الحصول على أزرار القائمة الرئيسية
function getMainButtons($user_id) {
$buttons = [
    [['text' => "🚀︙بِدءُ طلَبية رَشق جديدة", 'callback_data' => "list_services"]],
    [['text' => "💰︙إشحن رصيدك", 'callback_data' => "recharge"], ['text' => "🎟︙شحن كرت", 'callback_data' => "redeem_card"]],
    [['text' => "♻️︙تغيير العملة", 'callback_data' => "change_currency"], ['text' => "📊︙الاحصائيات", 'callback_data' => "statistics"]],
    [['text' => "💸︙شارك رابط الإحالة، واربح. 🤑", 'callback_data' => "referral"]],
    [['text' => "🔄︙تحويل رصيد", 'callback_data' => "transfer_balance"], ['text' => "📕︙التعليمات", 'callback_data' => "instructions"]],
        [['text' => "📢︙القناة الرسمية", 'callback_data' => "bot_channel"], ['text' => "🎥︙قناة الطلبات", 'callback_data' => "orders_channel"]],
    [['text' => "👨‍💻︙الدعم الفني", 'callback_data' => "support"]]
];
    
    // إضافة لوحة التحكم للمدير فقط
    if (isAdmin($user_id)) {
        $buttons[] = [['text' => "👑 لوحة التحكم", 'callback_data' => "admin_panel"]];
    }
    
    return $buttons;
}

// إظهار القائمة الرئيسية
function showMainMenu($chat_id, $user_id = null) {
    if ($user_id === null) {
        $user_id = $chat_id;
    }
    
    $text = processWelcomeText($user_id);
    $buttons = getMainButtons($user_id);
    
    sendMessage($chat_id, $text, $buttons);
}

// التحقق من تفعيل المستخدم
function isUserVerified($user_id) {
    global $users, $settings;
    
    // إذا كان التحقق الرياضي معطل، يعتبر المستخدم مفعل تلقائياً
    if (!($settings['math_verification_enabled'] ?? true)) {
        return true;
    }
    
    $user_data = getUserData($user_id);
    return $user_data['math_verified'] ?? false;
}

// الحصول على جميع الخدمات من جميع المواقع
function getAllServicesFromSites() {
    global $services1, $services2, $services3, $smm_sites;
    $all_services = [];
    
    if (!empty($services1) && ($smm_sites[1]['enabled'] ?? false)) {
        foreach ($services1 as $service_id => $service) {
            $service['site_id'] = 1;
            $all_services[$service_id] = $service;
        }
    }
    
    if (!empty($services2) && ($smm_sites[2]['enabled'] ?? false)) {
        foreach ($services2 as $service_id => $service) {
            $service['site_id'] = 2;
            $all_services[$service_id] = $service;
        }
    }
    
    if (!empty($services3) && ($smm_sites[3]['enabled'] ?? false)) {
        foreach ($services3 as $service_id => $service) {
            $service['site_id'] = 3;
            $all_services[$service_id] = $service;
        }
    }
    
    return $all_services;
}

// نظام التحقق الرياضي
function generateMathVerification() {
    $operations = ['+', '-', '*'];
    $operation = $operations[array_rand($operations)];
    
    switch($operation) {
        case '+':
            $num1 = rand(5, 50);
            $num2 = rand(5, 50);
            $answer = $num1 + $num2;
            $problem = "$num1 + $num2";
            break;
        case '-':
            $num1 = rand(20, 100);
            $num2 = rand(5, $num1 - 1);
            $answer = $num1 - $num2;
            $problem = "$num1 - $num2";
            break;
        case '*':
            $num1 = rand(2, 12);
            $num2 = rand(2, 12);
            $answer = $num1 * $num2;
            $problem = "$num1 × $num2";
            break;
    }
    
    return [
        'problem' => $problem,
        'answer' => $answer,
        'attempts' => 0
    ];
}

// التحويل بين المستخدمين
function transferUserBalance($from_user, $to_user, $amount) {
    global $settings, $users, $usersFile;
    
    $fee_percent = $settings['transfer_fee'] ?? 5;
    $fee = ($amount * $fee_percent) / 100;
    $net_amount = $amount - $fee;
    
    if (getBalance($from_user) < $amount) {
        return ['success' => false, 'error' => 'insufficient_balance'];
    }
    
    if (!isset($users[$to_user])) {
        return ['success' => false, 'error' => 'invalid_user'];
    }
    
    subtractBalance($from_user, $amount);
    addBalance($to_user, $net_amount);
    
    // تحديث إحصائيات التحويل
    $users[$from_user]['total_spent'] += $amount;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return [
        'success' => true,
        'net_amount' => $net_amount,
        'fee' => $fee
    ];
}
// دالة مساعدة لهروب النصوص في Markdown
function escapeMarkdown($text) {
    if (!is_string($text)) {
        return $text;
    }
    
    // الهروب من الرموز الخاصة في Markdown
    $characters = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($characters as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}
// دالة إرسال إشعار التفعيل إلى القناة
function sendActivationNotification($order_data) {
    global $bot_channels, $settings, $exchangeRates, $userCurrencies;
    
    // التحقق إذا كانت قناة التفعيلات مفعلة
    if (!($settings['activation_channel_enabled'] ?? true)) {
        return false;
    }
    
    $activation_channel = $bot_channels['activations_channel'] ?? '';
    if (!$activation_channel) {
        return false;
    }
    
    // إخفاء آخر 4 أرقام من ID المستخدم
    $user_id = $order_data['user_id'];
    $hidden_user_id = substr($user_id, 0, -4) . "••••";
    
    // إخفاء آخر 5 أحرف من الرابط
    $link = $order_data['link'];
    $hidden_link = $link;
    if (strlen($link) > 10) {
        if (strpos($link, '@') !== false) {
            // إذا كان الرابط معرف @
            $hidden_link = substr($link, 0, -5) . "•••••";
        } else {
            // إذا كان الرابط URL
            $hidden_link = substr($link, 0, -5) . "•••••";
        }
    }
    
    // تحويل السعر إلى عملة المستخدم الشخصية
    $price_usd = $order_data['price'];
    $user_currency = $userCurrencies[$user_id] ?? 'USD';
    $currency_rate = $exchangeRates[$user_currency]['rate'] ?? 1;
    $currency_symbol = $exchangeRates[$user_currency]['symbol'] ?? '$';
    $converted_price = round($price_usd * $currency_rate, 2) . $currency_symbol;
    
    // بناء نص الإشعار
    $text = "✅︙*عملية رشق جديدة.*\n\n";
    $text .= "🎬 - القسم: *{$order_data['category']}*\n";
    $text .= "✅ - الخدمة: *{$order_data['service']}*\n";
    $text .= "🆔 - رقم الطلب: *{$order_data['order_id']}*\n";
    $text .= "⚜ - العدد المطلوب: *{$order_data['quantity']}*\n";
    $text .= "💰 - سعر الطلب: {$converted_price} [ {$price_usd} 💲 ]\n";
    $text .= "👤 - العميل: *{$hidden_user_id}*\n";
    $text .= "🔗︙الرابط: *{$hidden_link}*\n\n";
   
    // الحصول على رابط البوت الحقيقي ديناميكياً
    $bot_username = getBotUsername();
    $bot_link = "https://t.me/$bot_username";
    
    // زر البوت الديناميكي
    $buttons = [[
        ['text' => "🤖 - بوت الخدمات - 🤖", 'url' => $bot_link]
    ]];
    
    // إرسال الإشعار إلى القناة مع تعطيل معاينة الرابط
    $data = [
        'chat_id' => $activation_channel,
        'text' => $text,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode(['inline_keyboard' => $buttons])
    ];
    
    return bot('sendMessage', $data);
}

// دالة للحصول على اسم البوت الحقيقي
function getBotUsername() {
    $result = bot('getMe', []);
    if ($result && $result['ok']) {
        return $result['result']['username'];
    }
    return "TurbAPIBot"; // قيمة افتراضية في حالة الخطأ
}
// دالة للحصول على رقم الطلب التالي
function getNextOrderNumber() {
    global $orderCounter, $orderCounterFile;
    
    $current_number = $orderCounter;
    $orderCounter++; // زيادة العداد للطلب القادم
    file_put_contents($orderCounterFile, $orderCounter);
    
    return $current_number;
}
// إضافة هذه الدوال في قسم الدوال الأساسية في functions.php

// إنشاء رقم حساب عشوائي للمستخدم
function generateUserAccountNumber($user_id) {
    global $users, $usersFile;
    
    $user_data = getUserData($user_id);
    
    // إذا كان الرقم موجوداً مسبقاً، إرجاعه
    if (isset($user_data['account_number'])) {
        return $user_data['account_number'];
    }
    
    // إنشاء رقم حساب جديد
    $random_suffix = strtoupper(substr(md5($user_id . time()), 0, 7));
    $account_number = "BOT-" . $random_suffix;
    
    // حفظ الرقم في بيانات المستخدم
    $user_data['account_number'] = $account_number;
    $users[$user_id] = $user_data;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $account_number;
}

// الحصول على الرصيد المصروف للمستخدم
function getSpentBalance($user_id) {
    global $users;
    $user_data = getUserData($user_id);
    return $user_data['total_spent'] ?? 0;
}

// الحصول على مستوى حساب المستخدم
function getUserLevel($user_id) {
    global $users;
    $user_data = getUserData($user_id);
    $total_spent = $user_data['total_spent'] ?? 0;
    
    if ($total_spent >= 1000) {
        return "💎 ماسي";
    } elseif ($total_spent >= 500) {
        return "🥇 ذهبي";
    } elseif ($total_spent >= 100) {
        return "🥈 فضي";
    } elseif ($total_spent >= 50) {
        return "🥉 برونزي";
    } else {
        return "🔰 عادي";
    }
}

// الحصول على نوع عملة المستخدم
function getUserCurrency($user_id) {
    global $userCurrencies, $exchangeRates;
    $currency_code = $userCurrencies[$user_id] ?? 'USD';
    return $exchangeRates[$currency_code]['name'] ?? 'دولار أمريكي';
}

// الحصول على رابط المستخدم
function getUserLink($user_id, $username = "") {
    if ($username) {
        return "https://t.me/" . $username;
    }
    return "https://t.me/user?id=" . $user_id;
}
// دالة للحصول على رصيد الموقع من API
function getSiteBalance($site_id) {
    global $smm_sites;
    
    if (!isset($smm_sites[$site_id]) || !$smm_sites[$site_id]['enabled']) {
        return ['error' => 'الموقع غير مفعل'];
    }
    
    $site = $smm_sites[$site_id];
    
    // إذا لم يكن هناك رابط أو مفتاح
    if (empty($site['url']) || empty($site['api_key'])) {
        return ['error' => 'إعدادات غير مكتملة'];
    }
    
    $params = [
        'key' => $site['api_key'],
        'action' => 'balance'
    ];
    
    $result = smmRequest($params, $site_id);
    
    if (isset($result['error'])) {
        return ['error' => $result['error']];
    }
    
    // تحليل الاستجابة بناءً على تنسيق كل موقع
    if (isset($result['balance'])) {
        return ['balance' => floatval($result['balance'])];
    } elseif (isset($result['data']['balance'])) {
        return ['balance' => floatval($result['data']['balance'])];
    } else {
        return ['error' => 'تعذر الحصول على الرصيد'];
    }
}
// معالجة الإحالة بعد التحقق من الاشتراك
// معالجة الإحالة بعد التحقق من الاشتراك
function handleReferralAfterSubscription($chat_id, $user_id) {
    global $users, $usersFile;
    
    // التحقق إذا كان هناك إحالة معلقة
    if (isset($users[$user_id]['pending_referral'])) {
        $referral_code = $users[$user_id]['pending_referral'];
        
        require_once 'referral_system.php';
        
        // بدء معالجة الإحالة (لا ننظف الإحالة المعلقة هنا، سيتم تنظيفها في نظام الإحالة)
        ReferralSystem::startReferralVerification($chat_id, $user_id, $referral_code);
        return true;
    }
    
    return false;
}
?>
