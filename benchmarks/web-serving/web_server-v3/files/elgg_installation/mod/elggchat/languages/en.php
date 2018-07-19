<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * English language file
 *
 * @package elggchat
 * @author ColdTrick IT Solutions
 * @copyright Coldtrick IT Solutions 2009-2014
 * @link http://www.coldtrick.com/
 *
 * for Elgg 1.8 and newer by iionly (iionly@gmx.de)
 * @copyright iionly 2014
 * @link https://github.com/iionly
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

return array(
	'admin:administer_utilities:elggchat' => 'ElggChat',
	'elggchat' => "ElggChat",
	'elggchat:title' => "ElggChat",
	'elggchat:chat:profile:invite' => "Invite for chat",
	'elggchat:chat:send' => "Send",

	'elggchat:friendspicker:info' => "Friends online",
	'elggchat:friendspicker:online' => "Online",
	'elggchat:friendspicker:offline' => "Offline",
	'elggchat:friendspicker:nofriends' => "currently none",

	'elggchat:chat:invite' => "Invite",
	'elggchat:chat:leave' => "Leave",
	'elggchat:chat:leave:confirm' => "Are you sure you wish to leave this chat session?",

	'elggchat:action:invite' => "<b>%s</b> invited <b>%s</b>",
	'elggchat:action:leave' => "<b>%s</b> left the chat session",
	'elggchat:action:join' => "<b>%s</b> joined the chat session",

	'elggchat:session:name:default' => "Chat session (%s)",
	'elggchat:session:onlinestatus' => "Last action: %s",

	'elggchat:session_list_introduction' => 'On this page you see a list of the current open chat sessions on your community site. You can see a more detailed view of a specific session by clicking on the \'Details\' button or you can delete a chat session by clicking on the \'Delete session\' button.',
	'elggchat:session_details_introduction' => 'On this page you can see the session details of the chat session you\'ve selected on the chat session list page. You can see who\'s currently logged-in to this chat session and all the messages posted. You can also add a message to this chat session. It will appear as system message within the chat session with the prefix \'[Message from admin]\'.',
	'elggchat:admin_message' => '[Message from admin]: ',
	'elggchat:post_admin_message' => 'Add a message to this chat session: ',
	'elggchat:post_admin_message_success' => 'The message was posted successfully to the chat session.',
	'elggchat:post_admin_message_error' => 'The message could not be posted to the chat session.',
	'elggchat:chatsession_delete' => 'Delete session',
	'elggchat:session_delete_success' => 'The chat session was deleted.',
	'elggchat:session_delete_error' => 'The chat session could not get deleted.',
	'elggchat:sessions_backbutton' => 'Back to chat sessions list',
	'elggchat:session:no_session_details' => 'The chat session does either not exist or there was an error retrieving the details of the chat session.',
	'elggchat:session:no_sessions' => "There are currently no open chat sessions.",
	'elggchat:chatsession_deleteconfirm' => 'Do you really want to delete this chat session now and without warning the members in the chat session?',
	'elggchat:chatsession_details' => 'Details',

	'elggchat:session:guid' => "Chat Session GUID: %s",
	'elggchat:session_details:guid' => "Details of chat session with GUID %s",
	'elggchat:session:last_updated' => "Last update: ",
	'elggchat:session:chat_participants' => "Members in this chat session: ",
	'elggchat:session:no_participants' => "There are no members participating in this chat session at the moment.",
	'elggchat:session:number_chat_participants' => "Number of chat members: ",
	'elggchat:session:session_messages' => "Messages posted in this chat session: ",
	'elggchat:session:no_messages' => "There have been no messages posted in this chat session.",
	'elggchat:session:number_session_messages' => "Number of chat messages: ",

	'elggchat:crondone' => "Chat sessions cleaned up\n",

	// Plugin settings
	'elggchat:admin:settings:hour' => "%s hour",
	'elggchat:admin:settings:hours' => "%s hours",
	'elggchat:admin:settings:days' => "%s days",

	'elggchat:admin:settings:maxsessionage' => "Max time a chat session can remain idle before cleanup",
	'elggchat:admin:settings:keepsessions' => "Keep all chat sessions and don't clean them up automatically (admins still can delete them manually)",

	'elggchat:admin:settings:chatupdateinterval' => "Polling interval (seconds) of the chat window",
	'elggchat:admin:settings:maxchatupdateinterval' => "Every 10 times of polling with no data returned the polling interval will be multiplied until it reaches this maximum (seconds)",
	'elggchat:admin:settings:enable_sounds' => "Enable sounds for new messages (only plays when chat window is minimized)",
	'elggchat:admin:settings:enable_flashing' => "Enable flashing for new messages (only blinks when chat window is minimized)",
	'elggchat:admin:settings:enable_extensions' => "Enable extensions (the ElggChat plugin comes with no extensions itself but it is possible for other plugins to add some functionality on their own to the ElggChat bar by extending the view \"elggchat/extensions\", if this option is enabled)",

	'elggchat:admin:settings:online_status:active' => "Max number of seconds before user will be idle",
	'elggchat:admin:settings:online_status:inactive' => "Max number of seconds before user will be inactive",

	// User settings
	'elggchat:usersettings:enable_chat' => "Enable ElggChat Toolbar? ",
	'elggchat:usersettings:allow_contact_from' => "Allow the following to contact me by chat: ",
	'elggchat:usersettings:allow_contact_from:all' => "Everyone can contact me",
	'elggchat:usersettings:allow_contact_from:friends' => "Only my friends can contact me",
	'elggchat:usersettings:allow_contact_from:none' => "Nobody can contact me",
	'elggchat:usersettings:show_offline_user' => "Show offline members? ",

	// Toolbar actions
	'elggchat:toolbar:minimize' => "Minimize ElggChat Toolbar",
	'elggchat:toolbar:maximize' => "Maximize ElggChat Toolbar",
);