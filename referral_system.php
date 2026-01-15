<?php
require_once 'config.php';
require_once 'functions.php';

// نظام الإحالة المنفصل
class ReferralSystem {
    
public static function handleReferralStart($chat_id, $from_id, $message) {
    global $users, $usersFile;
    
    // استخراج كود الإحالة
    $referral_code = self::extractReferralCode($message['text'] ?? '');
    
    if (!$referral_code) {
        return false;
    }
    
    // التحقق من الاشتراك الإجباري أولاً
    $subscription = checkSubscription($from_id);
    if (!$subscription['subscribed']) {
        // إذا لم يكن مشتركاً، نحفظ كود الإحالة ونطلب الاشتراك
        $user_data = getUserData($from_id);
        $users[$from_id]['pending_referral'] = $referral_code;
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // إرسال رسالة الاشتراك الإجباري
        sendSubscriptionMessage($chat_id, $subscription['missing_channels']);
        return true; // نعود بـ true للإشارة أن هناك إحالة معلقة
    }
    
    // إذا كان مشتركاً، نكمل العملية العادية
    // تسجيل المستخدم الجديد
    getUserData($from_id);
    
    // حفظ كود الإحالة مؤقتاً
    $users[$from_id]['pending_referral'] = $referral_code;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // بدء التحقق الرياضي للإحالة
    self::startReferralVerification($chat_id, $from_id, $referral_code);
    return true;
}
    // استخراج كود الإحالة
    private static function extractReferralCode($text) {
        if (strpos($text, '/start ') !== false) {
            $parts = explode(' ', $text);
            if (count($parts) >= 2) {
                $code = $parts[1];
                // التحقق إذا كان الكود بطول 8 أحرف (كود الإحالة الجديد)
                if (strlen($code) == 8 && preg_match('/^[A-Za-z0-9]+$/', $code)) {
                    return $code;
                }
            }
        }
        return null;
    }
    
// بدء التحقق الرياضي للإحالة
public static function startReferralVerification($chat_id, $user_id, $referral_code) {
    global $mathVerification, $mathVerificationFile, $steps, $stepsFile;
    
    // إنشاء مسألة رياضية
    $math_data = self::generateMathProblem();
    $math_data['referral_code'] = $referral_code;
    $math_data['type'] = 'referral';
    
    // حفظ بيانات التحقق
    $mathVerification[$user_id] = $math_data;
    file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // إعداد الخطوة
    $steps[$user_id] = [
        'step' => 'referral_math_verification',
        'timestamp' => time()
    ];
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // إرسال المسألة الرياضية - استخدام editMessage بدلاً من sendMessage
$message = "*يرجى التحقق انك لست روبوت ♻️*\n\n";
$message .= "*☑️ - يرجى اكمال التحقق من خلال إرسال الإجابة الصحيحة لهذه المسألة ⬇️*\n\n";
$message .= "*✳️ - السؤال:* " . $math_data['problem'] . "\n\n";
$message .= "*أرسل الإجابة الآن:*";

sendMessage($chat_id, $message);
}
    
// إنشاء مسألة رياضية سهلة
private static function generateMathProblem() {
    $operations = ['+', '-', '*'];
    $operation = $operations[array_rand($operations)];
    
    switch($operation) {
        case '+':
            $num1 = rand(1, 10);  // تقليل النطاق من 1-10
            $num2 = rand(1, 10);  // تقليل النطاق من 1-10
            $answer = $num1 + $num2;
            $problem = "$num1 + $num2";
            break;
        case '-':
            $num1 = rand(5, 15);  // تقليل النطاق من 5-15
            $num2 = rand(1, $num1 - 1); // التأكد من أن النتيجة موجبة
            $answer = $num1 - $num2;
            $problem = "$num1 - $num2";
            break;
        case '*':
            $num1 = rand(1, 6);   // تقليل النطاق من 1-6
            $num2 = rand(1, 6);   // تقليل النطاق من 1-6
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
    public static function handleReferralMathAnswer($chat_id, $user_id, $answer) {
        global $users, $usersFile, $mathVerification, $mathVerificationFile, $steps, $stepsFile;
        
        $math_data = $mathVerification[$user_id] ?? null;
        if (!$math_data || ($math_data['type'] ?? '') != 'referral') {
            sendMessage($chat_id, "❌ لم نتمكن من العثور على بيانات التحقق\\. يرجى إعادة المحاولة\\.");
            return;
        }
        
        $user_answer = intval($answer);
        $correct_answer = $math_data['answer'];
        $referral_code = $math_data['referral_code'] ?? null;
        
        if ($user_answer == $correct_answer) {
            // نجح التحقق - معالجة الإحالة
            self::processSuccessfulReferral($chat_id, $user_id, $referral_code);
            
            // تنظيف الخطوة بعد النجاح
            if (isset($steps[$user_id])) {
                unset($steps[$user_id]);
                file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
        } else {
            // فشل التحقق
            $math_data['attempts']++;
            $mathVerification[$user_id] = $math_data;
            file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            if ($math_data['attempts'] >= 3) {
                // تجاوز الحد الأقصى للمحاولات
                unset($mathVerification[$user_id]);
                file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                // تنظيف الخطوة
                if (isset($steps[$user_id])) {
                    unset($steps[$user_id]);
                    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                
                sendMessage($chat_id, "❌ لقد تجاوزت الحد الأقصى للمحاولات\\. يرجى إعادة المحاولة باستخدام /start");
            } else {
                sendMessage($chat_id, "❌ إجابة خاطئة\\! حاول مرة أخرى:\n\n🔢 " . $math_data['problem']);
            }
        }
    }
    
    // معالجة الإحالة الناجحة
    private static function processSuccessfulReferral($chat_id, $user_id, $referral_code) {
        global $users, $usersFile, $settings, $referrals, $referralsFile, $mathVerification, $mathVerificationFile;
        
        // تنظيف بيانات التحقق
        unset($mathVerification[$user_id]);
        file_put_contents($mathVerificationFile, json_encode($mathVerification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // البحث عن صاحب كود الإحالة
        $referrer_id = self::getUserByReferralCode($referral_code);
        
        if ($referrer_id && $referrer_id != $user_id) {
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
            $users[$user_id]['math_verified'] = true;
            
            // تنظيف الإحالة المعلقة
            if (isset($users[$user_id]['pending_referral'])) {
                unset($users[$user_id]['pending_referral']);
            }
            
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
                "*🎁︙قام $user_id بالدخول عبر رابط الإحالة الخاص بك وحصلت على {$bonus}$ دولار

💰︙أصبح رصيدك الان {$referrer_balance}*"
            );
            
$welcome_text = processWelcomeText($user_id);
            
            sendMessage($chat_id, $welcome_text, self::getMainButtons($user_id));
            
        } else {
            // كود إحالة غير صالح
            $users[$user_id]['math_verified'] = true;
            if (isset($users[$user_id]['pending_referral'])) {
                unset($users[$user_id]['pending_referral']);
            }
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
          sendMessage($chat_id, processWelcomeText($user_id), self::getMainButtons($user_id));  
        }
    }
    
    // الحصول على رابط الإحالة
    public static function getReferralLink($user_id) {
        global $users;
        $user_data = getUserData($user_id);
        
        // إنشاء كود إحالة إذا لم يكن موجوداً
        if (!isset($user_data['referral_code']) || empty($user_data['referral_code'])) {
            $user_data['referral_code'] = self::generateReferralCode();
            updateUserData($user_id, ['referral_code' => $user_data['referral_code']]);
        }
        
     // استخدام الدالة الموجودة في functions.php
$bot_username = getBotUsername();
        return "https://t.me/$bot_username?start={$user_data['referral_code']}";
    }
    
    // إنشاء كود إحالة عشوائي
    private static function generateReferralCode() {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }
    
    // الحصول على المستخدم بواسطة كود الإحالة
    private static function getUserByReferralCode($referral_code) {
        global $users;
        
        foreach ($users as $user_id => $user_data) {
            if (isset($user_data['referral_code']) && $user_data['referral_code'] == $referral_code) {
                return $user_id;
            }
        }
        
        return null;
    }
    
// عرض معلومات الإحالة
public static function showReferralInfo($chat_id, $message_id, $user_id) {
    global $settings, $users;
    
    $user_data = getUserData($user_id);
    $bonus = $settings['referral_bonus'] ?? 10;
    $referral_link = self::getReferralLink($user_id);
    $referral_count = $user_data['referral_count'] ?? 0;
    $referral_bonus = $user_data['referral_bonus'] ?? 0;
    $converted_bonus = convertCurrency($referral_bonus, $user_id);
    $converted_bonus_amount = convertCurrency($bonus, $user_id);
    
    $text = "*🏧︙يمكنك الآن الحصول على رصيد مجاني من خلال مشاركة رابط الدعوة الخاص بك 💰*\n\n";
    $text .= "*🔗︙الرابط الخاص بك :* `{$referral_link}`\n\n";
    $text .= "*📘︙شارك رابط الدعوة الخاص بك مع أصدقائك او قنواتك او اي مكان ، واحصل على {$converted_bonus_amount} دولار مجاناً لكل شخص يقوم بالدخول عبر رابطك ☑️*\n\n";
    $text .= "*🚀︙يمكنك الضغط على زر *تجهيز إعلان ♻️ للحصول على إعلان جاهز *حول البوت*\n\n";
    $text .= "*✅︙لقد قمت بدعوة {$referral_count} شخص الى الآن 👥.*\n\n";
    $text .= "*🔰︙أرباحك الى الآن {$converted_bonus}*\n\n";
    
    $buttons = [
        [['text' => "📋 نسخ رابط الإحالة", 'copy_text' => ['text' => $referral_link]]],
        [['text' => "📋 تجهيز إعلان ♻️", 'callback_data' => "prepare_ad"]],
        [['text' => getLang('back_button'), 'callback_data' => "back_home"]]
    ];
    
    // إضافة زر تغيير المكافأة للمدير فقط
    if ($user_id == ADMIN_ID) {
        $buttons[] = [['text' => "⚙️ تغيير مكافأة الدعوة", 'callback_data' => "change_referral_reward"]];
    }
    
    editMessage($chat_id, $message_id, $text, $buttons);
}
// تجهيز الإعلان
public static function prepareAd($chat_id, $message_id, $from_id) {
    $referral_link = self::getReferralLink($from_id);
    
    $ad_text = "🤖 أول *وأفضل بوت في التليجرام* لخدمات تعزيز و زيادة المتابعين والمشاهدات لجميع *مواقع التواصل الإجتماعي 🌟🚀.*

*⚡️ سريع 💰 مجاني 🚀 ضمان 🏆 جودة
👁 جرب مجاناً الآن 👇.*

*🔗 - https://t.me/TurbAPIBot?start=12f98c92*";
    
    // إرسال رسالة جديدة بدلاً من تعديل الرسالة الحالية
    sendMessage($chat_id, $ad_text, null, [[
        ['text' => getLang('back_button'), 'callback_data' => "referral"]
    ]]);
}
    
    // عرض إحصائيات الإحالة
    public static function showReferralStats($chat_id, $message_id, $user_id) {
        global $users, $referrals;
        
        $stats = self::getReferralStatistics($user_id);
        $bonus = self::getReferralBonus();
        
        $text = "*📊︙إحصائيات الدعوة*\n\n";
        $text .= "• عدد المدعوين: {$stats['referral_count']}\n";
        $text .= "• إجمالي الأرباح: " . convertCurrency($stats['referral_bonus'], $user_id) . "\n";
        $text .= "• مكافأة كل دعوة: {$bonus}\\$\n\n";
        
        if (!empty($stats['recent_referrals'])) {
            $text .= "*👥︙آخر المدعوين:*\n";
            foreach ($stats['recent_referrals'] as $index => $referral) {
                $text .= ($index + 1) . "\\. `{$referral['referred_id']}` \\- {$referral['date']}\n";
            }
        } else {
            $text .= "*⚠️︙لم تقم بدعوة أي مستخدم حتى الآن*\n";
            $text .= "شارك رابط الدعوة لبدء كسب المكافآت\\!";
        }
        
        $buttons = [
            [['text' => "📎 الحصول على رابط الدعوة", 'callback_data' => "referral"]],
            [['text' => getLang('back_button'), 'callback_data' => "referral"]]
        ];
        
        editMessage($chat_id, $message_id, $text, $buttons);
    }
    
    // الحصول على إحصائيات الإحالة
    public static function getReferralStatistics($user_id) {
        global $users, $referrals;
        
        $user_data = getUserData($user_id);
        $stats = [
            'referral_count' => $user_data['referral_count'] ?? 0,
            'referral_bonus' => $user_data['referral_bonus'] ?? 0,
            'referral_link' => self::getReferralLink($user_id)
        ];
        
        // الحصول على آخر الإحالات
        $stats['recent_referrals'] = [];
        if (!empty($referrals)) {
            $user_referrals = array_filter($referrals, function($ref) use ($user_id) {
                return $ref['referrer_id'] == $user_id;
            });
            
            $stats['recent_referrals'] = array_slice(array_reverse($user_referrals), 0, 5); // آخر 5 إحالات
        }
        
        return $stats;
    }
    
    // الحصول على مكافأة الإحالة
    public static function getReferralBonus() {
        global $settings;
        return $settings['referral_bonus'] ?? 10;
    }
    
    // تعيين مكافأة الإحالة
    public static function setReferralBonus($bonus) {
        global $settings, $settingsFile;
        $settings['referral_bonus'] = floatval($bonus);
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    
    // التحقق من صحة كود الإحالة
    public static function isValidReferralCode($referral_code) {
        global $users;
        return self::getUserByReferralCode($referral_code) !== null;
    }
    
    // دالة مساعدة للحصول على أزرار القائمة الرئيسية
    private static function getMainButtons($user_id) {
        $buttons = [
            [['text' => "🛍️ قائمة الخدمات", 'callback_data' => "list_services"]],
            [['text' => "💰 شحن الرصيد", 'callback_data' => "recharge"], ['text' => "💳 شحن كرت", 'callback_data' => "redeem_card"]],
            [['text' => "💱 تغيير العمله", 'callback_data' => "change_currency"], ['text' => "📊 الإحصائيات", 'callback_data' => "statistics"]],
            [['text' => "👥 رابط الاحاله", 'callback_data' => "referral"]],
            [['text' => "🔄 تحويل رصيد", 'callback_data' => "transfer_balance"], ['text' => "❓ التعليمات", 'callback_data' => "instructions"]],
            [['text' => "📢 قناه البوت", 'callback_data' => "bot_channel"], ['text' => "🛒 قناه الطلبات", 'callback_data' => "orders_channel"]],
            [['text' => "👨‍💻 الدعم الفني", 'callback_data' => "support"]]
        ];
        
        // إضافة لوحة التحكم للمدير فقط
        if ($user_id == ADMIN_ID) {
            $buttons[] = [['text' => "👑 لوحة التحكم", 'callback_data' => "admin_panel"]];
        }
        
        return $buttons;
    }
}

// دوال مساعدة للتوافق مع النظام القديم

// معالجة الإحالة من خلال النظام القديم
function handleReferralSystem($user_id, $referral_code) {
    return ReferralSystem::isValidReferralCode($referral_code);
}

// الحصول على رابط الإحالة (للتوافق)
function getReferralLink($user_id) {
    return ReferralSystem::getReferralLink($user_id);
}

// عرض معلومات الإحالة (للتوافق)
function showReferralInfo($chat_id, $message_id, $user_id) {
    ReferralSystem::showReferralInfo($chat_id, $message_id, $user_id);
}

// عرض إحصائيات الإحالة (للتوافق)
function showReferralStats($chat_id, $message_id, $user_id) {
    ReferralSystem::showReferralStats($chat_id, $message_id, $user_id);
}

// تجهيز الإعلان (للتوافق)
function prepareAd($chat_id, $message_id, $user_id) {
    ReferralSystem::prepareAd($chat_id, $message_id, $user_id);
}
?>