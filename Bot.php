<?php 
@require_once 'Config.php';
    
$telegram_ip_ranges = [['lower'=>'149.154.160.0', 'upper'=>'149.154.175.255'],['lower'=>'91.108.4.0','upper'=>'91.108.7.255']];
$ip_dec = (float) sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])); $ok=false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf('%u', ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf('%u', ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec && $ip_dec <= $upper_dec) $ok=true; 
} if (!$ok) @header('location: https://www.google.com/');

function bot($method, $data=[]) {
    global $Config; $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$Config['token'].'/'.$method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    return json_decode(curl_exec($ch));
}
function is_num($msg){
    if (strpos($msg,'1') !== false or strpos($msg,'2') !== false or strpos($msg,'3') !== false or strpos($msg,'4') !== false or strpos($msg,'5') !== false or strpos($msg,'6') !== false or strpos($msg,'7') !== false or strpos($msg,'8') !== false or strpos($msg,'9') !== false) {
        return true;
    }else{
        return false;
    }
}
$Update = json_decode(file_get_contents('php://input'));
$Settings = $sql->query("SELECT * FROM `panel` WHERE `id` = '85' LIMIT 1")->fetch_assoc();
if (isset($Update->message)==1) {
    $Text = str_replace(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ($Update->message->text));
    $FromId = $Update->message->from->id;
    $MessageId = $Update->message->message_id;
    $ChatId = $Update->callback_query->message->chat->id;
    $ChatType = $Update->message->chat->type;
    $Users = $sql->query("SELECT * FROM `users` WHERE `id` = '{$FromId}' LIMIT 1")->fetch_assoc();
    $tch_1 = bot('getChatMember',['chat_id'=>$Config['channel_id'][0], 'user_id'=>$FromId])->result->status;
} elseif (isset($Update->callback_query)==1) {
    $Data = $Update->callback_query->data;
    $QueryId = $Update->callback_query->id;
    $FromId = $Update->callback_query->from->id;
    $MessageId = $Update->callback_query->message->message_id;
    $Username = $Update->callback_query->from->username;
    $ChatId = $Update->callback_query->message->chat->id;
    $ChatType = $Update->callback_query->message->chat->type;
    $Users = $sql->query("SELECT * FROM `users` WHERE `id` = '{$FromId}' LIMIT 1")->fetch_assoc();
    $tch_1 = bot('getChatMember',['chat_id'=>$Config['channel_id'][0], 'user_id'=>$FromId])->result->status;
} elseif (isset($Update->inline_query)==1) {
    $Iquery = $Update->inline_query->query;
    $IqId = $Update->inline_query->id;
    $FromId = $Update->inline_query->from->id;
} if ($ChatType=='private' && $Users['block']==1 && !in_array($FromId, $Config['admins'])) die;

$Menu = json_encode(['inline_keyboard'=>[
    [['text'=>':|', 'callback_data'=>'moarefi'], ['text'=>'راهنمای استفاده 📚', 'callback_data'=>'use_help']],
    [['text'=>'تست نجوا', 'switch_inline_query_current_chat'=> 'تست @lamirmmdl']]
]]);
$Panel = json_encode(['keyboard'=>[
    [['text'=>'آمار 📈']],
    [['text'=>'فوروارد 📤'],['text'=>'ارسال 📩']],
    [['text'=>'بلاک کردن ⚠️'],['text'=>'آنبلاک کردن 🌀']],
    [['text'=>'🔑 کلید پاور ['.str_replace([0,1],['OFF','ON'],$Settings['power']).']'],['text'=>'/start']]
], 'resize_keyboard'=>true, 'one_time_keyboard'=>true]);
$BackPanel = json_encode(['keyboard'=>[
    [['text'=>'بازگشت ↪️']]
], 'resize_keyboard'=>true, 'one_time_keyboard'=>true]);
$Remove = json_encode(['KeyboardRemove'=>[], 'remove_keyboard'=>true]); 

