<?php
#==================[Final Version with Debugging]===============#

// Get secrets from Railway's Environment Variables
$botToken = getenv('BOT_TOKEN');
$adminId = getenv('ADMIN_ID');

// Make errors visible for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$website = "https://api.telegram.org/bot".$botToken;
$update = file_get_contents('php://input');
$update = json_decode($update, TRUE);

// Check if there is a message in the update to prevent errors
if (isset($update["message"])) {
    $messageData = $update["message"];

    // Safely get all the details from the message
    $chatId = $messageData["chat"]["id"] ?? null;
    $message = $messageData["text"] ?? null;
    $message_id = $messageData["message_id"] ?? null;
    $firstname = $messageData["from"]["first_name"] ?? 'User';
    $userId = $messageData["from"]["id"] ?? null;

    // --- Main Logic ---

    // Case 1: The message is a /start command
    if ($message === '/start') {
        sendMessage($chatId, "Hello $firstname !!, How can we help you today? ");
    }
    // Case 2: The message is from the ADMIN and IS A REPLY
    else if ($chatId == $adminId && isset($messageData["reply_to_message"])) {
        
        // --- START DEBUGGING BLOCK ---
        // Send the structure of the replied-to message back to the admin for inspection
        $debug_info = json_encode($messageData["reply_to_message"], JSON_PRETTY_PRINT);
        sendMessager($adminId, "<b>DEBUG INFO:</b>\n<pre>" . htmlspecialchars($debug_info) . "</pre>");
        // --- END DEBUGGING BLOCK ---

        if (isset($messageData["reply_to_message"]["forward_from"]["id"])) {
            $reply_id = $messageData["reply_to_message"]["forward_from"]["id"];
            // Attempt to send the reply to the user
            sendMessager($reply_id, $message);
        } else {
             // If the expected data isn't found, notify the admin
            sendMessager($adminId, "<b>DEBUG:</b> Reply failed. Could not find original sender's ID in the message data.");
        }
    }
    // Case 3: The message is from a regular USER
    else if ($chatId != $adminId) {
        // Forward the user's message to the admin
        forwardMessage($adminId, $chatId, $message_id);
    }
}

#===================[FUNCTIONS]================#

function sendMessage($chatId, $message) {
    if (!$chatId || !$message) return;
    $text = urlencode($message);
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=Html';
    @file_get_contents($url);
}

function sendMessager($chatId, $message) {
    if (!$chatId || !$message) return;
    $text = urlencode($message);
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=Html';
    @file_get_contents($url);
}

function forwardMessage($send, $chatId, $message_id) {
    if (!$send || !$chatId || !$message_id) return;
    $url = $GLOBALS['website'].'/forwardMessage?chat_id='.$send.'&from_chat_id='.$chatId.'&message_id='.$message_id.'&disable_notification=false';
    @file_get_contents($url);
}

