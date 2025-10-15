<?php
#==================[By Ghostbin01]===============#

// Get secrets from Railway's Environment Variables
$botToken = getenv('BOT_TOKEN');
$adminId = getenv('ADMIN_ID'); // The admin's Telegram ID

$website = "https://api.telegram.org/bot".$botToken;
ini_set('display_errors', 1);
error_reporting(E_ALL);
$update = file_get_contents('php://input');
$update = json_decode($update, TRUE);

// Extract message details (no changes here)
$chatId = $update["message"]["chat"]["id"];
$userId = $update["message"]["from"]["id"];
$firstname = $update["message"]["from"]["first_name"];
$message = $update["message"]["text"];
$message_id = $update["message"]["message_id"];
$reply_to_message = $update["message"]["reply_to_message"];
$reply_id = $update["message"]["reply_to_message"]["forward_from"]["id"];

#===============[CMDS]================#

if($message == '/start' || $message == '!start' || $message == '.start'){
    sendMessage($chatId, "Hello $firstname, this bot is inspired by @LivegramBot\nYour ID: $userId", $message_id);
}

// Main logic using the environment variable
if($chatId == $adminId){ // Check if the message is from the admin
    if($reply_to_message){
        sendMessager($reply_id, $message);
    }
} else {
    forwardMessage($adminId, $chatId, $message_id); // Forward to the admin
}
#===================[FUNCIONES]=============#

function sendMessage($chatId, $message, $message_id){
	$text = urlencode($message);
	$url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&reply_to_message_id='.$message_id.'&parse_mode=Html';
	file_get_contents($url);
}
function sendMessager($chatId, $message){
	$text = urlencode($message);
	$url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=Html';
	file_get_contents($url);
}

function forwardMessage($send, $chatId , $message_id){
	$url = $GLOBALS['website'].'/forwardMessage?chat_id='.$send.'&from_chat_id='.$chatId.'&message_id='.$message_id.'&disable_notification=false';
	file_get_contents($url);
}