if ($ChatType=='private' && $Settings['power']==0 && !in_array($FromId, $Config['admins'])) {
    bot('sendMessage', [
        'chat_id'=> $FromId,
        'text'=> "ربات خاموش میباشد 😴\nچند دقیقه بعد دوباره امتحان کنید ⏰",
    ]);
    die;
}
        
elseif ($ChatType=='private' && $tch_1=='left') {
    bot('sendMessage', [
        'chat_id'=> $FromId,
        'text'=> "📛 برای فعال شدن ربات باید در کانال زیر عضو شوید 📛
👉 {$Config['channel_link'][0]} 👈
✅ چرا باید عضو کانال شویم؟!
🔹 زیرا جهت پشتیبانی و دریافت اطلاعیه ها و آموزش های ربات لازم است حتما عضو کانال باشید ...
👇🏻⚠️ پس از عضویت در کانال به ربات برگشته و روی دکمه زیر کلیک کنید ⚠️👇🏻",
        'reply_markup'=> json_encode(['inline_keyboard'=>[
            [['text'=>'☑️ عضو شدم', 'callback_data'=>'isjoin']]
        ]])
    ]);
    die;
} elseif ($Data=='isjoin') {
    if ($tch_1=='left') {
        bot('answerCallbackQuery',[
            'callback_query_id'=>$QueryId,
            'text'=>'⚠️ شما هنوز در کانال ها عضو نشدید ...',
            'show_alert'=>true
        ]);
        die;
    } else {
        $MsgId= bot('deleteMessage',['chat_id'=>$FromId, 'message_id'=>$MessageId])->result->message_id;
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> 'عضویت شما با موفقیت تایید شد✔️',
            'reply_to_message_id'=> $MsgId,
            'reply_markup'=> $Menu
        ]);
        die;
    }
}
    

elseif (preg_match('/^\/(start)/', $Text)) {
    if ($sql->query("SELECT `id` FROM `users` WHERE `id`='{$FromId}'")->num_rows<1) {
        $sql->query("INSERT INTO `users` (`id`) VALUES ('{$FromId}')");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> $Update->message->from->first_name.' عزیز به ربات نجوا خوش آمدی',
            'reply_markup'=> $Menu
        ]);
    } else {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> $Update->message->from->first_name.' عزیز به ربات نجوا خوش آمدی',
            'reply_markup'=> $Menu
        ]);
    }
}

elseif ($Data=='moarefi') {
    bot('editMessageText', [
        'chat_id'=>$FromId,
        'message_id'=>$MessageId,
        'text'=>'ChannelID',
        'reply_markup'=>$Menu
    ]);
}

elseif ($Data=='use_help') {
    bot('editMessageText', [
        'chat_id'=>$FromId,
        'message_id'=>$MessageId,
        'text'=>'برای ساخت و ارسال یک نجوا به دیگران (فقط آن فرد توانایی خواندن پیام شمارا دارد❗️) کافیست به راهنمایی زیر توجه کنید.
            
1- ابتدا یوزرنیم ربات را در جایی که میخواهید پیام مخفی(نجوا) را ارسال کنید تایپ  یا کپی-پیست میکنید.
2- سپس بعد از گذاشتن یک فاصله متنی که میخواهید بصورت مخفی ارسال شود را تا 200 کاراکتر (پشتبانی از متن انگلیسی ، فارسی ، اعداد و بقیه نشانگرها و شکلک ها) وارد میکنید. (این 200 کاراکتر به دلیل محدودیت های تلگرام میباشد و با افزایش این محدودیت توسط تلگرام محدودیت ربات نیز افزایش میابد).
3- سپس یوزرنیم کاربر مورد نظر را همراه با @ وارد میکنید و منتظر میمانید تا ربات کشوی اینلاین را نمایش دهد.
4- سپس روی کشوی اینلاینی کلیک میکنید و نجوای شما ارسال میشود.
مثال :
@'.bot('getMe', [])->result->username.' EXAMPLE TEXT @example_username
⚠️توجه : فقط کاربر مورد نظر و سازنده نجوا توانایی خواندن متن را دارند و به دلیل اینلاین بودن ارسال نجوا میتوانید پیام مخفی را در پیوی شخص/سوپرگروه ها و حتی در کانال ارسال کنید.',
        'reply_markup'=>$Menu
    ]);
}

