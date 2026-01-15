<?php
// الإعدادات الأساسية
define("API_KEY", "8278594406:AAHmCjL8k6wfpZvxeNwGyllZti7fBqOPvW4");
define("ADMIN_ID", 8579608026);

// مواقع SMM المتعددة
$smm_sites = [
    1 => [
        'name' => 'الموقع 1',
        'url' => '',
        'api_key' => '',
        'enabled' => true
    ],
    2 => [
        'name' => 'الموقع 2', 
        'url' => '',
        'api_key' => '',
        'enabled' => false
    ],
    3 => [
        'name' => 'الموقع 3',
        'url' => '',
        'api_key' => '',
        'enabled' => false
    ]
];

// قنوات البوت
$bot_channels = [
    'main_channel' => 'https://t.me/+z910V5MmWtthYmU0',
    'orders_channel' => 'https://t.me/+SipGvLIr7q05ZTY8',
    'support_channel' => 'https://t.me/+YrpcXCO5j6Y1NWY0',
    'activations_channel' => 'https://t.me/+zasI-zHFmG9mZmI0' // ← إضافة جديدة
];

// قناة الاشتراك الإجباري
$private_channel_link = "https://t.me/+z910V5MmWtthYmU0";
$private_channel_id = "https://t.me/+YrpcXCO5j6Y1NWY0";

// ملفات البيانات
$servicesFile1 = "services_site1.json";
$servicesFile2 = "services_site2.json";
$servicesFile3 = "services_site3.json";
$stepsFile = "steps.json";
$balancesFile = "balances.json";
$cardsFile = "cards.json";
$welcomeFile = "welcome.json";
$categoriesFile = "categories.json";
$currenciesFile = "currencies.json";
$bannedFile = "banned.json";
$exchangeRatesFile = "exchange_rates.json";
$usersFile = "users.json";
$ordersFile = "orders.json";
$referralsFile = "referrals.json";
$settingsFile = "settings.json";
$instructionsFile = "instructions.json";
$mathVerificationFile = "math_verification.json";
$smmSitesFile = "smm_sites.json";
$botChannelsFile = "bot_channels.json";
$notificationsFile = "notifications.json";
$forcedChannelsFile = "forced_channels.json";
$adminsFile = "admins.json";
$orderCounterFile = "order_counter.json"; // ← إضافة جديدة

// إعدادات افتراضية
$defaultSettings = [
    'transfer_fee' => 5,
    'transfer_min_amount' => 1, // الحد الأدنى للتحويل بالدولار
    'referral_bonus' => 10,
    'recharge_text' => "*💳 لشحن الرصيد تواصل معنا عبر:* @haamadh\n*💵 السعر: كل 1\\$ = 1\\$ بدون عمولة\\.*",
    'math_verification_enabled' => true,
    'new_user_notifications' => true,
    'card_recharge_notifications' => true,
    'activation_channel_enabled' => true // ← إضافة جديدة
];

$defaultInstructions = "*📖 تعليمات استخدام البوت:*\n\n*• قم باختيار الخدمة المطلوبة*\n*• أرسل الرابط المطلوب*\n*• حدد الكمية*\n*• تأكد من الطلب وسيتم التنفيذ*\n\n*للاستفسارات:* @haamadh";

