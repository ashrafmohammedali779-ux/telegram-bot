<?php
require_once 'config.php';
require_once 'functions.php';

// إظهار لوحة التحكم
function showAdminPanel($chat_id, $message_id = null) {
    $buttons = [
        [
            ['text' => "🛠️ - إدارة الخدمات", 'callback_data' => "admin_manage_services"],
            ['text' => "📁 - إدارة الأقسام", 'callback_data' => "admin_manage_categories"]
        ],
        [
            ['text' => "💰️ - إدارة الرصيد", 'callback_data' => "admin_manage_balance"],
            ['text' => "📋 - قائمة الخدمات", 'callback_data' => "list_services"]
        ],
        [
            ['text' => "🎟️ - إنشاء كروت شحن", 'callback_data' => "admin_generate_card"],
            ['text' => "👋 - تعيين الترحيب", 'callback_data' => "admin_set_welcome"]
        ],
        [
            ['text' => "💱 - إدارة العملات", 'callback_data' => "admin_manage_currencies"],
            ['text' => "🚫 - إدارة الحظر", 'callback_data' => "admin_manage_bans"]
        ],
        [
            ['text' => "📊 - الإحصائيات", 'callback_data' => "statistics"],
            ['text' => "📖 - التعليمات", 'callback_data' => "admin_set_instructions"]
        ],
        [
            ['text' => "💳 - نص شحن الرصيد", 'callback_data' => "admin_set_recharge_text"],
            ['text' => "🔄 - إعدادات التحويل", 'callback_data' => "admin_transfer_settings"]
        ],
        [
            ['text' => "📢 - إعدادات الإحالة", 'callback_data' => "admin_referral_settings"],
            ['text' => "🌐 - إدارة المواقع", 'callback_data' => "admin_manage_sites"]
        ],
        [
            ['text' => "📺 - إدارة القنوات", 'callback_data' => "admin_manage_channels"],
            ['text' => "💬 - رسائل الدعم", 'callback_data' => "admin_support_messages"]
        ],
        [
            ['text' => "🔔 - الاشتراك الإجباري", 'callback_data' => "admin_forced_subscription"],
            ['text' => "👑 - إدارة الأدمن", 'callback_data' => "admin_manage_admins"]
        ],
        [
            ['text' => "⬅️ - الرجوع", 'callback_data' => "back_home"]
        ]
    ];
    
    if ($message_id) {
        editMessage($chat_id, $message_id, "*👨‍💻 - مرحبا بك عزيزي المطور، في لوحه الأدمن 🖤🙂

⚙️- من هنا يمكنك إدارة جميع أقسام البوت بكل سهولة واحترافية.
- تحكم بالازرار من اسفل👇🧑‍🔧*", $buttons);
    } else {
        sendMessage($chat_id, "*👨‍💻 - مرحبا بك عزيزي المطور، في لوحه الأدمن 🖤🙂

⚙️- من هنا يمكنك إدارة جميع أقسام البوت بكل سهولة واحترافية.
- تحكم بالازرار من اسفل👇🧑‍🔧*", $buttons);
    }
}

// معالجة أوامر المدير
function handleAdminCommands($chat_id, $text, $message) {
    global $steps, $stepsFile;
    
    if ($text == "/admin") {
        showAdminPanel($chat_id);
        return;
    }
}

// معالجة كالبات المدير
function handleAdminCallback($chat_id, $message_id, $from_id, $data, $callback) {
    global $steps, $stepsFile;
    
    switch($data) {
        case "admin_panel":
            showAdminPanel($chat_id, $message_id);
            break;
            
        case "admin_manage_services":
            showManageServices($chat_id, $message_id);
            break;
            
        case "admin_manage_categories":
            showManageCategories($chat_id, $message_id);
            break;
            
        case "admin_manage_balance":
            showManageBalance($chat_id, $message_id);
            break;
            
        case "admin_generate_card":
            $steps[$from_id] = ['step' => 'admin_generate_card_amount'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_card_amount'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_panel"]
            ]]);
            break;
            
        case "admin_set_welcome":
            $steps[$from_id] = ['step' => 'admin_set_welcome'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('set_welcome_text'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_panel"]
            ]]);
            break;
            
        case "admin_manage_currencies":
            showManageCurrencies($chat_id, $message_id);
            break;
            
        case "admin_manage_bans":
            showManageBans($chat_id, $message_id);
            break;
            
        case "admin_set_instructions":
            $steps[$from_id] = ['step' => 'admin_set_instructions'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('set_instructions_text'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_panel"]
            ]]);
            break;
            
        case "admin_set_recharge_text":
            $steps[$from_id] = ['step' => 'admin_set_recharge_text'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('set_recharge_text_msg'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_panel"]
            ]]);
            break;
            
        case "admin_transfer_settings":
            showTransferSettings($chat_id, $message_id);
            break;
            
        case "admin_referral_settings":
            showReferralSettings($chat_id, $message_id);
            break;
            
        case "admin_manage_sites":
            showManageSites($chat_id, $message_id);
            break;
            
        case "admin_manage_channels":
            showManageChannels($chat_id, $message_id);
            break;
            
        case "admin_support_messages":
            showSupportMessages($chat_id, $message_id);
            break;
            
        case "admin_forced_subscription":
            showForcedSubscription($chat_id, $message_id);
            break;
            
        case "admin_manage_admins":
            showManageAdmins($chat_id, $message_id);
            break;
            
        // إدارة الخدمات
        case "admin_add_service":
            showAddServiceCategory($chat_id, $message_id);
            break;
            
        case "admin_edit_delete_service":
            showEditDeleteService($chat_id, $message_id);
            break;
            
        // إدارة الأقسام
        case "admin_add_category":
            $steps[$from_id] = ['step' => 'admin_add_category'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_category_name'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_categories"]
            ]]);
            break;
            
        case "admin_delete_category":
            showDeleteCategory($chat_id, $message_id);
            break;
            
        // إدارة الرصيد
        case "admin_add_balance":
            $steps[$from_id] = ['step' => 'admin_add_balance_user'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_user_id_add'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_balance"]
            ]]);
            break;
            
        case "admin_subtract_balance":
            $steps[$from_id] = ['step' => 'admin_subtract_balance_user'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_user_id_subtract'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_balance"]
            ]]);
            break;
            
        // إدارة العملات
        case "admin_add_currency":
            $steps[$from_id] = ['step' => 'admin_add_currency_code'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_currency_code'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_currencies"]
            ]]);
            break;
            
        case "admin_delete_currency":
            showDeleteCurrency($chat_id, $message_id);
            break;
            
        // إدارة الحظر
        case "admin_ban_user":
            $steps[$from_id] = ['step' => 'admin_ban_user'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_user_id_ban'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_bans"]
            ]]);
            break;
            
        case "admin_unban_user":
            $steps[$from_id] = ['step' => 'admin_unban_user'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('send_user_id_unban'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_bans"]
            ]]);
            break;
            
        // إعدادات التحويل
        case "admin_set_transfer_fee":
            $steps[$from_id] = ['step' => 'admin_set_transfer_fee'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('set_transfer_fee'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_transfer_settings"]
            ]]);
            break;
            
        // إعدادات الإحالة
        case "admin_set_referral_bonus":
            $steps[$from_id] = ['step' => 'admin_set_referral_bonus'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, getLang('set_referral_bonus'), [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_referral_settings"]
            ]]);
            break;
            
        // إدارة المواقع
        case "admin_set_site1_url":
            $steps[$from_id] = ['step' => 'admin_set_site1_url'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🌐 - أدخل رابط الموقع ( 1⃣ ):

⚠️ - يجب إرسال الرابط بنفس الصيغه التالية:
https://رابط الموقع/api/v2

✅ - مثال:
 https://haamadh.com/api/v2*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        case "admin_set_site2_url":
            $steps[$from_id] = ['step' => 'admin_set_site2_url'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🌐 - أدخل رابط الموقع ( 2⃣ ):

⚠️ - يجب إرسال الرابط بنفس الصيغه التالية:
https://رابط الموقع/api/v2

✅ - مثال:
 https://haamadh.com/api/v2*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        case "admin_set_site3_url":
            $steps[$from_id] = ['step' => 'admin_set_site3_url'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🌐 - أدخل رابط الموقع ( 3⃣ ):

⚠️ - يجب إرسال الرابط بنفس الصيغه التالية:
https://رابط الموقع/api/v2

✅ - مثال:
 https://haamadh.com/api/v2*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        case "admin_set_site1_key":
            $steps[$from_id] = ['step' => 'admin_set_site1_key'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🔑 - أدخل مفتاح API للموقع ( 1⃣ ):

✳️ - اذهب إلى موقع الخدمات الخاص بك، وقم بنسخ مفتاح API، ثم أرسله هنا.*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        case "admin_set_site2_key":
            $steps[$from_id] = ['step' => 'admin_set_site2_key'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🔑 - أدخل مفتاح API للموقع ( 2⃣ ):

✳️ - اذهب إلى موقع الخدمات الخاص بك، وقم بنسخ مفتاح API، ثم أرسله هنا.*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        case "admin_set_site3_key":
            $steps[$from_id] = ['step' => 'admin_set_site3_key'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "*🔑 - أدخل مفتاح API للموقع ( 3⃣ ):

✳️ - اذهب إلى موقع الخدمات الخاص بك، وقم بنسخ مفتاح API، ثم أرسله هنا.*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_sites"]
            ]]);
            break;
            
        // إدارة القنوات
        case "admin_set_main_channel":
            $steps[$from_id] = ['step' => 'admin_set_main_channel'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "📢 أدخل رابط قناة البوت:", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_channels"]
            ]]);
            break;
            
        case "admin_set_orders_channel":
            $steps[$from_id] = ['step' => 'admin_set_orders_channel'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "🛒 أدخل رابط قناة الطلبات:", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_channels"]
            ]]);
            break;
            
        case "admin_set_support_channel":
            $steps[$from_id] = ['step' => 'admin_set_support_channel'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "👨‍💻 أدخل رابط قناة الدعم:", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_channels"]
            ]]);
            break;
            
        // الاشتراك الإجباري
        case "admin_add_forced_channel":
            $steps[$from_id] = ['step' => 'admin_add_forced_channel'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "🔔 أرسل رابط القناة أو المعرف (مثال: @channelname أو https://t.me/channelname):", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
            ]]);
            break;
            
        case "admin_delete_forced_channel":
            showDeleteForcedChannel($chat_id, $message_id);
            break;
            
        case "admin_view_forced_channels":
            showForcedChannelsList($chat_id, $message_id);
            break;
            
        // إدارة الأدمن
        case "admin_add_admin":
            $steps[$from_id] = ['step' => 'admin_add_admin'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "👑 أرسل معرف المستخدم (ID) الذي تريد إضافته كأدمن:", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
            ]]);
            break;
            
        case "admin_remove_admin":
            showRemoveAdmin($chat_id, $message_id);
            break;
            
        case "admin_view_admins":
            showAdminsList($chat_id, $message_id);
            break;
            
        // معالجة حذف القنوات والأدمن
        case strpos($data, "delete_forced_channel_") === 0:
            $channel_id = str_replace("delete_forced_channel_", "", $data);
            handleDeleteForcedChannel($chat_id, $message_id, $channel_id);
            break;
            
        case strpos($data, "remove_admin_") === 0:
            $admin_id = str_replace("remove_admin_", "", $data);
            handleRemoveAdmin($chat_id, $message_id, $admin_id);
            break;
            
        // معالجة كالبات إدارة الخدمات
        case strpos($data, "admin_add_service_category_") === 0:
            $category_id = str_replace("admin_add_service_category_", "", $data);
            showAddServiceSite($chat_id, $message_id, $category_id);
            break;
            
        case strpos($data, "admin_add_service_site_") === 0:
            $parts = explode('_', $data);
            $site_id = $parts[4];
            $category_id = $parts[5];
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_smm_id',
                'site_id' => $site_id,
                'category_id' => $category_id
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            editMessage($chat_id, $message_id, "🆔 *أرسل ايدي الخدمة من الموقع:*", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
            ]]);
            break;
            
        case strpos($data, "admin_service_link_") === 0:
            $link_format = str_replace("admin_service_link_", "", $data);
            $step_data = $steps[$from_id];
            
            // إنشاء الخدمة مع البيانات الجديدة
            $service_id = uniqid();
            $service_data = [
                'category' => $step_data['category_id'],
                'smm_id' => $step_data['smm_id'],
                'name' => $step_data['name'],
                'price' => $step_data['price'],
                'min' => $step_data['min'],
                'max' => $step_data['max'],
                'description' => $step_data['description'] ?? '',
                'link_format' => intval($link_format),
                'quality' => $step_data['quality'] ?? '✅️ عالية',
                'speed' => $step_data['speed'] ?? '',
                'guarantee' => $step_data['guarantee'] ?? ''
            ];
            
            // حفظ الخدمة في الموقع المناسب
            saveServicesBySite($step_data['site_id'], array_merge(
                getServicesBySite($step_data['site_id']),
                [$service_id => $service_data]
            ));
            
            // تنظيف الخطوات
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "✅ *تم إضافة الخدمة بنجاح!*\n\n📝 *البيانات المدخلة:*\n⚡ الجودة: {$service_data['quality']}\n🚀 السرعة: {$service_data['speed']}\n🛡️ الضمان: {$service_data['guarantee']}", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
            ]]);
            break;
            
        case strpos($data, "admin_service_quality_") === 0:
            $quality_type = str_replace("admin_service_quality_", "", $data);
            $step_data = $steps[$from_id];
            
            // تحويل نوع الجودة إلى نص
            $quality_texts = [
                'bad' => '🚫 لاننصح بها',
                'high' => '✅️ عالية', 
                'very_high' => '🔥 عالية جدا',
                'recommended' => '✅️ ينصح بها'
            ];
            
            $quality_text = $quality_texts[$quality_type] ?? '✅️ عالية';
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_speed',
                'site_id' => $step_data['site_id'],
                'category_id' => $step_data['category_id'],
                'smm_id' => $step_data['smm_id'],
                'name' => $step_data['name'],
                'price' => $step_data['price'],
                'min' => $step_data['min'],
                'max' => $step_data['max'],
                'quality' => $quality_text
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "⚡ *أرسل سرعة الخدمة:*\n\nمثال: يوميا 500K🔥");
            break;
            
        case strpos($data, "admin_delete_service_") === 0:
            $service_id = str_replace("admin_delete_service_", "", $data);
            $all_services = getAllServices();
            
            if (isset($all_services[$service_id])) {
                $service = $all_services[$service_id];
                $service_name = $service['name'];
                $site_id = $service['site_id'];
                
                // حذف الخدمة من الموقع المناسب
                $site_services = getServicesBySite($site_id);
                unset($site_services[$service_id]);
                saveServicesBySite($site_id, $site_services);
                
                editMessage($chat_id, $message_id, sprintf(getLang('service_deleted'), $service_name), [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
                ]]);
            } else {
                editMessage($chat_id, $message_id, getLang('service_not_found_delete'), [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
                ]]);
            }
            break;
            
        case strpos($data, "admin_delete_category_") === 0:
            $category_id = str_replace("admin_delete_category_", "", $data);
            global $categories, $categoriesFile;
            
            if (isset($categories[$category_id])) {
                $category_name = $categories[$category_id];
                
                // حذف الخدمات المرتبطة بهذا القسم من جميع المواقع
                $all_services = getAllServices();
                foreach ($all_services as $service_id => $service) {
                    if ($service['category'] == $category_id) {
                        $site_services = getServicesBySite($service['site_id']);
                        unset($site_services[$service_id]);
                        saveServicesBySite($service['site_id'], $site_services);
                    }
                }
                
                unset($categories[$category_id]);
                file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                editMessage($chat_id, $message_id, sprintf(getLang('category_deleted'), $category_name), [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_categories"]
                ]]);
            }
            break;
            
        case strpos($data, "admin_confirm_delete_currency_") === 0:
            $currency_code = str_replace("admin_confirm_delete_currency_", "", $data);
            handleDeleteCurrency($chat_id, $message_id, $currency_code);
            break;
            
        // إدارة قناة التفعيلات
        case "admin_manage_activation_channel":
            showActivationChannel($chat_id, $message_id);
            break;
            
        case "admin_set_activation_channel":
            $steps[$from_id] = ['step' => 'admin_set_activation_channel'];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            editMessage($chat_id, $message_id, "📢 أدخل معرف قناة التفعيلات (يجب أن يبدأ بـ -100):", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
            ]]);
            break;
            
        case "admin_toggle_activation_channel":
            global $settings, $settingsFile;
            $settings['activation_channel_enabled'] = !($settings['activation_channel_enabled'] ?? true);
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $status = $settings['activation_channel_enabled'] ? "✅ تم تفعيل" : "❌ تم تعطيل";
            editMessage($chat_id, $message_id, "$status قناة التفعيلات بنجاح.", [[
                ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
            ]]);
            break;
            
        case "admin_test_activation_notification":
            sendTestActivationNotification($chat_id, $message_id);
            break;
            
        case "admin_refresh_balances":
            answerCallback($callback, "✅ - تم تحديث الارصدة", true);
            showManageSites($chat_id, $message_id);
            break;
            
        case "admin_check_status_1":
            $balance_info = getSiteBalance(1);
            if (isset($balance_info['balance'])) {
                answerCallback($callback, "✅ - الموقع 1 يعمل بشكل صحيح", true);
            } else {
                answerCallback($callback, "⚠️ - الموقع 1 غير مرتبط: " . ($balance_info['error'] ?? 'خطأ غير معروف'), true);
            }
            break;
            
        case "admin_check_status_2":
            $balance_info = getSiteBalance(2);
            if (isset($balance_info['balance'])) {
                answerCallback($callback, "✅ - الموقع 2 يعمل بشكل صحيح", true);
            } else {
                answerCallback($callback, "⚠️ - الموقع 2 غير مرتبط: " . ($balance_info['error'] ?? 'خطأ غير معروف'), true);
            }
            break;
            
        case "admin_check_status_3":
            $balance_info = getSiteBalance(3);
            if (isset($balance_info['balance'])) {
                answerCallback($callback, "✅ - الموقع 3 يعمل بشكل صحيح", true);
            } else {
                answerCallback($callback, "⚠️ - الموقع 3 غير مرتبط: " . ($balance_info['error'] ?? 'خطأ غير معروف'), true);
            }
            break;
    }
}