elseif (preg_match('/^(SeenMsg\_(.*))$/', $Data, $Match)) {
    if ($Match[2] != null) {
        $Query = $sql->query("SELECT * FROM `najva` WHERE `id` = '{$Match[2]}' LIMIT 1")->fetch_assoc();
        if ((strtolower($Username)==strtolower($Query['username|id'])) || ($FromId==$Query['username|id']) || ($FromId==$Query['owner'])) {
            if ((strtolower($Username)==strtolower($Query['username|id'])) || ($FromId==$Query['username|id'])) {
                bot('editMessageText', [
                    'chat_id'=>$ChatId,
                    'inline_message_id'=>$Update->callback_query->inline_message_id,
                    'text'=>'نجوا توسط { '.$Username.' } خوانده شد... 👁',
                    'reply_markup'=>json_encode(['inline_keyboard'=>[
                        [['text'=>'نمایش پیام🔐', 'callback_data'=>'SeenMsg_'.$Match[2]]],
                        [['text'=>'نمایش فضول ها🤨', 'callback_data'=>'Fozol_'.$Match[2]]],
                        [['text'=>'حذف نجوا❌', 'callback_data'=>'delete_'.$Match[2]]],
                    ]])
                ]);
            } bot('answerCallbackQuery',[
                'callback_query_id'=>$QueryId,
                'text'=>$Query['text'],
                'show_alert'=>true
            ]);
        } else {
            bot('answerCallbackQuery',[
                'callback_query_id'=>$QueryId,
                'text'=>'شرمنده، نمیتوانید محتوای این نجوا را ببینید زیرا به شما ارسال نشده 🔐.',
                'show_alert'=>true
            ]);
            $first_name = $Update->callback_query->from->first_name;
            $fozol = $Query['fozol'] . "$first_name\n";
            $sql->query("UPDATE `najva` SET `fozol` = '{$fozol}' WHERE `id` = '{$$Match[2]}' LIMIT 1");
        }
    }
}
elseif (preg_match('/^(delete\_(.*))$/', $Data, $Match)) {
    if ($Match[2] != null) {
        $Query = $sql->query("SELECT * FROM `najva` WHERE `id` = '{$Match[2]}' LIMIT 1")->fetch_assoc();
        if ($FromId==$Query['owner']) {
                bot('editMessageText', [
                    'chat_id'=>$ChatId,
                    'inline_message_id'=>$Update->callback_query->inline_message_id,
                    'text'=>'❌'
                ]);
                $sql->query("DELETE * FROM `najva` WHERE `id` = '{$Match[2]}' LIMIT 1");
        } else {
            bot('answerCallbackQuery',[
                'callback_query_id'=>$QueryId,
                'text'=>'فرستادنده این نجوا شما نیستید ! ❌',
                'show_alert'=>true
            ]);
        }
    }
}
elseif($Data == 'text'){
    bot('answerCallbackQuery',[
        'callback_query_id'=>$QueryId,
        'text'=>'این دکمه نمایشی است 🌹',
        'show_alert'=>false
    ]);
}
elseif (preg_match('/^(Fozol\_(.*))$/', $Data, $Match)) {
    if ($Match[2] != null) {
        $Query = $sql->query("SELECT * FROM `najva` WHERE `id` = '{$Match[2]}' LIMIT 1")->fetch_assoc();
        if ((strtolower($Username)==strtolower($Query['username|id'])) || ($FromId==$Query['username|id']) || ($FromId==$Query['owner'])) {
            $f = explode("\n",$Query['fozol']);
            if ($Query['fozol'] != null and $Query['fozol'] != "\n" and $Query['fozol'] != '') {
                bot('answerCallbackQuery',[
                    'callback_query_id'=>$QueryId,
                    'text'=>'🤦🏻‍♂️'.$Query['fozol'],
                    'show_alert'=>true
                ]);
            }else{
                bot('answerCallbackQuery',[
                    'callback_query_id'=>$QueryId,
                    'text'=>"خداروشکر کسی فضولی نکرده 😀",
                    'show_alert'=>true
                ]);
            }
           
        } else {
            bot('answerCallbackQuery',[
                'callback_query_id'=>$QueryId,
                'text'=>'🤨 فضولی نکن به فضولا فضول 🤨',
                'show_alert'=>true
            ]);
        }
    }
} elseif ((preg_match('/^((.*) (.+))/si', $Iquery, $Match)?? preg_match('/^((.*) @(.*?))/si', $Iquery, $Match))) {
    if ($sql->query("SELECT `id` FROM `users` WHERE `id`='{$FromId}'")->num_rows<1) {
        bot('answerInlineQuery', [
            'inline_query_id'=>$IqId,
            'results'=>json_encode([[
                'type'=>'article',
                'id'=>base64_encode(rand()),
                'title'=>'عضویت اجباری',
                'input_message_content'=>[
                    'message_text'=>'برای استفاده از ربات لازم است ربات https://t.me/'.bot('getMe', [])->result->username.'?start را استارت کنید !',
                ],
                'description'=>'@'.bot('getMe', [])->result->username
            ]]),
            'cache_time'=>1,
            'switch_pm_text'=>'@'.bot('getMe', [])->result->username,
            'switch_pm_parameter'=>'start_bot'
        ]);
    } else {
        if ((($Iquery==null || ($Match[2]==null && $Match[3]==null)) || (mb_strlen($Match[2])<1 && mb_strlen($Match[3])<4))) {
            bot('answerInlineQuery', [
                'inline_query_id'=>$IqId,
                'results'=>json_encode([[
                    'type'=>'article',
                    'id'=>base64_encode(rand()),
                    'title'=>'خالی بودن کادر !',
                    'input_message_content'=>[
                        'message_text'=>'ابتدا یوزرنیم ربات رو تایپ کن بعدش با یه فاصله متنی که میخوای نمایش بدم رو بنویس و با یک فاصله یوزرنیم کاربر مورد نظرت رو بزار !!'
                    ],
                    'description'=>'متن نجوا [space] @username or ID'
                ]]),
                'cache_time'=>1,
                'switch_pm_text'=>'متن نجوا [space] @username or ID',
                'switch_pm_parameter'=>'no_parameter'
            ]);
        } else {
            if (mb_strlen($Match[2])>=200) {
                bot('answerInlineQuery', [
                    'inline_query_id'=>$IqId,
                    'results'=>json_encode([[
                        'type'=>'article',
                        'id'=>base64_encode(rand()),
                        'title'=>'پیام طولانی تر از 200 کاراکتر میباشد !',
                        'input_message_content'=>[
                            'message_text'=>'پیام طولانی تر از 200 کاراکتر میباشد !'
                        ]
                    ]]),
                    'cache_time'=>1,
                    'switch_pm_text'=>'پیام طولانی تر از 200 کاراکتر میباشد !',
                    'switch_pm_parameter'=>'long_text'
                ]);
            } else {
                if (mb_strlen($Match[3])>4) {
                    if(strlen($Match[3])==mb_strlen($Match[3], 'UTF-8')) {
                        if (strpos($Match[3],'@') !== false and is_num($Match[3]) != true) {
                            $username = strtolower(str_replace('@', null, $Match[3]));
                            $tag = "@$username";
                        }else{
                            $username = $Match[3];
                            $tag = "<a href='tg://user?id=$username'>$username</a>";
                        
                        }
                        
                        $sql->query("INSERT INTO `najva` (`owner`, `username|id`, `text`, `fozol`) VALUES ('{$FromId}', '{$username}', '{$Match[2]}', '');");
                        $use =  $sql->query("SELECT `id` FROM `users` WHERE `id`='{$FromId}'")->fetch_assoc();
                        if (!in_array($username,explode(" ",$use['rec']))) {
                            $rec = $use['rec'] . "$username ";
                        $sql->query("UPDATE `users` SET `rec` = '$rec' WHERE `id` = '{$FromId}' LIMIT 1");
                        }
                        $Query = $sql->query("SELECT * FROM `najva` WHERE `owner`='{$FromId}' ORDER BY `id` DESC LIMIT 1")->fetch_assoc();
                        bot('answerInlineQuery', [
                            'inline_query_id'=>$IqId,
                            'results'=>json_encode([[
                                'type'=>'article',
                                'id'=>base64_encode(rand()),
                                'title'=>'🦻🏻 ارسال نجوا به '.$username,
                                'input_message_content'=>[
                                    'message_text'=>"🔒 یک نجوا به $tag فقط ایشان می‌تواند آن را باز کند.",
                                    'parse_mode'=>'html',
                                    'disable_web_page_preview'=>true
                                ],
                                'reply_markup'=>['inline_keyboard'=>[
                                    [['text'=>'نمایش پیام🔐', 'callback_data'=>'SeenMsg_'.$Query['id']]],
                                    [['text'=>'نمایش فضول ها🤨', 'callback_data'=>'Fozol_'.$Query['id']]]
                                ],'resize_keyboard'=>true],
                                'description'=>'ارسال نجوا به '.$username . ' ✅',
                                'thumb_url'=>'https://tabnakjavan.com/files/fa/news/1398/7/12/62611_768.jpg',
                                
                                ]
                        ]),
                        'cache_time'=>150,
                                'is_personal'=> true,
                                'switch_pm_text'=>'خرید آپلودر پیشرفته',
                                'switch_pm_parameter'=>'uploader'
                        ]);
                       
                    }
                }
            }
        }
    }
}elseif(isset($Iquery)){
    bot('answerInlineQuery', [
        'inline_query_id'=>$IqId,
        'results'=>json_encode([[
            'type'=>'article',
            'id'=>base64_encode(rand()),
            'title'=>'پیام یا یوزرنیم|ایدی عددی خالی است',
            'input_message_content'=>[
                'message_text'=>"❌ پیام یا یوزرنیم|ایدی عددی خالی میباشد ❌",
                'parse_mode'=>'html',
                'disable_web_page_preview'=>true
            ],
            'description'=>'text [space] @username or @ID',
            'thumb_url'=>'https://cdn.asriran.com/files/fa/news/1398/7/10/1022327_724.jpg'
            ],[
                'type'=>'article',
                'id'=>base64_encode(rand()),
                'title'=>'🆘 راهنما',
                'input_message_content'=>[
                    'message_text'=>"🆘 راهنما ربات بگو مگو 🆘\nبرای ارسال پیام خصوصی در گروه یا کانال ابتدا ربات را اسارت کنید\nسپس در گروه یا کانال خود ابتدا یوزرنیم ربات رو بنویسید\nسپس یک خط فاصله و پیام خود که بیشتر از 300 کارکتر نباید باشد\nدوباره یک خط فاصله و یوزریم یا ایدی عددی فرد مورد نظر با @
مثال : 
@BgooMgoBot Test @lamirmmdl
یا
@BgooMgoBot Test 358165791",
                    'parse_mode'=>'html',
                    'disable_web_page_preview'=>true
                ],
                'reply_markup'=>['inline_keyboard'=>[
                    [['text'=>'ارسال نجوا تست🧠', 'switch_inline_query_current_chat'=>'Test @lamirmmdl']],
                    [['text'=>'کانال ما🏵', 'url'=>'https://t.me/ChannelID']]
                ]],
                'description'=>'راهنمای ربات بگو مگو 🆘',
                'thumb_url'=>'http://injouri.ir/wp-content/uploads/2018/12/Question-Mark-v.2-600x600.jpg',
                ]
    ]),'cache_time'=>150,
    'is_personal'=> true,
    'switch_pm_text'=>'| گروه |',
    'switch_pm_parameter'=>"gap"
    ]);
}
    
