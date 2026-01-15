<?php
require_once 'config.php';
require_once 'functions.php';

// التحقق من الاشتراك في القنوات الإجبارية
function checkSubscription($user_id) {
    global $forcedChannels, $private_channel_id, $private_channel_link;
    
    $missing_channels = [];
    
    // التحقق من القناة الخاصة الافتراضية
    if ($private_channel_id && !is_subscribed($private_channel_id, $user_id)) {
        $missing_channels[] = [
            'name' => 'القناة الرئيسية',
            'link' => $private_channel_link,
            'id' => $private_channel_id
        ];
    }
    
    // التحقق من القنوات الإضافية
    foreach ($forcedChannels as $channel) {
        if (!is_subscribed($channel['id'], $user_id)) {
            $missing_channels[] = $channel;
        }
    }
    
    if (empty($missing_channels)) {
        return ['subscribed' => true, 'missing_channels' => []];
    } else {
        return ['subscribed' => false, 'missing_channels' => $missing_channels];
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
            // إذا تمت معالجة الإحالة، لا نعرض أي رسالة أخرى
            // نظام الإحالة سيتولى إرسال الرسائل المناسبة
            return;
        }
        
        // إذا لم تكن هناك إحالة، نعرض الرسالة العادية
        editMessage($chat_id, $message_id, "✅ *تم التحقق من الاشتراك بنجاح!*\n\nيمكنك الآن استخدام البوت.", [[
            ['text' => "🚀 بدء الاستخدام", 'callback_data' => "back_home"]
        ]]);
        
        // إظهار القائمة الرئيسية
        showMainMenu($chat_id, $user_id);
    } else {
        // لم يتم الاشتراك في جميع القنوات
        editMessage($chat_id, $message_id, "❌ *لم تنضم بعد لجميع القنوات المطلوبة*\n\nيرجى الانضمام للقنوات ثم اضغط على زر التحقق:", 
            array_merge(
                array_map(function($channel) {
                    return [['text' => "انضم إلى {$channel['name']}", 'url' => $channel['link']]];
                }, $subscription['missing_channels']),
                [[['text' => getLang('check_subscription_button'), 'callback_data' => "verify_sub"]]]
            )
        );
    }
}
?>