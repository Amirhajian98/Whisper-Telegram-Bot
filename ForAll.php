<?php // نیاز به کرون جاب هر 2 دقیقه یک بار
@require_once 'Config.php';
$send = $sql->query("SELECT * FROM `sendAll` WHERE `id` = '85' LIMIT 1")->fetch_assoc();

function bot($method, $data=[]) {
    global $Config; $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$Config['token'].'/'.$method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    return json_decode(curl_query($ch));
}

if ($send['type']=='forward') {
    $Query = $sql->query("SELECT `id` FROM `users` LIMIT 85 OFFSET {$send['count']}");
    while($users = $Query->fetch_assoc()) {
        bot('ForwardMessage',[
            'chat_id'=>$users['id'],
            'from_chat_id'=>$send['from_id'],
            'message_id'=>$send['msg_id']
        ]);
    } $count = $send['count']+85;
    $sql->query("UPDATE `sendAll` SET `count` = '{$count}' WHERE `id` = '85' LIMIT 1");
    if ($count >= $sql->query("SELECT `id` FROM `users`")->num_rows) {
        bot('sendMessage',[
            'chat_id'=>$send['from_id'],
            'text'=>'پیام شما با موفقیت به همه اعضا فوروارد شد !'
        ]);
        $sql->query("UPDATE `sendAll` SET `type` = '-', `count` = '0', `from_id` = '0', `msg_id` = '0' WHERE `id` = '85' LIMIT 1");
    }
}

elseif ($send['type']=='send') {
    $Query = $sql->query("SELECT `id` FROM `users` LIMIT 85 OFFSET {$send['count']}");
    while($users = $Query->fetch_assoc()) {
        if ($send['sendtype']=='text') {
            if ($send['txtcap'] != '-' || $send['txtcap'] != '') {
                bot('sendMessage',[
                    'chat_id'=>$users['id'],
                    'text'=>$send['txtcap'],
                    'parse_mode'=>'html',
                    'disable_web_page_preview'=>true
                ]);
            }
        } elseif ($send['txtcap']=='-' || $send['txtcap']=='') {
            if ($send['sendtype']=='photo') {
                bot('sendPhoto',[
                    'chat_id'=>$users['id'],
                    'photo'=>$send['media']
                ]);
            } elseif ($send['sendtype']=='video') {
                bot('sendVideo',[
                    'chat_id'=>$users['id'],
                    'video'=>$send['media']
                ]);
            } elseif ($send['sendtype']=='document') {
                bot('sendDocument',[
                    'chat_id'=>$users['id'],
                    'document'=>$send['media']
                ]);
            }
        } 
        else {
            if ($send['sendtype']=='photo') {
                bot('sendPhoto',[
                    'chat_id'=>$users['id'],
                    'photo'=>$send['media'],
                    'txtcap'=>$send['txtcap'],
                    'parse_mode'=>'html'
                ]);
            } elseif ($send['sendtype']=='video') {
                bot('sendVideo',[
                    'chat_id'=>$users['id'],
                    'video'=>$send['media'],
                    'txtcap'=>$send['txtcap'],
                    'parse_mode'=>'html'
                ]);
            } elseif ($send['sendtype']=='document') {
                bot('sendDocument',[
                    'chat_id'=>$users['id'],
                    'document'=>$send['media'],
                    'txtcap'=>$send['txtcap'],
                    'parse_mode'=>'html'
                ]);
            }
        }
    } $count = $send['count']+85;
    $sql->query("UPDATE `sendAll` SET `count` = '{$count}' WHERE `id` = '85' LIMIT 1");
    if ($count >= $sql->query("SELECT `id` FROM `users`")->num_rows) {
        bot('sendMessage',[
            'chat_id'=>$send['from_id'],
            'text'=>'پیام شما با موفقیت به همه اعضا ارسال شد !'
        ]);
        $sql->query("UPDATE `sendAll` SET `type` = '-', `count` = '0', `sendtype` = '-', `txtcap` = '-', `media` = '-', `from_id` = '0' WHERE `id` = '85' LIMIT 1");
    }
}