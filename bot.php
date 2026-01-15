<?php
// تفعيل تسجيل الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'bot_errors.log');

require_once 'config.php';
require_once 'functions.php';
require_once 'user_handlers.php';
require_once 'admin_handlers.php';

// تعريف الدالة إذا لم تكن موجودة (حل احتياطي)
if (!function_exists('handleAdminCallbackData')) {
    function handleAdminCallbackData($chat_id, $message_id, $from_id, $data, $callback) {
        // معالجة كالبات المدير العامة
        if (function_exists('handleAdminCallback')) {
            handleAdminCallback($chat_id, $message_id, $from_id, $data, $callback);
        }
        
        // معالجة كالبات إدارة الخدمات
        if (function_exists('handleAdminServiceCallbacks')) {
            handleAdminServiceCallbacks($chat_id, $message_id, $from_id, $data, $callback);
        }
    }
}

// تحميل البيانات
loadData();

// تنظيف الخطوات القديمة
cleanupOldSteps();

$update = json_decode(file_get_contents("php://input"), true);
if (!$update) {
    exit;
}

$message = $update["message"] ?? null;
$callback = $update["callback_query"] ?? null;

$chat_id = $message["chat"]["id"] ?? $callback["message"]["chat"]["id"] ?? null;
$from_id = $message["from"]["id"] ?? $callback["from"]["id"] ?? null;
$message_id = $callback["message"]["message_id"] ?? null;

$text = $message["text"] ?? null;
$data = $callback["data"] ?? null;

// التحقق من البيانات الأساسية
if (!$chat_id || !$from_id) {
    exit;
}

// التحقق من الحظر
if (isUserBanned($from_id)) {
    sendMessage($chat_id, getLang('you_are_banned'));
    exit;
}

// التحقق من الاشتراك الإجباري أولاً (للمستخدمين العاديين فقط)
if (!isAdmin($from_id)) {
    $subscription = checkSubscription($from_id);
    if (!$subscription['subscribed']) {
        // إذا كان زر التحقق من الاشتراك
        if ($data == "verify_sub" || $data == "check_subscription") {
            handleSubscriptionVerification($chat_id, $message_id, $from_id);
            exit;
        } 
        // إذا كان أمر /start مع إحالة - السماح بمعالجة الإحالة أولاً
        elseif ($text && strpos($text, "/start") === 0) {
            require_once 'referral_system.php';
            $is_referral = ReferralSystem::handleReferralStart($chat_id, $from_id, $message);
            
            // إذا كانت إحالة، نستمر في معالجتها حتى مع وجود اشتراك إجباري
            if ($is_referral) {
                // لا نخرج هنا، نترك النظام يكمل معالجة الإحالة
                // سيتم التعامل مع الاشتراك الإجباري لاحقاً في نظام الإحالة
            } else {
                // إذا لم تكن إحالة، نعرض رسالة الاشتراك الإجباري
                sendSubscriptionMessage($chat_id, $subscription['missing_channels']);
                exit;
            }
        }
        // أي حالة أخرى
        else {
            sendSubscriptionMessage($chat_id, $subscription['missing_channels']);
            exit;
        }
    }
}

// معالجة أمر /start أولاً (مع الإحالة والاشتراك الإجباري)
if ($text && strpos($text, "/start") === 0) {
    // التحقق أولاً إذا كان دخول عبر رابط إحالة
    require_once 'referral_system.php';
    if (ReferralSystem::handleReferralStart($chat_id, $from_id, $message)) {
        exit; // تمت معالجة الإحالة، لا تتابع
    }
    
    // إذا لم يكن هناك إحالة، ابدأ العملية العادية
    handleStart($chat_id, $from_id, $message);
    exit;
}

// معالجة الأوامر النصية الأخرى
if ($text) {
    if ($text == "/admin" && isAdmin($from_id)) {
        showAdminPanel($chat_id);
        exit;
    }
    
    // معالجة الردود على رسائل الدعم (للمدير فقط)
    if (isAdmin($from_id) && isset($message['reply_to_message'])) {
        $reply_text = $message['reply_to_message']['text'] ?? '';
        if (strpos($reply_text, '📩 رسالة دعم جديدة') !== false) {
            // استخراج معرف المستخدم من رسالة الدعم
            preg_match('/👤 من: (\d+)/', $reply_text, $matches);
            if (isset($matches[1])) {
                $target_user = $matches[1];
                sendMessage($target_user, "*👨‍💻 رد من الدعم الفني:*\n\n{$text}");
                sendMessage($chat_id, "*✅ تم إرسال الرد للمستخدم* `{$target_user}`");
            }
            exit;
        }
    }
    
    // معالجة الخطوات - للمستخدمين العاديين والأدمن
    if (isset($steps[$from_id]['step'])) {
        // إذا كان أدمن ومعالجة خطوات الأدمن
        if (isAdmin($from_id) && strpos($steps[$from_id]['step'], 'admin_') === 0) {
            handleAdminStep($chat_id, $from_id, $text, $message);
        } else {
            // معالجة خطوات المستخدمين العاديين
            handleSteps($chat_id, $from_id, $text, $message);
        }
        exit;
    }
    
    // إذا لم يكن هناك خطوة نشطة ولا نص معروف، لا نقوم بأي شيء
    // هذا يمنع ظهور القائمة الرئيسية تلقائياً عند إرسال أي رسالة عشوائية
    if (!isAdmin($from_id)) {
        // يمكن إضافة رسالة توضيحية هنا إذا أردت
        // sendMessage($chat_id, "⚠️ لم أفهم طلبك. يرجى استخدام الأزرار أو الأوامر المتاحة.");
    }
}

