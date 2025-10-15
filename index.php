<?php
#==================[By Ghostbin01]===============#

// Get secrets from Railway's Environment Variables
$botToken = getenv('BOT_TOKEN');
$adminId = getenv('ADMIN_ID');

// Make errors visible for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$website = "https://api.telegram.org/bot".$botToken;
$update = file_get_contents('php://input');
$update = json_decode($update, TRUE);

// Check if there is a message in the update
if (isset($update["message"])) {
    $messageData = $update["message"];

    // Safely extract message details
    $chatId = $messageData["chat"]["id"] ?? null;
    $message = $messageData["text"] ?? null;
    $message_id = $messageData["message_id"] ?? null;
    $firstname = $messageData["from"]["first_name"] ?? 'User';
    $userId = $messageData["from"]["id"] ?? null;
    $reply_to_message = $messageData["reply_to_message"] ?? null;

    // --- Main Logic ---

    // 1. Handle the /start command
    if ($message === '/start') {
        sendMessage($chatId, "Hello $firstname, this bot is inspired by @LivegramBot\nYour ID: $userId", $message_id);
    }
    // 2. Handle replies FROM the admin
    else if ($chatId == $adminId && isset($reply_to_message)) {
        // Safely get the original user's ID from the message being replied to
        $reply_id = $reply_to_message["forward_from"]["id"] ?? null;
        if ($reply_id) {
            sendMessager($reply_id, $message);
        }
    }
    // 3. Handle messages FROM a user
    else if ($chatId != $adminId) {
        forwardMessage($adminId, $chatId, $message_id);
    }
}

#===================[FUNCTIONS]================#

function sendMessage($chatId, $message, $message_id) {
    if (!$chatId || !$message) return;
    $text = urlencode($message);
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&reply_to_message_id='.$message_id.'&parse_mode=Html';
    file_get_contents($url);
}

function sendMessager($chatId, $message) {
    if (!$chatId || !$message) return;
    $text = urlencode($message);
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=Html';
    file_get_contents($url);
}

function forwardMessage($send, $chatId, $message_id) {
    if (!$send || !$chatId || !$message_id) return;
    $url = $GLOBALS['website'].'/forwardMessage?chat_id='.$send.'&from_chat_id='.$chatId.'&message_id='.$message_id.'&disable_notification=false';
    file_get_contents($url);
}
