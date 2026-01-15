<?php
require_once 'config.php';
require_once 'functions.php';

// معالجة أمر /start
function handleStart($chat_id, $from_id, $message) {
    global $steps, $stepsFile, $users, $usersFile, $settings;
    
    // التحقق أولاً إذا كان دخول عبر رابط إحالة
    require_once 'referral_system.php';
    if (ReferralSystem::handleReferralStart($chat_id, $from_id, $message)) {
        return; // تمت معالجة الإحالة، لا تتابع
    }
    
    // إذا لم يكن هناك إحالة، اتبع المسار العادي
    // التحقق من الاشتراك الإجباري أولاً
    $subscription = checkSubscription($from_id);
    if (!$subscription['subscribed']) {
        sendSubscriptionMessage($chat_id, $subscription['missing_channels']);
        return;
    }
   
    
    // تسجيل المستخدم إذا لم يكن مسجلاً
    $user_data = getUserData($from_id);
    
    // إرسال إشعار للمالك إذا كان المستخدم جديداً
    if ($user_data['is_new'] ?? false) {
        $username = $message['from']['username'] ?? '';
        $first_name = $message['from']['first_name'] ?? '';
        sendNewUserNotification($from_id, $username, $first_name);
        
        // تحديث حالة المستخدم لعدم كونه جديداً
        updateUserData($from_id, ['is_new' => false]);
    }
    
    // التحقق إذا كان المستخدم يحتاج للتحقق الرياضي
    if (!isUserVerified($from_id) && ($settings['math_verification_enabled'] ?? true)) {
        startMathVerification($chat_id, $from_id);
        return;
    }
    
    // إظهار القائمة الرئيسية
    showMainMenu($chat_id, $from_id);
}

// في دالة startMathVerification - تحديث النص
function startMathVerification($chat_id, $user_id) {
    global $steps, $stepsFile, $mathVerification, $mathVerificationFile;
    
    $math_problem = generateMathProblem();
    $math_problem['type'] = 'normal';
    
    $mathVerification[$user_id] = $math_problem;
    file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $steps[$user_id] = [
        'step' => 'math_verification',
        'timestamp' => time()
    ];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // ✅ النص الجديد المحدث للتحقق العادي
    $message = "*يرجى التحقق انك لست روبوت ♻️*\n\n";
    $message .= "*☑️ - يرجى اكمال التحقق من خلال إرسال الإجابة الصحيحة لهذه المسألة ⬇️*\n\n";
    $message .= "*✳️ - السؤال: " . $math_problem['problem'] . "*\n\n";
    $message .= "*أرسل الإجابة الآن:*";
    
    sendMessage($chat_id, $message);
}

// معالجة التحقق الرياضي
function handleMathVerification($chat_id, $user_id, $answer) {
    global $users, $usersFile, $steps, $stepsFile, $mathVerification, $mathVerificationFile;
    
    $math_data = $mathVerification[$user_id] ?? null;
    if (!$math_data) {
        sendMessage($chat_id, "❌ لم نتمكن من العثور على بيانات التحقق. يرجى إعادة المحاولة باستخدام /start");
        return;
    }
    
    // إذا كان تحقق إحالة، استدعاء النظام المنفصل
    if (($math_data['type'] ?? '') == 'referral') {
        require_once 'referral_system.php';
        ReferralSystem::handleReferralMathAnswer($chat_id, $user_id, $answer);
        return;
    }
    
    // معالجة التحقق الرياضي العادي
    $user_answer = intval($answer);
    $correct_answer = $math_data['answer'];
    
    if ($user_answer == $correct_answer) {
        // نجح التحقق
        updateUserData($user_id, ['math_verified' => true]);
        unset($mathVerification[$user_id]);
        file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        unset($steps[$user_id]);
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // التحقق من الاشتراك الإجباري بعد التحقق الرياضي - استخدم checkSubscription بدلاً من checkSubscriptionForVerifiedUser
        $subscription = checkSubscription($user_id);
        
        if (!$subscription['subscribed']) {
            // إرسال رسالة الاشتراك الإجباري
            $text = "✅ *تم التحقق الرياضي بنجاح!*\n\n";
            $text .= "لكن يجب عليك الاشتراك في القنوات التالية لاستخدام البوت:\n\n";
            
            $buttons = [];
            foreach ($subscription['missing_channels'] as $channel) {
                $buttons[] = [['text' => "انضم إلى {$channel['name']}", 'url' => $channel['link']]];
            }
            
            $buttons[] = [['text' => "✅ تحقق من الاشتراك", 'callback_data' => "verify_sub"]];
            
            sendMessage($chat_id, $text, $buttons);
        } else {
            // دمج الرسالتين في رسالة واحدة لحل مشكلة التأخر
           // استخدام رسالة الترحيب الأساسية فقط
$welcome_text = processWelcomeText($user_id); 
            sendMessage($chat_id, $welcome_text, getMainButtons($user_id));
        }
        
    } else {
        // فشل التحقق
        $math_data['attempts']++;
        $mathVerification[$user_id] = $math_data;
        file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        if ($math_data['attempts'] >= 3) {
            // تجاوز الحد الأقصى للمحاولات، إنشاء مسألة جديدة
            $new_math_problem = generateMathProblem();
            $new_math_problem['type'] = 'normal';
            $mathVerification[$user_id] = $new_math_problem;
            file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, sprintf(getLang('math_verification_required'), $new_math_problem['problem']));
        } else {
            sendMessage($chat_id, sprintf(getLang('math_incorrect'), $math_data['problem']));
        }
    }
}