// معالجة الردود (Callbacks)
if ($data && $callback) {
    $callback_id = $callback['id'];
    
    // الرد على الكallback أولاً
    answerCallback($callback_id);
    
    // تسجيل للتصحيح
    error_log("Callback received: " . $data . " from user: " . $from_id);
    
    // معالجة التحقق من الاشتراك أولاً
    if ($data == "verify_sub" || $data == "check_subscription") {
        handleSubscriptionVerification($chat_id, $message_id, $from_id);
        exit;
    }
    
    // معالجة كالبات الأدمن أولاً
    if (isAdmin($from_id)) {
        handleAdminCallbackData($chat_id, $message_id, $from_id, $data, $callback_id);
        exit;
    }
    
    // معالجة كالبات المستخدمين العاديين
    switch($data) {
        case "back_home": 
            handleBackHome($chat_id, $message_id, $from_id, $callback); 
            break;
            case 'referral_math_verification':
    require_once 'referral_system.php';
    ReferralSystem::handleReferralMathAnswer($chat_id, $from_id, $text);
    break;
        case "list_services": 
            handleListServices($chat_id, $message_id); 
            break;
        case "recharge": 
            handleRecharge($chat_id, $message_id); 
            break;
        case "redeem_card": 
            handleRedeemCard($chat_id, $message_id, $from_id); 
            break;
        case "change_currency": 
            handleChangeCurrency($chat_id, $message_id); 
            break;
        case "statistics": 
            handleStatistics($chat_id, $message_id, $from_id); 
            break;
        case "transfer_balance": 
            handleTransferBalance($chat_id, $message_id, $from_id); 
            break;
        case "referral": 
            handleReferral($chat_id, $message_id, $from_id); 
            break;
        case "prepare_ad": 
            require_once 'referral_system.php';
            ReferralSystem::prepareAd($chat_id, $message_id, $from_id); 
            break;
        case "referral_stats": 
            require_once 'referral_system.php';
            ReferralSystem::showReferralStats($chat_id, $message_id, $from_id); 
            break;
        case "copy_referral": 
            require_once 'referral_system.php';
            $referral_link = ReferralSystem::getReferralLink($from_id);
            editMessage($chat_id, $message_id, "*✅ تم نسخ الرابط بنجاح\\!*\n\n*🔗 رابطك:*\n`{$referral_link}`", [[
                ['text' => getLang('back_button'), 'callback_data' => "referral"]
            ]]); 
            break;
        case "change_referral_reward": 
            if (isAdmin($from_id)) {
                global $steps, $stepsFile;
                $steps[$from_id] = ['step' => 'admin_set_referral_bonus'];
                file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                editMessage($chat_id, $message_id, "*⚙️ أدخل مكافأة الإحالة الجديدة \\(\\$\\):*", [[
                    ['text' => getLang('back_button'), 'callback_data' => "referral"]
                ]]);
            }
            break;
        case "instructions": 
            handleInstructions($chat_id, $message_id); 
            break;
        case "bot_channel":
            handleBotChannel($chat_id, $message_id);
            break;
        case "orders_channel":
            handleOrdersChannel($chat_id, $message_id);
            break;
        case "support":
            handleSupport($chat_id, $message_id, $from_id);
            break;
            
        // معالجة تأكيد التحويل
        case strpos($data, "confirm_transfer_") === 0:
            handleTransferConfirmation($chat_id, $message_id, $from_id, $data);
            break;
        case "cancel_transfer":
            handleTransferCancel($chat_id, $message_id, $from_id);
            break;
            
        default:
            // معالجة الكالبات الأخرى (أقسام، خدمات، تأكيد طلب، إلخ)
            handleCallbackData($chat_id, $message_id, $from_id, $data, $callback);
            break;
    }
    exit;
}

// معالجة الأوامر الإدارية النصية
if (isAdmin($from_id) && $text) {
    handleAdminCommands($chat_id, $text, $message);
    exit;
}

// لا نقوم بأي شيء إضافي هنا لمنع ظهور القائمة الرئيسية تلقائياً
?>