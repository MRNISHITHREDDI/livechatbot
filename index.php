<?php
#==================[Final Privacy-Bypass Version]===============#

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
    $firstname = $messageData["from"]["first_name"] ?? 'User';
    $userId = $messageData["from"]["id"] ?? null;

    // --- Main Logic ---

    // Case 1: The message is a /start command
    if ($message === '/start') {
        sendMessage($chatId, "Hello $firstname !!, How can we help you today? ");
    }
    // Case 2: The message is from the ADMIN and IS A REPLY
    else if ($chatId == $adminId && isset($messageData["reply_to_message"])) {
        // Get the text of the message being replied to
        $repliedToText = $messageData["reply_to_message"]["text"] ?? '';
        $reply_id = null;

        // Use a regular expression to find the User ID in the text
        if (preg_match('/User ID: (\d+)/', $repliedToText, $matches)) {
            $reply_id = $matches[1];
        }

        if ($reply_id) {
            // If we found an ID, send the admin's message to that user
            sendMessage($reply_id, $message);
        } else {
            // If no ID was found, inform the admin
            sendMessage($adminId, "⚠️ Could not find a User ID in the message you replied to. Please only reply to messages from users.");
        }
    }
    // Case 3: The message is from a regular USER
    else if ($chatId != $adminId) {
        // Instead of forwarding, create a new message with the user's info
        $forwardText = "<b>New message from:</b> " . htmlspecialchars($firstname) . "\n";
        $forwardText .= "<b>User ID:</b> <code>" . $userId . "</code>\n\n";
        $forwardText .= "<em>" . htmlspecialchars($message) . "</em>";
        
        // Send this new, formatted message to the admin
        sendMessage($adminId, $forwardText);
        
        // Send a confirmation message to the user
        sendMessage($chatId, "message sent ✅");
    }
}

#===================[FUNCTIONS]================#

function sendMessage($chatId, $message) {
    if (!$chatId || !$message) return;
    $text = urlencode($message);
    // Use parse_mode=HTML to render the bold and italic tags
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=HTML';
    @file_get_contents($url);
}