// التحقق من الاشتراك
function handleVerifySub($chat_id, $message_id, $from_id, $callback) {
    $subscription = checkSubscription($from_id);
    if (!$subscription['subscribed']) {
        editMessage($chat_id, $message_id, "❌ لم تشترك بعد في جميع القنوات المطلوبة. يرجى الاشتراك أولاً ثم اضغط على زر \"تحقق\" مرة أخرى.", 
            array_merge(
                array_map(function($channel) {
                    return [['text' => "انضم إلى {$channel['name']}", 'url' => $channel['link']]];
                }, $subscription['missing_channels']),
                [[['text' => "✅ تحقق من الاشتراك", 'callback_data' => "verify_sub"]]]
            )
        );
        return;
    }
    
    // تسجيل المستخدم
    $user_data = getUserData($from_id);
    
    // إرسال إشعار للمالك إذا كان المستخدم جديداً
    if ($user_data['is_new'] ?? false) {
        $username = $callback['from']['username'] ?? '';
        $first_name = $callback['from']['first_name'] ?? '';
        sendNewUserNotification($from_id, $username, $first_name);
        
        // تحديث حالة المستخدم لعدم كونه جديداً
        updateUserData($from_id, ['is_new' => false]);
    }
    
    // التحقق إذا كان المستخدم يحتاج للتحقق الرياضي
    global $settings;
    if (!isUserVerified($from_id) && ($settings['math_verification_enabled'] ?? true)) {
        editMessage($chat_id, $message_id, "✅ تم التحقق من الاشتراك بنجاح! سيتم الآن التحقق من حسابك...");
        startMathVerification($chat_id, $from_id);
        return;
    }
    
    // إظهار القائمة الرئيسية
    editMessage($chat_id, $message_id, processWelcomeText($from_id), getMainButtons($from_id));
}

// الرجوع للقائمة الرئيسية
function handleBackHome($chat_id, $message_id, $from_id, $callback) {
    editMessage($chat_id, $message_id, processWelcomeText($from_id), getMainButtons($from_id));
}