// إظهار إدارة الخدمات
function showManageServices($chat_id, $message_id) {
    $buttons = [
        [['text' => "➕ إضافة خدمة", 'callback_data' => "admin_add_service"]],
        [['text' => "📝 تعديل/حذف خدمة", 'callback_data' => "admin_edit_delete_service"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, "🛠 إدارة الخدمات:", $buttons);
}

// إظهار إدارة الأقسام
function showManageCategories($chat_id, $message_id) {
    global $categories;
    
    $buttons = [
        [['text' => "➕ إضافة قسم", 'callback_data' => "admin_add_category"]]
    ];
    
    if (!empty($categories)) {
        $buttons[] = [['text' => "🗑 حذف قسم", 'callback_data' => "admin_delete_category"]];
    }
    
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]];
    
    editMessage($chat_id, $message_id, "📁 إدارة الأقسام:", $buttons);
}

// إظهار إدارة الرصيد
function showManageBalance($chat_id, $message_id) {
    $buttons = [
        [['text' => "➕ إضافة رصيد", 'callback_data' => "admin_add_balance"]],
        [['text' => "➖ خصم رصيد", 'callback_data' => "admin_subtract_balance"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, "💰 إدارة الرصيد:", $buttons);
}

// إظهار إدارة العملات
function showManageCurrencies($chat_id, $message_id) {
    global $exchangeRates;
    
    $text = getLang('manage_currencies') . "\n\n";
    foreach ($exchangeRates as $code => $currency) {
        $text .= "{$currency['name']} ($code): {$currency['rate']} {$currency['symbol']}\n";
    }
    
    $buttons = [
        [['text' => "➕ إضافة عملة", 'callback_data' => "admin_add_currency"]],
        [['text' => "🗑 حذف عملة", 'callback_data' => "admin_delete_currency"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار حذف العملات
function showDeleteCurrency($chat_id, $message_id) {
    global $exchangeRates;
    
    if (empty($exchangeRates)) {
        editMessage($chat_id, $message_id, "❌ لا توجد عملات لحذفها.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_currencies"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($exchangeRates as $code => $currency) {
        $buttons[] = [[
            'text' => "{$currency['name']} ($code)", 
            'callback_data' => "admin_confirm_delete_currency_$code"
        ]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_currencies"]];
    
    editMessage($chat_id, $message_id, "🗑 اختر العملة التي تريد حذفها:", $buttons);
}

// معالجة حذف العملة
function handleDeleteCurrency($chat_id, $message_id, $currency_code) {
    global $exchangeRates, $exchangeRatesFile;
    
    if (isset($exchangeRates[$currency_code])) {
        $currency_name = $exchangeRates[$currency_code]['name'];
        unset($exchangeRates[$currency_code]);
        file_put_contents($exchangeRatesFile, json_encode($exchangeRates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        editMessage($chat_id, $message_id, "✅ تم حذف العملة بنجاح: $currency_name", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_currencies"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "❌ العملة غير موجودة.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_currencies"]
        ]]);
    }
}

// إظهار إدارة الحظر
function showManageBans($chat_id, $message_id) {
    global $banned;
    
    $text = getLang('manage_bans_button') . "\n\n";
    $text .= "👥 عدد المحظورين: " . count($banned) . "\n";
    
    if (!empty($banned)) {
        $text .= "\nالمستخدمين المحظورين:\n";
        $count = 1;
        foreach ($banned as $user_id) {
            $text .= "$count. `$user_id`\n";
            $count++;
        }
    }
    
    $buttons = [
        [['text' => "🚫 حظر مستخدم", 'callback_data' => "admin_ban_user"]],
        [['text' => "✅ إلغاء حظر", 'callback_data' => "admin_unban_user"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار إعدادات التحويل
function showTransferSettings($chat_id, $message_id) {
    global $settings;
    
    $text = "🔄 إعدادات التحويل:\n\n";
    $text .= "💸 عمولة التحويل: " . ($settings['transfer_fee'] ?? 5) . "%\n";
    $text .= "💰 الحد الأدنى للتحويل: " . ($settings['transfer_min_amount'] ?? 1) . " دولار\n";
    
    $buttons = [
        [['text' => "💸 تعيين عمولة التحويل", 'callback_data' => "admin_set_transfer_fee"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار إعدادات الإحالة
function showReferralSettings($chat_id, $message_id) {
    global $settings;
    
    $text = "📢 إعدادات الإحالة:\n\n";
    $text .= "🎁 مكافأة الإحالة: " . ($settings['referral_bonus'] ?? 10) . "$\n";
    
    $buttons = [
        [['text' => "🎁 تعيين مكافأة الإحالة", 'callback_data' => "admin_set_referral_bonus"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار إدارة المواقع
function showManageSites($chat_id, $message_id) {
    global $smm_sites;
    
    $text = "*🌐 - قسم إدارة مواقع SMM خدمات البوت :*\n\n";
    
    foreach ($smm_sites as $site_id => $site) {
        $status = $site['enabled'] ? "✅ مفعل" : "❌ معطل";
        $text .= "*🌐 - الموقع $site_id ($status)*\n";
        $text .= "*🔗 - الرابط:* " . ($site['url'] ?: "غير مضبوط") . "\n";
        
        // عرض المفتاح قصير مع إخفاء 15 حرف بـ 5 نقاط فقط
        if ($site['api_key']) {
            $api_key_length = strlen($site['api_key']);
            if ($api_key_length > 15) {
                // عرض أول 10 أحرف فقط + 5 نقاط
                $visible_part = substr($site['api_key'], 0, 10);
                $text .= "*🔑 - المفتاح:* " . $visible_part . "•••••\n";
            } else {
                // إذا كان المفتاح أقل من 15 حرف، عرضه كاملاً
                $text .= "*🔑 - المفتاح:* " . $site['api_key'] . "\n";
            }
        } else {
            $text .= "*🔑 - المفتاح:* غير مضبوط\n";
        }
        
        // تحديد حالة الاتصال
        $connection_status = "غير مرتبط ⚠️";
        if ($site['enabled'] && !empty($site['url']) && !empty($site['api_key'])) {
            $balance_info = getSiteBalance($site_id);
            if (isset($balance_info['balance'])) {
                $text .= "*💰 - الرصيد:* " . $balance_info['balance'] . " $\n";
                $connection_status = "يعمل ✅";
            } else {
                $text .= "*💰 - الرصيد:* ❌ " . ($balance_info['error'] ?? 'خطأ غير معروف') . "\n";
                $connection_status = "غير مرتبط ⚠️";
            }
        } else {
            $text .= "*💰 - الرصيد:* ❌ غير متاح\n";
        }
        
        $text .= "*📡 - الحالة:* $connection_status\n\n";
    }
    
    $buttons = [
        [
            ['text' => "🌐 - تعيين موقع 1", 'callback_data' => "admin_set_site1_url"],
            ['text' => "🔒 - تعيين المفتاح 1", 'callback_data' => "admin_set_site1_key"]
        ],
        [
            ['text' => "🌐 - تعيين موقع 2", 'callback_data' => "admin_set_site2_url"],
            ['text' => "🔒 - تعيين المفتاح 2", 'callback_data' => "admin_set_site2_key"]
        ],
        [
            ['text' => "🌐 - تعيين موقع 3", 'callback_data' => "admin_set_site3_url"],
            ['text' => "🔒 - تعيين المفتاح 3", 'callback_data' => "admin_set_site3_key"]
        ],
        [['text' => "🔄 - تحديث الارصدة", 'callback_data' => "admin_refresh_balances"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار إدارة القنوات
function showManageChannels($chat_id, $message_id) {
    global $bot_channels;
    
    $text = "*📺 - إدارة قنوات البوت:*\n\n";
    $text .= "*♻️ - قناة البوت:* " . ($bot_channels['main_channel'] ?: "غير مضبوطة") . "\n";
    $text .= "*🌪 - قناة الطلبات:* " . ($bot_channels['orders_channel'] ?: "غير مضبوطة") . "\n";
    $text .= "*🛍 - قناة الدعم:* " . ($bot_channels['support_channel'] ?: "غير مضبوطة") . "\n";
    $text .= "*📢 - قناة التفعيلات:* " . ($bot_channels['activations_channel'] ?: "غير مضبوطة") . "\n";
    
    $buttons = [
        [
            ['text' => "♻️ قناة البوت", 'callback_data' => "admin_set_main_channel"],
            ['text' => "🌪 قناة الطلبات", 'callback_data' => "admin_set_orders_channel"]
        ],
        [
            ['text' => "🛍 قناة الدعم", 'callback_data' => "admin_set_support_channel"],
            ['text' => "📢 قناة التفعيلات", 'callback_data' => "admin_manage_activation_channel"]
        ],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار رسائل الدعم
function showSupportMessages($chat_id, $message_id) {
    global $notifications;
    
    $text = "💬 رسائل الدعم:\n\n";
    
    if (empty($notifications)) {
        $text .= "لا توجد رسائل دعم حالياً.";
    } else {
        foreach ($notifications as $user_id => $message) {
            $text .= "👤 المستخدم: `$user_id`\n";
            $text .= "📝 الرسالة: " . substr($message, 0, 100) . "...\n\n";
        }
    }
    
    $buttons = [[['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار الاشتراك الإجباري
function showForcedSubscription($chat_id, $message_id) {
    global $forcedChannels;
    
    $text = "*🔔 - إدارة الاشتراك الإجباري:*\n\n";
    $text .= "*📊 - عدد القنوات:* " . count($forcedChannels) . "\n\n";
    
    if (!empty($forcedChannels)) {
        $text .= "*القنوات الحالية:*\n";
        foreach ($forcedChannels as $channel_id => $channel) {
            $text .= "• {$channel['name']}\n";
        }
    }
    
    $buttons = [
        [['text' => "➕ إضافة قناة", 'callback_data' => "admin_add_forced_channel"]],
        [['text' => "➖ حذف قناة", 'callback_data' => "admin_delete_forced_channel"]],
        [['text' => "📋 عرض القنوات", 'callback_data' => "admin_view_forced_channels"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار إدارة الأدمن
function showManageAdmins($chat_id, $message_id) {
    global $admins;
    
    $text = "*👑 - إدارة الأدمن:*\n\n";
    $text .= "*📊 - عدد الأدمن:* " . count($admins) . "\n\n";
    
    if (!empty($admins)) {
        $text .= "*الأدمن الحاليون:*\n";
        foreach ($admins as $admin_id) {
            $text .= "• `$admin_id`\n";
        }
    }
    
    $buttons = [
        [['text' => "➕ إضافة أدمن", 'callback_data' => "admin_add_admin"]],
        [['text' => "➖ حذف أدمن", 'callback_data' => "admin_remove_admin"]],
        [['text' => "📋 عرض الأدمن", 'callback_data' => "admin_view_admins"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_panel"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار قائمة القنوات الإجبارية
function showForcedChannelsList($chat_id, $message_id) {
    global $forcedChannels;
    
    $text = "📋 قنوات الاشتراك الإجباري:\n\n";
    
    if (empty($forcedChannels)) {
        $text .= "لا توجد قنوات مضافة.";
        $buttons = [[['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]]];
    } else {
        foreach ($forcedChannels as $channel_id => $channel) {
            $text .= "• {$channel['name']}\n";
        }
        $buttons = [[['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]]];
    }
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار قائمة الأدمن
function showAdminsList($chat_id, $message_id) {
    global $admins;
    
    $text = "👑 قائمة الأدمن:\n\n";
    
    if (empty($admins)) {
        $text .= "لا توجد أدمن مضافة.";
    } else {
        foreach ($admins as $admin_id) {
            $text .= "• `$admin_id`\n";
        }
    }
    
    $buttons = [[['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]]];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إظهار حذف القناة الإجبارية
function showDeleteForcedChannel($chat_id, $message_id) {
    global $forcedChannels;
    
    if (empty($forcedChannels)) {
        editMessage($chat_id, $message_id, "❌ لا توجد قنوات لحذفها.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($forcedChannels as $channel_id => $channel) {
        $buttons[] = [['text' => $channel['name'], 'callback_data' => "delete_forced_channel_$channel_id"]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]];
    
    editMessage($chat_id, $message_id, "🗑 اختر القناة التي تريد حذفها:", $buttons);
}

// إظهار حذف الأدمن
function showRemoveAdmin($chat_id, $message_id) {
    global $admins;
    
    if (count($admins) <= 1) {
        editMessage($chat_id, $message_id, "❌ لا يمكن حذف الأدمن الأساسي.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($admins as $admin_id) {
        if ($admin_id != ADMIN_ID) { // لا يمكن حذف الأدمن الأساسي
            $buttons[] = [['text' => "👤 $admin_id", 'callback_data' => "remove_admin_$admin_id"]];
        }
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]];
    
    editMessage($chat_id, $message_id, "🗑 اختر الأدمن الذي تريد حذفه:", $buttons);
}

// معالجة حذف قناة إجبارية
function handleDeleteForcedChannel($chat_id, $message_id, $channel_id) {
    if (removeForcedChannel($channel_id)) {
        editMessage($chat_id, $message_id, "✅ تم حذف القناة بنجاح.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "❌ فشل في حذف القناة.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
        ]]);
    }
}

// معالجة حذف أدمن
function handleRemoveAdmin($chat_id, $message_id, $admin_id) {
    if (removeAdmin($admin_id)) {
        editMessage($chat_id, $message_id, "✅ تم حذف الأدمن بنجاح.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "❌ لا يمكن حذف الأدمن الأساسي.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
        ]]);
    }
}

// إظهار إضافة خدمة (اختيار القسم أولاً)
function showAddServiceCategory($chat_id, $message_id) {
    global $categories;
    
    if (empty($categories)) {
        editMessage($chat_id, $message_id, "❌ يجب إنشاء أقسام أولاً قبل إضافة الخدمات.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($categories as $category_id => $category_name) {
        $buttons[] = [['text' => $category_name, 'callback_data' => "admin_add_service_category_$category_id"]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]];
    
    editMessage($chat_id, $message_id, getLang('send_service_category'), $buttons);
}

// إظهار إضافة خدمة (اختيار الموقع بعد القسم)
function showAddServiceSite($chat_id, $message_id, $category_id) {
    global $smm_sites;
    
    $buttons = [];
    
    if ($smm_sites[1]['enabled'] ?? false) {
        $buttons[] = [['text' => "🌐 {$smm_sites[1]['name']}", 'callback_data' => "admin_add_service_site_1_$category_id"]];
    }
    
    if ($smm_sites[2]['enabled'] ?? false) {
        $buttons[] = [['text' => "🌐 {$smm_sites[2]['name']}", 'callback_data' => "admin_add_service_site_2_$category_id"]];
    }
    
    if ($smm_sites[3]['enabled'] ?? false) {
        $buttons[] = [['text' => "🌐 {$smm_sites[3]['name']}", 'callback_data' => "admin_add_service_site_3_$category_id"]];
    }
    
    if (empty($buttons)) {
        editMessage($chat_id, $message_id, "❌ يجب تفعيل موقع واحد على الأقل قبل إضافة الخدمات.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
        ]]);
        return;
    }
    
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]];
    
    editMessage($chat_id, $message_id, getLang('select_site_for_service'), $buttons);
}

// إظهار تعديل/حذف الخدمات
function showEditDeleteService($chat_id, $message_id) {
    $all_services = getAllServices();
    
    if (empty($all_services)) {
        editMessage($chat_id, $message_id, getLang('no_services_to_delete'), [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]
        ]]);
        return;
    }
    
    $buttons = [];
    foreach ($all_services as $service_id => $service) {
        $site_name = $service['site_id'] == 1 ? '🌐1' : ($service['site_id'] == 2 ? '🌐2' : '🌐3');
        $buttons[] = [[
            'text' => "{$site_name} {$service['name']}", 
            'callback_data' => "admin_delete_service_$service_id"
        ]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_services"]];
    
    editMessage($chat_id, $message_id, getLang('select_service_to_delete'), $buttons);
}

// إظهار حذف الأقسام
function showDeleteCategory($chat_id, $message_id) {
    global $categories;
    
    $buttons = [];
    foreach ($categories as $category_id => $category_name) {
        $buttons[] = [[
            'text' => $category_name, 
            'callback_data' => "admin_delete_category_$category_id"
        ]];
    }
    $buttons[] = [['text' => getLang('back_button'), 'callback_data' => "admin_manage_categories"]];
    
    editMessage($chat_id, $message_id, getLang('select_category_to_delete'), $buttons);
}

// إظهار إدارة قناة التفعيلات
function showActivationChannel($chat_id, $message_id) {
    global $bot_channels, $settings;
    
    $channel_id = $bot_channels['activations_channel'] ?? '';
    $status = $settings['activation_channel_enabled'] ?? true ? "✅ مفعل" : "❌ معطل";
    
    $text = "📢 *إدارة قناة التفعيلات*\n\n";
    $text .= "🆔 معرف القناة: `" . ($channel_id ?: "غير مضبوط") . "`\n";
    $text .= "⚡️ الحالة: $status\n\n";
    $text .= "*📝 ملاحظة:*\n";
    $text .= "• يجب أن يبدأ معرف القناة بـ -100\n";
    $text .= "• مثال: -1001234567890\n";
    $text .= "• سيتم نشر إشعارات الطلبات الجديدة تلقائياً في هذه القناة";
    
    $buttons = [
        [['text' => "🆔 تعيين معرف القناة", 'callback_data' => "admin_set_activation_channel"]],
        [['text' => ($settings['activation_channel_enabled'] ?? true ? "❌ تعطيل" : "✅ تفعيل"), 'callback_data' => "admin_toggle_activation_channel"]],
        [['text' => "📤 اختبار الإشعار", 'callback_data' => "admin_test_activation_notification"]],
        [['text' => getLang('back_button'), 'callback_data' => "admin_manage_channels"]]
    ];
    
    editMessage($chat_id, $message_id, $text, $buttons);
}

// إرسال إشعار اختبار لقناة التفعيلات
function sendTestActivationNotification($chat_id, $message_id) {
    global $bot_channels;
    
    $activation_channel = $bot_channels['activations_channel'] ?? '';
    if (!$activation_channel) {
        editMessage($chat_id, $message_id, "❌ لم يتم تعيين قناة التفعيلات بعد.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
        ]]);
        return;
    }
    
    $test_data = [
        'order_id' => "TEST-001",
        'category' => "اختبار",
        'service' => "خدمة تجريبية",
        'quantity' => 1000,
        'price' => 5.00,
        'user_id' => "5806409403",
        'link' => "https://example.com/test"
    ];
    
    if (sendActivationNotification($test_data)) {
        editMessage($chat_id, $message_id, "✅ تم إرسال إشعار الاختبار بنجاح إلى قناة التفعيلات.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
        ]]);
    } else {
        editMessage($chat_id, $message_id, "❌ فشل في إرسال إشعار الاختبار. تحقق من إعدادات القناة.", [[
            ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
        ]]);
    }
}

// معالجة خطوات المدير
function handleAdminSteps($chat_id, $from_id, $text, $message) {
    global $steps, $stepsFile, $categories, $categoriesFile;
    global $balances, $balancesFile, $welcome, $welcomeFile, $exchangeRates, $exchangeRatesFile;
    global $banned, $bannedFile, $instructions, $instructionsFile, $settings, $settingsFile;
    global $smm_sites, $smmSitesFile, $bot_channels, $botChannelsFile, $notifications, $notificationsFile;
    global $forcedChannels, $forcedChannelsFile, $admins, $adminsFile;
    
    $step_data = $steps[$from_id];
    $step = $step_data['step'];
    
    switch($step) {
        case 'admin_generate_card_amount':
            $amount = floatval($text);
            $code = generateCard($amount);
            
            sendMessage($chat_id, sprintf(getLang('card_created'), $code, $amount));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_activation_channel':
            // التحقق من صحة معرف القناة
            if (preg_match('/^-100\d+$/', $text)) {
                $bot_channels['activations_channel'] = $text;
                file_put_contents($botChannelsFile, json_encode($bot_channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                sendMessage($chat_id, "✅ تم تعيين قناة التفعيلات بنجاح: `$text`", [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
                ]]);
            } else {
                sendMessage($chat_id, "❌ معرف القناة غير صالح. يجب أن يبدأ بـ -100 ويتبعه أرقام فقط.\nمثال: -1001234567890", [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_activation_channel"]
                ]]);
            }
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_welcome':
            $welcome['text'] = $text;
            file_put_contents($welcomeFile, json_encode($welcome, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('welcome_updated'));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_add_category':
            $category_id = uniqid();
            $categories[$category_id] = $text;
            file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('category_added'));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_add_balance_user':
            $user_id = intval($text);
            $steps[$from_id] = [
                'step' => 'admin_add_balance_amount',
                'user_id' => $user_id
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('send_amount_add'));
            break;
            
case 'admin_add_balance_amount':
    $user_id = $step_data['user_id'];
    $amount = floatval($text);
    
    addBalance($user_id, $amount);
    $new_balance = getBalance($user_id);
    
    // الرسالة الأصلية للمدير (موجودة مسبقاً)
    sendMessage($chat_id, sprintf(getLang('balance_added'), $amount, $user_id, $new_balance));
    
    // ✓ الإضافة الجديدة فقط: إرسال رسالة للمستخدم
    $converted_amount = convertCurrency($amount, $user_id);
    $converted_balance = convertCurrency($new_balance, $user_id);
    sendMessage($user_id, "💰 *تم شحن رصيدك بنجاح!*\n\n*📊 المبلغ المضاف:* {$converted_amount}\n*💳 الرصيد الجديد:* {$converted_balance}\n*📅 التاريخ:* " . date('Y-m-d H:i:s'));
    
    unset($steps[$from_id]);
    file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    break;
            
        case 'admin_subtract_balance_user':
            $user_id = intval($text);
            $steps[$from_id] = [
                'step' => 'admin_subtract_balance_amount',
                'user_id' => $user_id
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('send_amount_subtract'));
            break;
            
        case 'admin_subtract_balance_amount':
            $user_id = $step_data['user_id'];
            $amount = floatval($text);
            
            subtractBalance($user_id, $amount);
            $new_balance = getBalance($user_id);
            
            sendMessage($chat_id, sprintf(getLang('balance_subtracted'), $amount, $user_id, $new_balance));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_add_currency_code':
            $code = strtoupper($text);
            $steps[$from_id] = [
                'step' => 'admin_add_currency_rate',
                'code' => $code
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('send_currency_rate'));
            break;
            
        case 'admin_add_currency_rate':
            $code = $step_data['code'];
            $rate = floatval($text);
            
            $steps[$from_id] = [
                'step' => 'admin_add_currency_symbol',
                'code' => $code,
                'rate' => $rate
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('send_currency_symbol'));
            break;
            
        case 'admin_add_currency_symbol':
            $code = $step_data['code'];
            $rate = $step_data['rate'];
            $symbol = $text;
            
            $exchangeRates[$code] = [
                'rate' => $rate,
                'symbol' => $symbol,
                'name' => $code
            ];
            file_put_contents($exchangeRatesFile, json_encode($exchangeRates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('currency_added'));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_ban_user':
            $user_id = intval($text);
            
            if (in_array($user_id, $banned)) {
                sendMessage($chat_id, sprintf(getLang('user_already_banned'), $user_id));
            } else {
                $banned[] = $user_id;
                file_put_contents($bannedFile, json_encode($banned, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                sendMessage($chat_id, sprintf(getLang('user_banned'), $user_id));
            }
            
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_unban_user':
            $user_id = intval($text);
            
            if (!in_array($user_id, $banned)) {
                sendMessage($chat_id, sprintf(getLang('user_not_banned'), $user_id));
            } else {
                $banned = array_diff($banned, [$user_id]);
                file_put_contents($bannedFile, json_encode($banned, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                sendMessage($chat_id, sprintf(getLang('user_unbanned'), $user_id));
            }
            
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_instructions':
            file_put_contents($instructionsFile, $text);
            $instructions = $text;
            
            sendMessage($chat_id, getLang('instructions_updated'));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_recharge_text':
            $settings['recharge_text'] = $text;
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, getLang('recharge_text_updated'));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_transfer_fee':
            $fee = floatval($text);
            $settings['transfer_fee'] = $fee;
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, sprintf(getLang('transfer_fee_updated'), $fee));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_referral_bonus':
            $bonus = floatval($text);
            $settings['referral_bonus'] = $bonus;
            file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, sprintf(getLang('referral_bonus_updated'), $bonus));
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        // إعدادات المواقع
        case 'admin_set_site1_url':
            $smm_sites[1]['url'] = $text;
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين رابط الموقع 1⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_site2_url':
            $smm_sites[2]['url'] = $text;
            $smm_sites[2]['enabled'] = !empty($text);
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين رابط الموقع 2⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_site3_url':
            $smm_sites[3]['url'] = $text;
            $smm_sites[3]['enabled'] = !empty($text);
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين رابط الموقع 3⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_site1_key':
            $smm_sites[1]['api_key'] = $text;
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين مفتاح API للموقع 1⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_site2_key':
            $smm_sites[2]['api_key'] = $text;
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين مفتاح API للموقع 2⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_site3_key':
            $smm_sites[3]['api_key'] = $text;
            file_put_contents($smmSitesFile, json_encode($smm_sites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "*✅ - تم تعيين مفتاح API للموقع 3⃣ بنجاح!*");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        // إعدادات القنوات
        case 'admin_set_main_channel':
            $bot_channels['main_channel'] = $text;
            file_put_contents($botChannelsFile, json_encode($bot_channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "✅ تم تعيين قناة البوت بنجاح!");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_orders_channel':
            $bot_channels['orders_channel'] = $text;
            file_put_contents($botChannelsFile, json_encode($bot_channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "✅ تم تعيين قناة الطلبات بنجاح!");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        case 'admin_set_support_channel':
            $bot_channels['support_channel'] = $text;
            file_put_contents($botChannelsFile, json_encode($bot_channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "✅ تم تعيين قناة الدعم بنجاح!");
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        // الاشتراك الإجباري
        case 'admin_add_forced_channel':
            $channel_info = extractChannelInfo($text);
            if ($channel_info) {
                $channel_id = addForcedChannel($channel_info);
                sendMessage($chat_id, "✅ تم إضافة القناة بنجاح:\n\nاسم القناة: {$channel_info['name']}\nرابط القناة: {$channel_info['link']}", [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
                ]]);
            } else {
                sendMessage($chat_id, "❌ لم أتمكن من التعرف على القناة. يرجى إرسال رابط أو معرف صحيح للقناة.", [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_forced_subscription"]
                ]]);
            }
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        // إدارة الأدمن
        case 'admin_add_admin':
            if (is_numeric($text)) {
                $admin_id = intval($text);
                if (addAdmin($admin_id)) {
                    sendMessage($chat_id, "✅ تم إضافة الأدمن بنجاح: `$admin_id`", [[
                        ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
                    ]]);
                } else {
                    sendMessage($chat_id, "❌ هذا المستخدم مسجل بالفعل كأدمن.", [[
                        ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
                    ]]);
                }
            } else {
                sendMessage($chat_id, "❌ يرجى إرسال معرف مستخدم صحيح (أرقام فقط).", [[
                    ['text' => getLang('back_button'), 'callback_data' => "admin_manage_admins"]
                ]]);
            }
            unset($steps[$from_id]);
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            break;
            
        // إضافة خدمة جديدة - الخطوات
        case 'admin_add_service_smm_id':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = intval($text);
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_name',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "📝 *أرسل اسم الخدمة:*");
            break;
            
        case 'admin_add_service_name':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $text;
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_price',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "💰 *أرسل سعر الخدمة:*");
            break;
            
        case 'admin_add_service_price':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $step_data['name'];
            $price = floatval($text);
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_min_max',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name,
                'price' => $price
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "🔢 *أرسل الحد الأدنى والأقصى في رسالة واحدة (سطر لكل منهما):*\n\nمثال:\n100\n1000");
            break;
            
        case 'admin_add_service_min_max':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $step_data['name'];
            $price = $step_data['price'];
            
            // فصل الحد الأدنى والأقصى من النص
            $lines = explode("\n", $text);
            $min = intval(trim($lines[0]));
            $max = isset($lines[1]) ? intval(trim($lines[1])) : $min;
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_quality',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name,
                'price' => $price,
                'min' => $min,
                'max' => $max
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // أزرار اختيار الجودة
            $quality_buttons = [
                [
                    ['text' => "🚫 لاننصح بها", 'callback_data' => "admin_service_quality_bad"],
                    ['text' => "✅️ عالية", 'callback_data' => "admin_service_quality_high"]
                ],
                [
                    ['text' => "🔥 عالية جدا", 'callback_data' => "admin_service_quality_very_high"],
                    ['text' => "✅️ ينصح بها", 'callback_data' => "admin_service_quality_recommended"]
                ]
            ];
            
            sendMessage($chat_id, "🎯 *اختر جودة الخدمة:*", $quality_buttons);
            break;
            
        case 'admin_add_service_speed':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $step_data['name'];
            $price = $step_data['price'];
            $min = $step_data['min'];
            $max = $step_data['max'];
            $quality = $step_data['quality'];
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_guarantee',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name,
                'price' => $price,
                'min' => $min,
                'max' => $max,
                'quality' => $quality,
                'speed' => $text
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "🛡️ *أرسل مدة الضمان:*\n\nمثال: 180 يوم");
            break;
            
        case 'admin_add_service_guarantee':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $step_data['name'];
            $price = $step_data['price'];
            $min = $step_data['min'];
            $max = $step_data['max'];
            $quality = $step_data['quality'];
            $speed = $step_data['speed'];
            $guarantee = $text;
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_description',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name,
                'price' => $price,
                'min' => $min,
                'max' => $max,
                'quality' => $quality,
                'speed' => $speed,
                'guarantee' => $guarantee
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            sendMessage($chat_id, "📄 *أرسل وصف الخدمة (أو اكتب 'لا' لتخطي):*");
            break;
            
        case 'admin_add_service_description':
            $site_id = $step_data['site_id'];
            $category_id = $step_data['category_id'];
            $smm_id = $step_data['smm_id'];
            $name = $step_data['name'];
            $price = $step_data['price'];
            $min = $step_data['min'];
            $max = $step_data['max'];
            $quality = $step_data['quality'];
            $speed = $step_data['speed'];
            $guarantee = $step_data['guarantee'];
            $description = ($text == 'لا') ? '' : $text;
            
            $steps[$from_id] = [
                'step' => 'admin_add_service_link_format',
                'site_id' => $site_id,
                'category_id' => $category_id,
                'smm_id' => $smm_id,
                'name' => $name,
                'price' => $price,
                'min' => $min,
                'max' => $max,
                'quality' => $quality,
                'speed' => $speed,
                'guarantee' => $guarantee,
                'description' => $description
            ];
            file_put_contents($stepsFile, json_encode($steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // أزرار اختيار صيغة الرابط
            sendMessage($chat_id, "🔗 *اختر صيغة الرابط المطلوبة:*", [
                [
                    ['text' => getLang('link_format_1'), 'callback_data' => "admin_service_link_1"],
                    ['text' => getLang('link_format_2'), 'callback_data' => "admin_service_link_2"]
                ]
            ]);
            break;
    }
}

// دالة لمعالجة خطوات المدير
function handleAdminStep($chat_id, $from_id, $text, $message) {
    handleAdminSteps($chat_id, $from_id, $text, $message);
}

// دالة رئيسية لمعالجة كل كالبات المدير
function handleAdminCallbackData($chat_id, $message_id, $from_id, $data, $callback) {
    // معالجة كالبات المدير العامة
    handleAdminCallback($chat_id, $message_id, $from_id, $data, $callback);
}
?>