// إنشاء الملفات إذا لم تكن موجودة
function createFiles() {
    global $servicesFile1, $servicesFile2, $servicesFile3, $stepsFile, $balancesFile, $cardsFile, $welcomeFile;
    global $categoriesFile, $currenciesFile, $bannedFile, $exchangeRatesFile;
    global $usersFile, $ordersFile, $referralsFile, $settingsFile, $instructionsFile, $mathVerificationFile;
    global $smmSitesFile, $botChannelsFile, $notificationsFile, $forcedChannelsFile, $adminsFile, $orderCounterFile;
    global $defaultSettings, $defaultInstructions, $smm_sites, $bot_channels;
    
    $files = [
        $servicesFile1 => [],
        $servicesFile2 => [],
        $servicesFile3 => [],
        $stepsFile => [],
        $balancesFile => [],
        $cardsFile => [],
        $welcomeFile => ['text' => "*👋 أهلاً بك عزيزي المستخدم*\n*في بوت خدمات الرشق الموثوقه*\n\n*اختر طلبك من القائمة:*\n*رصيدك الحالي:* {balance}"],
        $categoriesFile => [],
        $currenciesFile => [],
        $bannedFile => [],
        $exchangeRatesFile => [
            'USD' => ['rate' => 1, 'symbol' => '$', 'name' => 'دولار أمريكي'],
            'SAR' => ['rate' => 3.5, 'symbol' => ' ر.س', 'name' => 'ريال سعودي'],
            'YNR' => ['rate' => 530, 'symbol' => ' ر.ي', 'name' => 'ريال يمني'],
            'YSR' => ['rate' => 2900, 'symbol' => ' ر.ي.ج', 'name' => 'ريال يمني جنوبي'],
            'IQD' => ['rate' => 75, 'symbol' => ' د.ع', 'name' => 'دينار عراقي'],
            'EGP' => ['rate' => 50, 'symbol' => ' ج.م', 'name' => 'جنيه مصري'],
        ],
        $usersFile => [],
        $ordersFile => [],
        $referralsFile => [],
        $settingsFile => $defaultSettings,
        $instructionsFile => $defaultInstructions,
        $mathVerificationFile => [],
        $smmSitesFile => $smm_sites,
        $botChannelsFile => $bot_channels,
        $notificationsFile => [],
        $forcedChannelsFile => [],
        $adminsFile => [ADMIN_ID], // الأدمن الأساسي
        $orderCounterFile => 1 // ← إضافة جديدة - يبدأ من 1
    ];
    
    foreach ($files as $file => $default) {
        if (!file_exists($file)) {
            if ($file == $instructionsFile) {
                file_put_contents($file, $default);
            } else {
                file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}

// تحميل البيانات
function loadData() {
    global $servicesFile1, $servicesFile2, $servicesFile3, $stepsFile, $balancesFile, $cardsFile, $welcomeFile;
    global $categoriesFile, $currenciesFile, $bannedFile, $exchangeRatesFile;
    global $usersFile, $ordersFile, $referralsFile, $settingsFile, $instructionsFile, $mathVerificationFile;
    global $smmSitesFile, $botChannelsFile, $notificationsFile, $forcedChannelsFile, $adminsFile, $orderCounterFile;
    global $services1, $services2, $services3, $steps, $balances, $cards, $welcome, $categories, $userCurrencies;
    global $banned, $exchangeRates, $users, $orders, $referrals, $settings, $instructions, $mathVerification;
    global $smm_sites, $bot_channels, $notifications, $forcedChannels, $admins, $orderCounter;
    
    createFiles();
    
    $services1 = json_decode(file_get_contents($servicesFile1), true) ?: [];
    $services2 = json_decode(file_get_contents($servicesFile2), true) ?: [];
    $services3 = json_decode(file_get_contents($servicesFile3), true) ?: [];
    $steps = json_decode(file_get_contents($stepsFile), true) ?: [];
    $balances = json_decode(file_get_contents($balancesFile), true) ?: [];
    $cards = json_decode(file_get_contents($cardsFile), true) ?: [];
    $welcome = json_decode(file_get_contents($welcomeFile), true) ?: [];
    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
    $userCurrencies = json_decode(file_get_contents($currenciesFile), true) ?: [];
    $banned = json_decode(file_get_contents($bannedFile), true) ?: [];
    $exchangeRates = json_decode(file_get_contents($exchangeRatesFile), true) ?: [];
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
    $referrals = json_decode(file_get_contents($referralsFile), true) ?: [];
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
    $instructions = file_exists($instructionsFile) ? file_get_contents($instructionsFile) : "";
    $mathVerification = json_decode(file_get_contents($mathVerificationFile), true) ?: [];
    $smm_sites = json_decode(file_get_contents($smmSitesFile), true) ?: [];
    $bot_channels = json_decode(file_get_contents($botChannelsFile), true) ?: [];
    $notifications = json_decode(file_get_contents($notificationsFile), true) ?: [];
    $forcedChannels = json_decode(file_get_contents($forcedChannelsFile), true) ?: [];
    $admins = json_decode(file_get_contents($adminsFile), true) ?: [];
    $orderCounter = file_exists($orderCounterFile) ? intval(file_get_contents($orderCounterFile)) : 1; // ← إضافة جديدة
}
// حفظ البيانات
function saveData() {
    global $servicesFile1, $servicesFile2, $servicesFile3, $stepsFile, $balancesFile, $cardsFile;
    global $services1, $services2, $services3, $steps, $balances, $cards;
    
    file_put_contents($servicesFile1, json_encode($services1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($servicesFile2, json_encode($services2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($servicesFile3, json_encode($services3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($balancesFile, json_encode($balances, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($cardsFile, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// دالة للحصول على جميع الخدمات من جميع المواقع
function getAllServices() {
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

// دالة للحصول على خدمات موقع معين
function getServicesBySite($site_id) {
    global $services1, $services2, $services3;
    
    switch($site_id) {
        case 1: return $services1;
        case 2: return $services2;
        case 3: return $services3;
        default: return [];
    }
}

// دالة لحفظ خدمات موقع معين
function saveServicesBySite($site_id, $services) {
    global $servicesFile1, $servicesFile2, $servicesFile3, $services1, $services2, $services3;
    
    switch($site_id) {
        case 1:
            $services1 = $services;
            file_put_contents($servicesFile1, json_encode($services1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
        case 2:
            $services2 = $services;
            file_put_contents($servicesFile2, json_encode($services2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
        case 3:
            $services3 = $services;
            file_put_contents($servicesFile3, json_encode($services3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
    }
}

// التحقق إذا كان المستخدم أدمن
function isAdmin($user_id) {
    global $admins;
    return in_array($user_id, $admins);
}

// إضافة أدمن جديد
function addAdmin($user_id) {
    global $admins, $adminsFile;
    if (!in_array($user_id, $admins)) {
        $admins[] = $user_id;
        file_put_contents($adminsFile, json_encode($admins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    return false;
}

// حذف أدمن
function removeAdmin($user_id) {
    global $admins, $adminsFile;
    if ($user_id != ADMIN_ID) { // لا يمكن حذف الأدمن الأساسي
        $admins = array_diff($admins, [$user_id]);
        file_put_contents($adminsFile, json_encode($admins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    return false;
}

// الحصول على قنوات الاشتراك الإجباري
function getForcedChannels() {
    global $forcedChannels;
    return $forcedChannels;
}

// إضافة قناة إجبارية
function addForcedChannel($channel_data) {
    global $forcedChannels, $forcedChannelsFile;
    $channel_id = uniqid();
    
    // تحديد نوع القناة تلقائياً
    if (strpos($channel_data['link'], 'joinchat') !== false || strpos($channel_data['link'], '+') !== false) {
        $channel_data['type'] = 'private';
    } else {
        $channel_data['type'] = 'public';
    }
    
    $forcedChannels[$channel_id] = $channel_data;
    file_put_contents($forcedChannelsFile, json_encode($forcedChannels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $channel_id;
}

// حذف قناة إجبارية
function removeForcedChannel($channel_id) {
    global $forcedChannels, $forcedChannelsFile;
    if (isset($forcedChannels[$channel_id])) {
        unset($forcedChannels[$channel_id]);
        file_put_contents($forcedChannelsFile, json_encode($forcedChannels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    return false;
}

// استخراج معلومات القناة من الرابط
function extractChannelInfo($text) {
    // إذا كان معرف قناة يبدأ بـ -100
    if (preg_match('/^(-100\d+)$/', $text, $matches)) {
        $channel_id = $matches[1];
        return [
            'id' => $channel_id,
            'name' => "قناة خاصة ($channel_id)",
            'link' => "https://t.me/$channel_id",
            'type' => 'private'
        ];
    }
    
    // إذا كان رابط قناة عامة
    if (preg_match('/^@([a-zA-Z0-9_]+)$/', $text, $matches)) {
        $username = $matches[1];
        return [
            'id' => "@$username",
            'name' => $username,
            'link' => "https://t.me/$username",
            'type' => 'public'
        ];
    }
    
    // إذا كان رابط قناة عامة كامل
    if (preg_match('/https?:\/\/t\.me\/([a-zA-Z0-9_]+)/', $text, $matches)) {
        $username = $matches[1];
        return [
            'id' => "@$username",
            'name' => $username,
            'link' => $text,
            'type' => 'public'
        ];
    }
    
    return null;
}

// الحصول على معلومات القناة
function getChannelInfo($channel_link) {
    // محاولة الحصول على معلومات القناة من خلال API
    $channel_info = extractChannelInfo($channel_link);
    if ($channel_info) {
        return $channel_info;
    }
    
    // إذا فشل، استخدام القيم الافتراضية
    return [
        'id' => $channel_link,
        'name' => $channel_link,
        'link' => $channel_link,
        'type' => 'unknown'
    ];
}

// التحقق من الاشتراك في القنوات الخاصة
function is_subscribed_private($channel_id, $user_id) {
    // محاولة استخدام طريقة getChatMember للقنوات الخاصة
    try {
        $chat_member = bot('getChatMember', [
            'chat_id' => $channel_id,
            'user_id' => $user_id
        ]);
        
        if ($chat_member && $chat_member['ok']) {
            $status = $chat_member['result']['status'];
            // الحالات التي تعتبر مشتركاً
            $valid_statuses = ['creator', 'administrator', 'member', 'restricted'];
            return in_array($status, $valid_statuses);
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error checking private channel subscription: " . $e->getMessage());
        return false;
    }
}

// دالة التحقق من الاشتراك في القنوات الإجبارية (محدثة)
function checkSubscription($user_id) {
    global $forcedChannels, $private_channel_id, $private_channel_link;
    
    $missing_channels = [];
    
    // التحقق من القناة الخاصة الافتراضية
    if ($private_channel_id && !is_subscribed($private_channel_id, $user_id)) {
        $missing_channels[] = [
            'name' => 'القناة الرئيسية',
            'link' => $private_channel_link,
            'id' => $private_channel_id,
            'type' => 'public'
        ];
    }
    
    // التحقق من القنوات الإضافية
    foreach ($forcedChannels as $channel_id => $channel) {
        $subscribed = false;
        
        if ($channel['type'] == 'private') {
            // قناة خاصة - استخدام المعرف مباشرة
            $subscribed = is_subscribed_private($channel['id'], $user_id);
        } else {
            // قناة عامة
            $subscribed = is_subscribed($channel['id'], $user_id);
        }
        
        if (!$subscribed) {
            $missing_channels[] = $channel;
        }
    }
    
    if (empty($missing_channels)) {
        return ['subscribed' => true, 'missing_channels' => []];
    } else {
        return ['subscribed' => false, 'missing_channels' => $missing_channels];
    }
}
?>