// قائمة الخدمات
function handleListServices($chat_id, $message_id) {
    global $categories;
    
    if (empty($categories)) {
        editMessage($chat_id, $message_id, getLang('no_services'), [[
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($categories as $category_id => $category_name) {
        $buttons[] = [['text' => $category_name, 'callback_data' => "category_$category_id"]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "back_home"]];
    
    editMessage($chat_id, $message_id, getLang('select_category'), $buttons);
}

// شحن الرصيد
function handleRecharge($chat_id, $message_id) {
    global $settings;
    editMessage($chat_id, $message_id, $settings['recharge_text'] ?? getLang('recharge_text'), [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
}

// شحن الكرت
function handleRedeemCard($chat_id, $message_id, $from_id) {
    global $steps, $stepsFile;
    
    $steps[$from_id] = ['step' => 'redeem_card'];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    editMessage($chat_id, $message_id, getLang('send_card_code'), [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
}

// تغيير العملة
function handleChangeCurrency($chat_id, $message_id) {
    global $exchangeRates;
    
    $buttons = [];
    foreach ($exchangeRates as $code => $currency) {
        $buttons[] = [[
            'text' => "{$currency['name']} ({$currency['symbol']})", 
            'callback_data' => "set_currency_$code"
        ]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "back_home"]];
    
    editMessage($chat_id, $message_id, getLang('change_currency'), $buttons);
}

// الإحصائيات
function handleStatistics($chat_id, $message_id, $from_id) {
    $stats = getUserStatistics($from_id);
    
    $text = "*🛍︙مشترياتك وتفاصيل حسابك في البوت 🔰*\n\n";
    
    // إحصائيات المستخدم - الحصول على القيم بالدولار وتحويلها
    $total_charged = convertCurrency($stats['user_charged'], $from_id); // إجمالي الرصيد المشحون
    $current_balance = convertCurrency($stats['user_balance'], $from_id); // الرصيد الحالي المتوفر
    $total_spent = convertCurrency($stats['user_spent'], $from_id); // إجمالي الصرفيات
    $total_orders = $stats['user_orders']; // عدد الطلبات
    $vip_level = $stats['vip_level']; // مستوى VIP
    $vip_bonus = $stats['vip_bonus']; // نسبة المكافأة
    $join_date = $stats['join_date']; // تاريخ الانضمام
    
    $buttons = [
        [['text' => "🔰 - تفاصيل حسابك 🔰", 'callback_data' => "no"]],
        [['text' => $current_balance, 'callback_data' => "no"], ['text' => "💰 رصيدك:", 'callback_data' => "no"]],
        [['text' => $total_spent, 'callback_data' => "no"], ['text' => "💸 الصرفيات:", 'callback_data' => "no"]],
        [['text' => $total_orders, 'callback_data' => "no"], ['text' => "🛎️ عدد طلباتك:", 'callback_data' => "no"]],
        [['text' => $vip_level, 'callback_data' => "no"], ['text' => "💎 مستوى حسابك :", 'callback_data' => "no"]],
        [['text' => $vip_bonus."%", 'callback_data' => "no"], ['text' => "🪄 نسبة الزيادة :", 'callback_data' => "no"]],
        [['text' => $join_date, 'callback_data' => "no"], ['text' => "📆 الإنشاء:", 'callback_data' => "no"]],
    ];
    
    // إضافة إحصائيات الإحالة
    global $users;
    $user_data = $users[$from_id] ?? [];
    if (isset($user_data['referral_count'])) {
        $referral_bonus = convertCurrency($user_data['referral_bonus'] ?? 0, $from_id);
        
        $buttons[] = [['text' => "📊 إحصائيات الإحالة", 'callback_data' => "no"]];
        $buttons[] = [
            ['text' => $user_data['referral_count'], 'callback_data' => "no"], 
            ['text' => "👥 عدد المُحالين:", 'callback_data' => "no"]
        ];
        $buttons[] = [
            ['text' => $referral_bonus, 'callback_data' => "no"], 
            ['text' => "💰 إجمالي مكافآت الإحالة:", 'callback_data' => "no"]
        ];
    }
    
    // إضافة إحصائيات البوت للمدير فقط
    if (isAdmin($from_id)) {
        $total_balance_all = convertCurrency($stats['total_balance'], $from_id);
        $total_spent_all = convertCurrency($stats['total_spent'], $from_id);
        $total_orders_all = $stats['total_orders'];
        $total_users_all = $stats['total_users'];
        
        $buttons[] = [['text' => "🔰 - تفاصيل جميع العملاء 🔰", 'callback_data' => "no"]];
        $buttons[] = [['text' => $total_balance_all, 'callback_data' => "no"], ['text' => "💰 الرصيد:", 'callback_data' => "no"]];
        $buttons[] = [['text' => $total_spent_all, 'callback_data' => "no"], ['text' => "💸 الصرفيات:", 'callback_data' => "no"]];
        $buttons[] = [['text' => $total_orders_all, 'callback_data' => "no"], ['text' => "🛎️ عدد الطلبات:", 'callback_data' => "no"]];
        $buttons[] = [['text' => $total_users_all, 'callback_data' => "no"], ['text' => "👥 عدد العملاء:", 'callback_data' => "no"]];
    }
    
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "back_home"]];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// تحويل الرصيد
function handleTransferBalance($chat_id, $message_id, $from_id) {
    global $settings;
    
    $min_amount = $settings['transfer_min_amount'] ?? 1;
    $fee_percent = $settings['transfer_fee'] ?? 5;
    
    $text = "*✅︙يمكنك تحويل رصيدك الى أصدقائك الآن. 🙋🏻*

♻️︙أرسل حساب الشخص الذي تريد التحويل اليه، *( الأيدي)*

*💥︙أقل مبلغ للتحويل 1$ 💰*
*➕︙عمولة التحويل 5% 🧿*";
    
    editMessage($chat_id, $message_id, $text, [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
    
    global $steps, $stepsFile;
    $steps[$from_id] = ['step' => 'transfer_user_id'];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// نظام الإحالة
function handleReferral($chat_id, $message_id, $from_id) {
    require_once 'referral_system.php';
    ReferralSystem::showReferralInfo($chat_id, $message_id, $from_id);
}

// التعليمات
function handleInstructions($chat_id, $message_id) {
    global $instructions;
    editMessage($chat_id, $message_id, $instructions, [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
}

// قناة البوت
function handleBotChannel($chat_id, $message_id) {
    global $bot_channels;
    
    $channel_link = $bot_channels['main_channel'] ?? '';
    if ($channel_link) {
        editMessage($chat_id, $message_id, "*📢 قناة البوت الرسمية:*", [[
            ['text' => "📢 انضم للقناة", 'url' => $channel_link]
        ], [
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "📢 لم يتم تعيين قناة البوت بعد.", [[
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
    }
}

// قناة الطلبات
function handleOrdersChannel($chat_id, $message_id) {
    global $bot_channels;
    
    $channel_link = $bot_channels['orders_channel'] ?? '';
    if ($channel_link) {
        editMessage($chat_id, $message_id, "*🛒 قناة الطلبات:*", [[
            ['text' => "🛒 انضم للقناة", 'url' => $channel_link]
        ], [
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "🛒 لم يتم تعيين قناة الطلبات بعد.", [[
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
    }
}

// الدعم الفني
function handleSupport($chat_id, $message_id, $from_id) {
    global $steps, $stepsFile;
    
    $steps[$from_id] = ['step' => 'support_message'];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    editMessage($chat_id, $message_id, "*📬︙إدارة الدعم الخاصه بالبوت .*

*✅︙أنت الأن في تواصل مع الدعم، أي رسالة ترسلها سيتم ايصالها الى الإدارة فورا.*
⚜︙أي مشكلة واجهتك في البوت فقط قم بإرسالها هنا الان، ولا تنسى ارفاقها مع حسابك الخاص بالبوت.
*🕰︙متواجدون على مدار الساعة.*

ارسل رسالتك وانتظر الرد

*❎︙تجنب إرسال الإساءات إن أمكن.*", [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
}

// معالجة باقي الكالبات
function handleCallbackData($chat_id, $message_id, $from_id, $data, $callback) {
    global $steps, $stepsFile, $categories, $userCurrencies, $currenciesFile;
    
    // معالجة الأقسام
    if (strpos($data, "category_") === 0) {
        $category_id = str_replace("category_", "", $data);
        $all_services = getAllServices();
        $category_services = array_filter($all_services, function($service) use ($category_id) {
            return $service['category'] == $category_id;
        });
        
        if (empty($category_services)) {
            editMessage($chat_id, $message_id, getLang('no_services'), [[
                ['text' => getLang('back_button'), 'callback_data' => "back_home"]
            ]]);
            return;
        }
        
        $buttons = [];
        
        // إضافة الزر الفارغ فوق أزرار الخدمات
        $buttons[] = [['text' => "🎬︙نوع الرشق ◁سعر 1000 عدد. 💰", 'callback_data' => "no"]];
        
        foreach ($category_services as $service_id => $service) {
            $price = convertCurrency($service['price'], $from_id);
            $buttons[] = [[
                'text' => "{$service['name']} ◁ {$price}", 
                'callback_data' => "service_$service_id"
            ]];
        }
        $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "list_services"]];
        
        editMessage($chat_id, $message_id, getLang('select_service'), $buttons);
    }
    // ... باقي الكود
// في دالة handleCallbackData، أضف هذا الحالة:
elseif (strpos($data, "refresh_order_") === 0) {
    handleOrderRefresh($chat_id, $message_id, $from_id, $data);
}
elseif ($data == "no_action") {
    // لا تفعل شيئاً عند الضغط على زر "الطلب مكتمل"
    answerCallback($data, "✅ الطلب مكتمل", true);
}
// معالجة الخدمات
elseif (strpos($data, "service_") === 0) {
    $service_id = str_replace("service_", "", $data);
    $all_services = getAllServices();
    
    if (!isset($all_services[$service_id])) {
        editMessage($chat_id, $message_id, getLang('service_not_found'), [[
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
        return;
    }
    
    $service = $all_services[$service_id];
    $category_name = $categories[$service['category']] ?? 'غير محدد';
    
    // بناء نص الخدمة - تم التعديل حسب الطلب
    $text = "*📋︙معلومات الخدمة الكاملة*\n\n";
    $text .= "*📁︙اسم القسم:* $category_name\n";
    $text .= "*🛍️︙الخدمة:* {$service['name']}\n\n";
    
    $text .= "*✳️︙المعلومات الأكثر تفاصيل تجدها اسفل 👇*\n";
    $text .= "*🏷︙يمكنك طلب الخدمة عبر الضغط على زر ( طلب الخدمة )*";
    
    // الحصول على السعر المحول بدون كلمة "لكل 1000"
    $converted_price = convertCurrency($service['price'], $from_id);
    
    // إنشاء أزرار معلومات الخدمة - تم التعديل بالكامل
    $buttons = [
        [['text' => "⬇️ - بيانات الخدمة - ⬇️", 'callback_data' => "no"]],
        [['text' => $converted_price, 'callback_data' => "no"], ['text' => "💰︙سعر 1K", 'callback_data' => "no"]],
        [['text' => $service['speed'] ?? 'غير محدد', 'callback_data' => "no"], ['text' => "🚀︙السرعة", 'callback_data' => "no"]],
        [['text' => $service['quality'] ?? '✅️ عالية', 'callback_data' => "no"], ['text' => "🏆︙الجودة", 'callback_data' => "no"]],
        [['text' => $service['guarantee'] ?? 'غير محدد', 'callback_data' => "no"], ['text' => "♻️︙الضمان", 'callback_data' => "no"]],
        [['text' => $service['min'] . "⚜", 'callback_data' => "no"], ['text' => "📊︙الحد الادنى", 'callback_data' => "no"]],
        [['text' => $service['max'] . "✔️", 'callback_data' => "no"], ['text' => "📉︙الحد الاقصى", 'callback_data' => "no"]],
        [['text' => "✳️︙طلب الخدمة", 'callback_data' => "request_service_$service_id"]],
        [['text' => getLang('back_button'), 'callback_data' => "category_{$service['category']}"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}
    
    // معالجة طلب الخدمة
    elseif (strpos($data, "request_service_") === 0) {
        $service_id = str_replace("request_service_", "", $data);
        $all_services = getAllServices();
        
        if (!isset($all_services[$service_id])) {
            editMessage($chat_id, $message_id, getLang('service_not_found'), [[
                ['text' => getLang('back_button'), 'callback_data' => "back_home"]
            ]]);
            return;
        }
        
        $service = $all_services[$service_id];
        $price = convertCurrency($service['price'], $from_id);
        
        $description_text = $service['description'] ? "\n📝 " . $service['description'] : "";
        $text = sprintf(getLang('send_link'), 
            $service['name'], 
            $price . " لكل 1000",
            $service['min'],
            $service['max'],
            $description_text
        );
        
        $steps[$from_id] = [
            'step' => 'service_link',
            'service_id' => $service_id,
            'site_id' => $service['site_id']
        ];
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        editMessage($chat_id, $message_id, $text, [[
            ['text' => getLang('back_button'), 'callback_data' => "service_$service_id"]
        ]]);
    }
    
    // معالجة تأكيد الطلب
    elseif (strpos($data, "confirm_order_") === 0) {
        handleOrderConfirmation($chat_id, $message_id, $from_id, $data);
    }
    
    // معالجة إلغاء الطلب
    elseif ($data == "cancel_order") {
        global $steps, $stepsFile;
        unset($steps[$from_id]);
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        editMessage($chat_id, $message_id, getLang('order_canceled'), [[
            ['text' => getLang('back_button'), 'callback_data' => "back_home"]
        ]]);
    }
    
    // معالجة تأكيد التحويل
    elseif (strpos($data, "confirm_transfer_") === 0) {
        handleTransferConfirmation($chat_id, $message_id, $from_id, $data);
    }
    
    // معالجة إلغاء التحويل
    elseif ($data == "cancel_transfer") {
        handleTransferCancel($chat_id, $message_id, $from_id);
    }
    
    // معالجة تغيير العملة
    elseif (strpos($data, "set_currency_") === 0) {
        $currency_code = str_replace("set_currency_", "", $data);
        global $exchangeRates;
        
        if (isset($exchangeRates[$currency_code])) {
            $userCurrencies[$from_id] = $currency_code;
            file_put_contents($currenciesFile, json_encode($userCurrencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            editMessage($chat_id, $message_id, getLang('currency_changed') . $exchangeRates[$currency_code]['name'], [[
                ['text' => getLang('back_button'), 'callback_data' => "back_home"]
            ]]);
        }
    }
    
    // معالجة القنوات والدعم
    elseif ($data == "bot_channel") {
        handleBotChannel($chat_id, $message_id);
    }
    elseif ($data == "orders_channel") {
        handleOrdersChannel($chat_id, $message_id);
    }
    elseif ($data == "support") {
        handleSupport($chat_id, $message_id, $from_id);
    }
    
    // لوحة التحكم للمدير
    elseif ($data == "admin_panel" && isAdmin($from_id)) {
        showAdminPanel($chat_id, $message_id);
    }
}

// معالجة الخطوات - تم التحديث لحل المشكلة
function handleSteps($chat_id, $from_id, $text, $message) {
    global $steps, $stepsFile, $orders, $ordersFile, $users, $usersFile;
    
    $step_data = $steps[$from_id] ?? null;
    if (!$step_data) {
        // إذا لم يكن هناك خطوة نشطة، لا تقم بأي معالجة ولا ترسل رسالة الترحيب
        return false;
    }
    
    $step = $step_data['step'];
    
    switch($step) {
        case 'math_verification':
            handleMathVerification($chat_id, $from_id, $text);
            break;
            
      case 'referral_math_verification':
    require_once 'referral_system.php';
    ReferralSystem::handleReferralMathAnswer($chat_id, $from_id, $text);
    break;
            
        case 'redeem_card':
            $amount = redeemCard($from_id, $text);
            if ($amount === false) {
                sendMessage($chat_id, getLang('invalid_card'));
            } else {
                $new_balance = getBalance($from_id);
                $converted_amount = convertCurrency($amount, $from_id);
                $converted_balance = convertCurrency($new_balance, $from_id);
                
                sendMessage($chat_id, getLang('card_redeemed') . $converted_amount . getLang('current_balance') . $converted_balance);
                
                // تحديث إحصائيات الشحن
                updateUserData($from_id, [
                    'total_charged' => ($users[$from_id]['total_charged'] ?? 0) + $amount
                ]);
            }
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'service_link':
            $service_id = $step_data['service_id'];
            $site_id = $step_data['site_id'];
            $all_services = getAllServices();
            $service = $all_services[$service_id];
            
            // التحقق من صيغة الرابط
            $link_format = $service['link_format'] ?? 1;
            $is_valid = false;
            
            if ($link_format == 1) {
                // صيغة @username
                $is_valid = preg_match('/^@[a-zA-Z0-9_]+$/', $text);
            } else {
                // صيغة رابط كامل
                $is_valid = filter_var($text, FILTER_VALIDATE_URL) !== false;
            }
            
            if (!$is_valid) {
                sendMessage($chat_id, getLang('invalid_link_format'));
                return true;
            }
            
            $steps[$from_id] = [
                'step' => 'service_quantity',
                'service_id' => $service_id,
                'site_id' => $site_id,
                'link' => $text
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('send_quantity'));
            break;
            
case 'service_quantity':
    $service_id = $step_data['service_id'];
    $site_id = $step_data['site_id'];
    $link = $step_data['link'];
    $all_services = getAllServices();
    $service = $all_services[$service_id];
    
    $quantity = intval($text);
    if ($quantity < $service['min'] || $quantity > $service['max']) {
        sendMessage($chat_id, sprintf(getLang('invalid_quantity'), $service['min'], $service['max']));
        return true;
    }
    
    // حساب السعر بدقة مع التعامل مع الكسور العشرية
    $total_price = ($service['price'] * $quantity) / 1000;
    // إذا كان السعر صغير جداً، استخدم حساب أكثر دقة
    if ($total_price < 0.0001) {
        $total_price = $service['price'] * ($quantity / 1000);
    }
    // التأكد من أن السعر لا يكون صفراً
    if ($total_price == 0 && $quantity > 0 && $service['price'] > 0) {
        $total_price = max(0.000001, $service['price'] * ($quantity / 1000));
    }
    $user_balance = getBalance($from_id);
    
    if ($user_balance < $total_price) {
        $converted_balance = convertCurrency($user_balance, $from_id);
        $converted_price = convertCurrency($total_price, $from_id);
        sendMessage($chat_id, sprintf(getLang('insufficient_balance'), $converted_balance, $converted_price));
        unset($steps[$from_id]);
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    
    $converted_price = convertCurrency($total_price, $from_id);
    $converted_price_per_k = convertCurrency($service['price'], $from_id);
    
    // الحصول على اسم القسم
    global $categories;
    $category_name = $categories[$service['category']] ?? 'غير محدد';
    
    // النص الجديد لتأكيد الطلب
    $confirm_text = "*✅ - معلومات تأكيد الطلب .*\n\n";
    $confirm_text .= "🌀 - القسم: *{$category_name}*\n";
    $confirm_text .= "🛍 - الخدمة: *{$service['name']}*\n";
    $confirm_text .= "💰 - السعر 1K: *{$converted_price_per_k}*\n";
    $confirm_text .= "💸 - السعر الكلي: *{$converted_price}*\n";
    $confirm_text .= "🏆 - الجودة: *{$service['quality']}*\n";
    $confirm_text .= "🚀 - السرعة: *{$service['speed']}*\n";
    $confirm_text .= "🔰 - الضمان: *{$service['guarantee']}*\n\n";
    $confirm_text .= "🏷 - الوصف: *" . ($service['description'] ?: 'لا يوجد وصف') . "*\n";
    $confirm_text .= "🔗 - الرابط: *{$link}*\n\n";
    $confirm_text .= "*♻️ - هل تريد المتابعة وتأكيد الطلب؟*";
    
    $steps[$from_id] = [
        'step' => 'confirm_order',
        'service_id' => $service_id,
        'site_id' => $site_id,
        'link' => $link,
        'quantity' => $quantity,
        'total_price' => $total_price
    ];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // الأزرار الجديدة - زر التأكيد فوق زر الإلغاء مع تعطيل معاينة الرابط
    $keyboard = [
        [
            ['text' => "☑️ - تأكيد الطلب", 'callback_data' => "confirm_order_$from_id"]
        ],
        [
            ['text' => "⚠️ - إلغاء الطلب", 'callback_data' => "cancel_order"]
        ]
    ];
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $confirm_text,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];
    
    bot('sendMessage', $data);
    break;
            
        case 'transfer_user_id':
            $to_user = intval($text);
            
            // التحقق إذا كان المستخدم موجوداً
            global $users;
            if (!isset($users[$to_user])) {
                sendMessage($chat_id, "*⚠️ - معرف المستخدم هذا غير مسجل في البوت*\n\nيرجى إرسال معرف مستخدم صحيح:");
                return true;
            }
            
            if ($to_user == $from_id) {
                sendMessage($chat_id, "❌ لا يمكن تحويل الرصيد لنفسك.");
                unset($steps[$from_id]);
                file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return true;
            }
            
            $steps[$from_id] = [
                'step' => 'transfer_amount',
                'to_user' => $to_user
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "✅︙معرف المستخدم: *{$to_user}*\n\n🔰 - الآن *أرسل المبلغ الذي تريد تحويله* للمستخدم *(المبلغ فقط بالدولار):*");
            break;
            
        case 'transfer_amount':
            $to_user = $step_data['to_user'];
            $amount = floatval($text);
            
            // التحقق من صحة المبلغ
            if ($amount <= 0) {
                sendMessage($chat_id, "*❌︙المبلغ غير صالح. يرجى إرسال مبلغ صحيح:\n\n✅︙قم بإرسال مبلغ ارقام مثل 1 ، 2 ، 100.*");
                return true;
            }
            
            // التحقق من الحد الأدنى للتحويل
            global $settings;
            $min_amount = $settings['transfer_min_amount'] ?? 1;
            
            if ($amount < $min_amount) {
                sendMessage($chat_id, "*⚠️︙المبلغ أقل من الحد الأدنى للتحويل ({$min_amount}$)\nارسل مبلغ أكبر من الحد الادنى✅*");
                return true;
            }
            
            // التحقق من رصيد المستخدم
            $user_balance = getBalance($from_id);
            if ($user_balance < $amount) {
                $converted_balance = convertCurrency($user_balance, $from_id);
                $converted_amount = convertCurrency($amount, $from_id);
                sendMessage($chat_id, "*❎︙عذرا عزيزي المستخدم ، رصيدك غير كافي للتحويل\n\n💰︙رصيدك الحالي: {$converted_balance}\n💸︙المبلغ المطلوب: {$converted_amount}*");
                unset($steps[$from_id]);
                file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return true;
            }
            
            // حساب العمولة والمبلغ الصافي
            $fee_percent = $settings['transfer_fee'] ?? 5;
            $fee = ($amount * $fee_percent) / 100;
            $net_amount = $amount - $fee;
            
            // عرض تفاصيل التحويل للموافقة
            $text = "*📜︙تفاصيل عملية التحويل ♻️*\n\n";
            $text .= "👤︙المستخدم المستلم: *{$to_user}*\n";
            $text .= "💰︙المبلغ: *{$amount}$* دولار\n";
            $text .= "💸︙عمولة التحويل *({$fee_percent}%): {$fee}$* دولار\n";
            $text .= "✅︙المبلغ المستلم: *{$net_amount}*$ دولار\n\n";
            $text .= "*هل تريد تأكيد عملية التحويل؟ ✅*";
            
            $steps[$from_id] = [
                'step' => 'transfer_confirm',
                'to_user' => $to_user,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $net_amount
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, $text, [
                [
                    ['text' => "✅ - تأكيد العملية", 'callback_data' => "confirm_transfer_$from_id"],
                    ['text' => "⚠️ - إلغاء ورجوع", 'callback_data' => "cancel_transfer"]
                ]
            ]);
            break;
            
        case 'support_message':
            // إرسال رسالة الدعم للمالك
            sendSupportNotification($from_id, $text);
            
            sendMessage($chat_id, "*✅ - تم إرسال رسالتك للدعم الفني. سيتم الرد عليك في أقرب وقت🤙🏻.*", [[
                ['text' => getLang('back_button'), 'callback_data' => "back_home"]
            ]]);
            
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        default:
            // إذا كانت الخطوة غير معروفة، قم بإزالتها
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
    }
    
    return true;
}

// معالجة تأكيد الطلب
function handleOrderConfirmation($chat_id, $message_id, $from_id, $data) {
    global $steps, $stepsFile, $orders, $ordersFile, $users, $usersFile, $categories;
    
    $target_user = str_replace("confirm_order_", "", $data);
    if ($target_user != $from_id) {
        return;
    }
    
    if (!isset($steps[$from_id]) || $steps[$from_id]['step'] != 'confirm_order') {
        editMessage($chat_id, $message_id, getLang('no_order_to_confirm'));
        return;
    }
    
    $order_data = $steps[$from_id];
    $all_services = getAllServices();
    $service = $all_services[$order_data['service_id']];
    
    // الحصول على رقم الطلب التالي
    $order_number = getNextOrderNumber();
    
    // التحقق من الرصيد مرة أخرى
    $user_balance = getBalance($from_id);
    $balance_before = $user_balance; // حفظ الرصيد قبل الطلب
    if ($user_balance < $order_data['total_price']) {
        $converted_balance = convertCurrency($user_balance, $from_id);
        $converted_price = convertCurrency($order_data['total_price'], $from_id);
        editMessage($chat_id, $message_id, sprintf(getLang('insufficient_balance'), $converted_balance, $converted_price));
        unset($steps[$from_id]);
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return;
    }
    
    // خصم الرصيد
    subtractBalance($from_id, $order_data['total_price']);
    
    // تحديث إحصائيات المستخدم
    updateUserData($from_id, [
        'total_spent' => ($users[$from_id]['total_spent'] ?? 0) + $order_data['total_price'],
        'total_orders' => ($users[$from_id]['total_orders'] ?? 0) + 1
    ]);
    
    // إرسال الطلب لـ SMM API
    $params = [
        'action' => 'add',
        'service' => $service['smm_id'],
        'link' => $order_data['link'],
        'quantity' => $order_data['quantity']
    ];
    
    $api_response = smmRequest($params, $order_data['site_id']);
    
    // الحصول على معرف الطلب من SMM إذا كان موجوداً
    $smm_order_id = $api_response['order'] ?? 'غير متوفر';
    
    // حفظ الطلب
    $order_id = uniqid();
    $orders[$order_id] = [
        'user_id' => $from_id,
        'service_id' => $order_data['service_id'],
        'service_name' => $service['name'],
        'site_id' => $order_data['site_id'],
        'link' => $order_data['link'],
        'quantity' => $order_data['quantity'],
        'price' => $order_data['total_price'],
        'status' => ($api_response && isset($api_response['order'])) ? 'pending' : 'failed',
        'api_response' => $api_response,
        'date' => date('Y-m-d H:i:s'),
        'order_number' => $order_number,
        'smm_order_id' => $smm_order_id,
        'remaining' => $order_data['quantity'], // الكمية المتبقية
        'start_count' => 0, // العدد المبدئي
        'current_status' => 'في الانتظار' // الحالة الافتراضية
    ];
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // إرسال إشعار التفعيل إلى القناة
    $category_name = $categories[$service['category']] ?? 'غير محدد';
    $activation_data = [
        'order_id' => $order_number,
        'category' => $category_name,
        'service' => $service['name'],
        'quantity' => $order_data['quantity'],
        'price' => $order_data['total_price'],
        'user_id' => $from_id,
        'link' => $order_data['link']
    ];
    sendActivationNotification($activation_data);
    
// إرسال إشعار للمالك
$user_info = $users[$from_id] ?? [];
$username = isset($user_info['username']) ? escapeMarkdown($user_info['username']) : 'بدون معرف';
$first_name = isset($user_info['first_name']) ? escapeMarkdown($user_info['first_name']) : 'بدون اسم';

$order_notification = "*🛒 طلب جديد*\n\n";
$order_notification .= "👤 المستخدم: " . $first_name . "\n";
$order_notification .= "🆔 المعرف: `" . escapeMarkdown($from_id) . "`\n";
$order_notification .= "📦 الخدمة: " . escapeMarkdown($service['name']) . "\n";
$order_notification .= "🔗 الرابط: " . $order_data['link'] . "\n"; // ← الرابط بدون هروب
$order_notification .= "📊 الكمية: " . escapeMarkdown($order_data['quantity']) . "\n";
$order_notification .= "💰 السعر: " . convertCurrency($order_data['total_price'], $from_id) . "\n";
$order_notification .= "🆔 رقم الطلب: `" . escapeMarkdown($order_id) . "`\n";
$order_notification .= "#️⃣ رقم التسلسل: `" . escapeMarkdown($order_number) . "`\n";
$order_notification .= "📅 التاريخ: " . escapeMarkdown(date('Y-m-d H:i:s')) . "\n";
$order_notification .= "📊 حالة API: " . (($api_response && isset($api_response['order'])) ? '✅ ناجح' : '❌ فاشل');

// إرسال الإشعار لجميع الأدمن
global $admins;
foreach ($admins as $admin_id) {
    sendMessage($admin_id, $order_notification);
}
    
    // إرسال تأكيد للمستخدم بالشكل الجديد
    $converted_price = convertCurrency($order_data['total_price'], $from_id);
    
    // بناء النص الجديد
    $confirm_text = "*✅ - تم تنفيذ الطلب بنجاح !*\n\n";
    $confirm_text .= "*♻️︙الخدمة: {$service['name']}*\n";
    $confirm_text .= "*📦︙الكمية: {$order_data['quantity']}*\n";
    $confirm_text .= "*💰︙السعر الكلي: {$converted_price}*\n";
    $confirm_text .= "*🧾︙رقم الطلب: {$order_number}*\n";
    $confirm_text .= "*🆔︙معرف الطلب: {$smm_order_id}*\n";
    $confirm_text .= "*🔗︙الرابط: {$order_data['link']}*\n\n";
    $confirm_text .= "*- حالة الطلب في الاسفل⬇️⬇️*\n\n";
    $confirm_text .= "*🏷︙العدد المطلوب: {$order_data['quantity']}*\n";
    $confirm_text .= "*📊︙العدد المكتمل: 0*\n";
    $confirm_text .= "*🅿️︙العدد المتبقي: {$order_data['quantity']}*\n";
    $confirm_text .= "*🔘︙الحاله:في الانتضار♻️*\n\n";
    $confirm_text .= "*🔄︙تحديث حالة الطلب عبر زر  [ ♻️ التحديث ]  في الاسفل.*";
    
    // حفظ بيانات الطلب للاستخدام في التحديث
    $steps[$from_id] = [
        'step' => 'order_tracking',
        'order_id' => $order_id,
        'last_update' => time()
    ];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // استخدام زر التحديث مع إخفاء معاينة الرابط
    $buttons = [[
        ['text' => "♻️ - تحديث الطلب", 'callback_data' => "refresh_order_$order_id"]
    ]];
    
    // استخدام editMessageText مباشرة مثل دالة التحديث
    $edit_data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $confirm_text,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode(['inline_keyboard' => $buttons])
    ];
    
    $result = bot("editMessageText", $edit_data);
    return handleApiError($result, "editMessage");
}
// معالجة تحويل الرصيد
function handleTransferConfirmation($chat_id, $message_id, $from_id, $data) {
    global $steps, $stepsFile, $users, $usersFile;
    
    $target_user = str_replace("confirm_transfer_", "", $data);
    if ($target_user != $from_id) {
        return;
    }
    
    if (!isset($steps[$from_id]) || $steps[$from_id]['step'] != 'transfer_confirm') {
        editMessage($chat_id, $message_id, "❌ لا توجد عملية تحويل لتأكيدها.");
        return;
    }
    
    $transfer_data = $steps[$from_id];
    $to_user = $transfer_data['to_user'];
    $amount = $transfer_data['amount'];
    $fee = $transfer_data['fee'];
    $net_amount = $transfer_data['net_amount'];
    
    // التحقق من رصيد المرسل
    $from_balance = getBalance($from_id);
    if ($from_balance < $amount) {
        editMessage($chat_id, $message_id, "❌ رصيدك غير كافي لإتمام عملية التحويل.");
        unset($steps[$from_id]);
        file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return;
    }
    
    // خصم المبلغ من المرسل
    subtractBalance($from_id, $amount);
    
    // إضافة المبلغ الصافي للمستقبل
    addBalance($to_user, $net_amount);
    
    // إرسال إشعار للمرسل
    $new_balance = getBalance($from_id);
    $converted_new_balance = convertCurrency($new_balance, $from_id);
    
    $sender_text = "*✅ - تم تحويل مبلغ {$amount}$ للمستخدم {$to_user} بنجاح✔️*\n";
    $sender_text .= "*رصيدك بعد التحويل: {$converted_new_balance}*";
    
    editMessage($chat_id, $message_id, $sender_text);
    
    // إرسال إشعار للمستقبل
    $to_user_balance = getBalance($to_user);
    $converted_to_balance = convertCurrency($to_user_balance, $to_user);
    
    $receiver_text = "*☑️ - تم استلام مبلغ {$net_amount}$ دولار*\n\n";
    $receiver_text .= "*🌐 - من المستخدم: {$from_id}*\n";
    $receiver_text .= "*💰 - رصيدك الان: {$converted_to_balance}*";
    
    sendMessage($to_user, $receiver_text);
    
    // إرسال إشعار للإدمن بالتحويل
    $admin_notification = "*💰 إشعار تحويل رصيد*\n\n";
    $admin_notification .= "👤 *المحول:* {$from_id}\n";
    $admin_notification .= "👥 *المستلم:* {$to_user}\n";
    $admin_notification .= "💵 *المبلغ:* {$amount}$\n";
    $admin_notification .= "💸 *العمولة:* {$fee}$\n";
    $admin_notification .= "✅ *المبلغ الصافي:* {$net_amount}$\n";
    $admin_notification .= "📅 *التاريخ:* " . date('Y-m-d H:i:s') . "\n";
    $admin_notification .= "🆔 *رقم العملية:* " . uniqid();
    
    // إرسال الإشعار لجميع الأدمن
    global $admins;
    foreach ($admins as $admin_id) {
        sendMessage($admin_id, $admin_notification);
    }
    
    unset($steps[$from_id]);
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
// معالجة تحديث حالة الطلب
function handleOrderRefresh($chat_id, $message_id, $from_id, $data) {
    global $orders, $ordersFile, $steps, $stepsFile;
    
    $order_id = str_replace("refresh_order_", "", $data);
    
    if (!isset($orders[$order_id]) || $orders[$order_id]['user_id'] != $from_id) {
        editMessage($chat_id, $message_id, "❌ لم نتمكن من العثور على الطلب.");
        return;
    }
    
    $order = $orders[$order_id];
    
    // التحقق إذا كانت حالة الطلب مكتملة
    if ($order['current_status'] == 'مكتمل ✅') {
        answerCallback($data, "⚠️ - أصبح حالة الطلب مكتمل✅. لايمكنك تحديث الطلب.", true);
        return;
    }
    
    // تحديث حالة الطلب من SMM API
    $params = [
        'action' => 'status',
        'order' => $order['smm_order_id']
    ];
    
    $status_response = smmRequest($params, $order['site_id']);
    
    // تحديث بيانات الطلب بناءً على الاستجابة
    $remaining = $order['quantity'];
    $start_count = 0;
    $current_status = 'في الانتضار⏳';
    
    if ($status_response && isset($status_response['status'])) {
        // تحليل استجابة حالة الطلب
        $api_status = $status_response['status'];
        $remaining = $status_response['remains'] ?? $order['quantity'];
        $start_count = $status_response['start_count'] ?? 0;
        
        // تحويل حالة API إلى نص عربي مع الرموز الجديدة
        switch($api_status) {
            case 'Pending':
                $current_status = 'في الانتضار⏳';
                break;
            case 'Processing':
            case 'In progress':
                $current_status = 'قيد التقدم ♻️';
                break;
            case 'Completed':
                $current_status = 'مكتمل ✅';
                break;
            case 'Partial':
                $current_status = 'جزئي🪫';
                break;
            case 'Cancelled':
                $current_status = 'ملغي ⛔️';
                break;
            default:
                $current_status = 'في الانتضار⏳';
        }
    }
    
    // تحديث بيانات الطلب
    $orders[$order_id]['remaining'] = $remaining;
    $orders[$order_id]['start_count'] = $start_count;
    $orders[$order_id]['current_status'] = $current_status;
    $orders[$order_id]['last_check'] = date('Y-m-d H:i:s');
    
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // تنسيق التاريخ والوقت حسب التوقيت السعودي (12 ساعة)
    $saudi_time = getSaudiDateTime();
    
    // بناء نص التحديث الجديد
    $converted_price = convertCurrency($order['price'], $from_id);
    $delivered = $order['quantity'] - $remaining;
    
    $refresh_text = "*✅ - تم تنفيذ الطلب بنجاح !*\n\n";
    $refresh_text .= "*♻️︙الخدمة: {$order['service_name']}*\n";
    $refresh_text .= "*📦︙الكمية: {$order['quantity']}*\n";
    $refresh_text .= "*💰︙السعر الكلي: {$converted_price}*\n";
    $refresh_text .= "*🧾︙رقم الطلب: {$order['order_number']}*\n";
    $refresh_text .= "*🆔︙معرف الطلب: {$order['smm_order_id']}*\n";
    $refresh_text .= "*🔗︙الرابط: {$order['link']}*\n\n";
    $refresh_text .= "*- حالة الطلب في الاسفل⬇️⬇️*\n\n";
    $refresh_text .= "*🏷︙العدد المطلوب: {$order['quantity']}*\n";
    $refresh_text .= "*📊︙العدد المكتمل: {$delivered}*\n";
    $refresh_text .= "*🅿️︙العدد المتبقي: {$remaining}*\n";
    $refresh_text .= "*🔘︙الحاله: {$current_status}*\n";
    $refresh_text .= "*🔰︙اخر تحديث: {$saudi_time}*\n\n";
    $refresh_text .= "*🔄︙تحديث حالة الطلب عبر زر  [ ♻️ التحديث ]  في الاسفل.*";
    
    // التحقق إذا كانت الحالة أصبحت مكتملة
    if ($current_status == 'مكتمل ✅') {
        $buttons = [[
            ['text' => "✅︙الطلب متكمل.", 'callback_data' => "no_action"]
        ]];
        // إرسال همسة خاصة للحالة المكتملة
        answerCallback($data, "🎉 تم اكتمال الطلب بنجاح!", true);
    } else {
        $buttons = [[
            ['text' => "♻️ - تحديث الطلب", 'callback_data' => "refresh_order_$order_id"]
        ]];
        // إرسال همسة تأكيد التحديث العادية
        answerCallback($data, "✅ تم تحديث الطلب", true);
    }
    
    // استخدام editMessage مع تعطيل معاينة الرابط
    $edit_data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $refresh_text,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode(['inline_keyboard' => $buttons])
    ];
    
    $result = bot("editMessageText", $edit_data);
    return handleApiError($result, "editMessage");
}

// دالة للحصول على التاريخ والوقت السعودي (12 ساعة)
function getSaudiDateTime() {
    // إنشاء كائن وقت مع التوقيت السعودي
    $saudi_timezone = new DateTimeZone('Asia/Riyadh');
    $now = new DateTime('now', $saudi_timezone);
    
    // أسماء الأيام بالعربية
    $arabic_days = [
        'Sunday' => 'الأحد',
        'Monday' => 'الاثنين', 
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس',
        'Friday' => 'الجمعة',
        'Saturday' => 'السبت'
    ];
    
    // أسماء الأشهر بالعربية
    $arabic_months = [
        'January' => 'يناير',
        'February' => 'فبراير',
        'March' => 'مارس',
        'April' => 'أبريل',
        'May' => 'مايو',
        'June' => 'يونيو',
        'July' => 'يوليو',
        'August' => 'أغسطس',
        'September' => 'سبتمبر',
        'October' => 'أكتوبر',
        'November' => 'نوفمبر',
        'December' => 'ديسمبر'
    ];
    
    // الحصول على اليوم والشهر
    $english_day = $now->format('l');
    $english_month = $now->format('F');
    
    $arabic_day = $arabic_days[$english_day] ?? $english_day;
    $arabic_month = $arabic_months[$english_month] ?? $english_month;
    
    // تنسيق الوقت بنظام 12 ساعة
    $hour_12 = $now->format('g'); // ساعة بدون أصفار (1-12)
    $minute = $now->format('i'); // دقائق
    $ampm = $now->format('A') == 'AM' ? 'ص' : 'م'; // تحويل AM/PM إلى ص/م
    
    // تنسيق التاريخ النهائي
    $day_number = $now->format('j'); // يوم الشهر بدون أصفار
    $formatted_date = "🗓️ {$arabic_day}، {$day_number} {$arabic_month} {$hour_12}:{$minute} {$ampm}";
    
    return $formatted_date;
}
function handleTransferCancel($chat_id, $message_id, $from_id) {
    global $steps, $stepsFile;
    
    unset($steps[$from_id]);
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    editMessage($chat_id, $message_id, "*✅ - تم إلغاء عملية التحويل بنجاح.*", [[
        ['text' => getLang('back_button'), 'callback_data' => "back_home"]
    ]]);
}
?>