elseif (in_array($FromId, $Config['admins'])) {
    if (in_array($Text, ['/panel', 'بازگشت ↪️'])) {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> 'به پنل مدیریت وارد شدید ➰',
            'reply_markup'=> $Panel
        ]);
    }
    
    elseif ($Text=='آمار 📈') {
        $user = $sql->query("SELECT `id` FROM `users`")->num_rows;
        $najva = $sql->query("SELECT `id` FROM `najva`")->num_rows;
        $ban = $sql->query("SELECT `block` FROM `users` WHERE `block` = '1'")->num_rows;
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> "تعداد اعضای ربات برابر : {$user}\nتعداد نجواها : {$najva}
تعداد مدیران ربات : ".(count($Config['admins']))."\nتعداد اعضای بلاک شده در ربات برابر : {$ban}",
            'reply_markup'=> $Panel
        ]);
        die;
    }
        
    elseif ($Text=='فوروارد 📤') {
        $sql->query("UPDATE `users` SET `command` = 'for_all' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> '⚜️ پیام خود را ارسال کنید تا به همه اعضا فوروارد کنم :',
            'reply_markup'=> $BackPanel
        ]);
    } elseif ($Users['command']=='for_all' && !in_array($Text, ['بازگشت ↪️', '/start', '/panel'])) {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        $sql->query("UPDATE `sendAll` SET `type` = 'forward', `count` = '0', `from_id` = '{$FromId}', `msg_id` = '{$MessageId}' WHERE `id` = '85' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> 'پیام شما به عنوان فوروارد همگانی تنظیم شد
به زودی به همه کاربران ربات ارسال میگردد !',
            'reply_markup'=> $Panel
        ]);
    }
    
    elseif ($Text=='ارسال 📩') {
        $sql->query("UPDATE `users` SET `command` = 'sendAll' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> '⚜️ پیام خود را در قالب متن یا رسانه کپشن دار ارسال کنید تا به همه اعضا ارسال کنم :',
            'reply_markup'=> $BackPanel
        ]);
    } elseif ($Users['command']=='sendAll' && !in_array($Text, ['بازگشت ↪️', '/start', '/panel'])) {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        if (isset($Update->message->text)) {
            $file_type = 'text'; $text = $Update->message->text ?? '-';
        } elseif (isset($Update->message->photo)) {
            $file_type = 'photo'; $media = $Update->message->photo[2]->file_id ?? '-';
        } elseif (isset($Update->message->video)) {
            $file_type = 'video'; $media = $Update->message->video->file_id ?? '-';
        } elseif (isset($Update->message->document)) {
            $file_type = 'document'; $media = $Update->message->document->file_id ?? '-';
        } $caption = $Update->message->caption ?? $text;
        $sql->query("UPDATE `sendAll` SET `type` = 'send', `count` = '0', `sendtype` = '{$file_type}', `txt` = '{$caption}', `media` = '{$media}', `from_id` = '{$FromId}' WHERE `id` = '85' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> 'پیام شما به عنوان پیام همگانی تنظیم شد
به زودی به همه کاربران ربات ارسال میگردد !',
            'reply_markup'=> $Panel
        ]);
    }

    elseif ($Text=='بلاک کردن ⚠️') {
        $sql->query("UPDATE `users` SET `command` = 'block_user' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> '⚜️ آیدی عددی کاربر مورد نظر را ارسال نمایید :',
            'reply_markup'=> $BackPanel
        ]);
    } elseif ($Users['command']=='block_user' && !in_array($Text, ['بازگشت ↪️', '/start', '/panel'])) {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        if ($sql->query("SELECT `id` FROM `users` WHERE `id` = '{$Text}'")->num_rows>0) {
            $Query = $sql->query("SELECT * FROM `users` WHERE `id` = '{$Text}' LIMIT 1")->fetch_assoc();
            if ($Query['block']==0) {
                $sql->query("UPDATE `users` SET `block` = '1' WHERE `id` = '{$Text}' LIMIT 1");
                bot('sendMessage', [
                    'chat_id'=> $Text,
                    'text'=> 'شما توسط مدیران ربات بلاک شدید!',
                    'reply_markup'=> $Remove
                ]);
                bot('sendMessage', [
                    'chat_id'=> $FromId,
                    'text'=> 'کاربر مورد نظر با موفقیت بلاک شد!',
                    'reply_markup'=> $Panel
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id'=> $FromId,
                    'text'=> 'کاربر مورد نظر از قبل بلاک میباشد !',
                    'reply_markup'=> $BackPanel
                ]);
            }
        } else {
            bot('sendMessage', [
                'chat_id'=> $FromId,
                'text'=> 'کاربر مورد نظر عضو ربات نمیباشد !',
                'reply_markup'=> $BackPanel
            ]);
        }
    }
    
    elseif ($Text=='آنبلاک کردن 🌀') {
        $sql->query("UPDATE `users` SET `command` = 'unblock_user' WHERE `id` = '{$FromId}' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> '⚜️ آیدی عددی کاربر مورد نظر را ارسال نمایید :',
            'reply_markup'=> $BackPanel
        ]);
    } elseif ($Users['command']=='unblock_user' && !in_array($Text, ['بازگشت ↪️', '/start', '/panel'])) {
        $sql->query("UPDATE `users` SET `command` = 'none' WHERE `id` = '{$FromId}' LIMIT 1");
        if ($sql->query("SELECT `id` FROM `users` WHERE `id` = '{$Text}'")->num_rows>0) {
            $Query = $sql->query("SELECT * FROM `users` WHERE `id` = '{$Text}' LIMIT 1")->fetch_assoc();
            if ($Query['block']==1) {
                $sql->query("UPDATE `users` SET `block` = '0' WHERE `id` = '{$Text}' LIMIT 1");
                bot('sendMessage', [
                    'chat_id'=> $Text,
                    'text'=> 'شما توسط مدیران ربات آنبلاک شدید!',
                    'reply_markup'=> $Menu
                ]);
                bot('sendMessage', [
                    'chat_id'=> $FromId,
                    'text'=> 'کاربر مورد نظر با موفقیت آنبلاک شد!',
                    'reply_markup'=> $Panel
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id'=> $FromId,
                    'text'=> 'کاربر مورد نظر از قبل آنبلاک میباشد !',
                    'reply_markup'=> $BackPanel
                ]);
            }
        } else {
            bot('sendMessage', [
                'chat_id'=> $FromId,
                'text'=> 'کاربر مورد نظر عضو ربات نمیباشد !',
                'reply_markup'=> $BackPanel
            ]);
        }
    }
    
    elseif (strpos($Text, '🔑 کلید پاور') !== false) {
        $explode = explode(' ', $Text);
        $Match[2] = str_replace(['[', ']'], null, $explode[3]);
        $type = str_replace(['ON', 'OFF'], ['خاموش' ,'روشن'], $Match[2]);
        if ($Match[2]=='ON')$power=0; else$power=1;
        $sql->query("UPDATE `panel` SET `power` = '{$power}' WHERE `id` = '85' LIMIT 1");
        bot('sendMessage', [
            'chat_id'=> $FromId,
            'text'=> "ربات با موفقیت {$type} شد ✔️",
            'reply_markup'=> $Panel
        ]);
    }
}