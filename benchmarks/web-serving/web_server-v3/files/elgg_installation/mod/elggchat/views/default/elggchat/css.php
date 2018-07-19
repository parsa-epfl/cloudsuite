<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * All the ElggChat CSS can be found here
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
?>

#elggchat_toolbar {
	position: fixed;
	bottom: 0px;
	left: 0px;
	min-height: 25px;
	background: #DEDEDE;
	z-index: 9999;
}

*html #elggchat_toolbar {
	position: fixed;
	bottom: 0px;
	left: 0px;
	min-height: 25px;
	background: #DEDEDE;
	z-index: 9999;
}

#elggchat_toolbar_right {
	float: right;
}

.session {
	float: right;
	background: #E4ECF5;
	padding: 3px 3px 0 3px;
	margin: 1px 3px 0px 3px;
	height: 23px;
	/* ie fix */
	max-width:200px;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	text-decoration: none;
	text-shadow: none;
	color: black;
	border: 1px solid #4690D6;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}

.session a {
	color: #4690D6;
}

.session a:hover {
	color: #4690D6;
	text-decoration: underline;
}

.elggchat_session_new_messages {
	background: #333333;
}

.elggchat_session_new_messages.elggchat_session_new_messages_blink {
	background: #E4ECF5;
}

#elggchat_extensions {
	float: left;
	height: 25px;
	padding: 0 3px 0 3px;
}

#elggchat_friends {
	position: relative;
	float: right;
	width: 120px;
	height: 23px;
	padding: 3px 3px 0 3px;
	margin: 1px 3px 0px 3px;
	background: white;
	border: 1px solid #CCCCCC;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}

#elggchat_friends a {
	color: #4690D6;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	text-decoration: none;
	text-shadow: none;
}

#elggchat_friends a:hover {
	text-decoration: underline;
}

#elggchat_friends_picker {
	display: none;
	position: absolute;
	bottom: 25px;
	right: -1px;
	width: 120px;
	background: white;
	color: black;
	padding: 3px 3px 2px 3px;
	overflow-x: hidden;
	max-height: 300px;
	overflow-y: auto;
	white-space: nowrap;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	text-decoration: none;
	text-shadow: none;
	border: 1px solid #CCCCCC;
	-moz-border-radius-topleft: 5px;
	-moz-border-radius-topright: 5px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;
}

#elggchat_friends_picker a {
	color: #4690D6;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	text-decoration: none;
	text-shadow: none;
}

#elggchat_friends_picker a:hover {
	text-decoration: underline;
}


#elggchat_friends_picker .settings {
	font-size: 90%;
	background-color: #DEDEDE;
	color: #0054A7;
	padding: 2px 2px 2px 2px;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	font-weight: bold;
	text-decoration: none;
	text-shadow: none;
	vertical-align: middle;
}

.toggle_elggchat_toolbar {
	float:left;
	width: 15px;
	height: 25px;
	background: transparent url(<?php echo elgg_get_site_url(); ?>mod/elggchat/_graphics/minimize.png) repeat-x left center;
}

.minimizedToolbar {
	width: 15px;
	height: 25px;
	background-position: right center;
	-moz-border-radius-topright: 5px;
	-webkit-border-top-right-radius: 5px;
	border-top-right-radius: 5px;
}

.messageWrapper {
	background: white;
	color: black;
	padding: 10px;
	margin: 0 5px 5px 5px;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
	border-radius: 8px;
}

.messageWrapper table {
	background: white;
	height: 0px;
	font-size: 11px;
}

.systemMessageWrapper {
	padding: 3px;
	margin: 0 5px 5px 5px;
	color: red;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
	border-radius: 8px;
}

.messageIcon {
	margin-right: 7px;
}

.messageName {
	border-bottom: 1px solid #DDDDDD;
	width: 100%;
	font-weight: bold;
	color: #4690D6;
}

.chatsessiondatacontainer {
	position: relative;
	width: 200px;
	display: none;
}

.chatsessiondata {
	position: absolute;
	bottom: 19px;
	width: 206px;
	margin: 0 -4px;
	max-height: 600px;
	border: 1px solid #4690D6;
	border-bottom: 0px;
	background: #E4ECF5;
	-moz-border-radius-topright: 5px;
	-moz-border-radius-topleft: 5px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;
}

.chatmembers {
	border-bottom: 1px solid #DEDEDE;
	max-height: 154px;
	overflow-y: auto;
	padding-top: 2px;
	padding-left: 2px;
}

.chatmembers a {
	color: #0054A7;
	font-family: "Lucida Grande",Arial,Tahoma,Verdana,sans-serif;
	font-weight: bold;
	text-decoration: none;
	text-shadow: none;
}

.chatmembers a:hover {
	color: #0054A7;
	text-decoration: underline;
}

.chatmember td {
	vertical-align: middle;
}

.chatmembers .chatmemberinfo {
	width: 100%;
}

.chatmembersfunctions {
	text-align: right;
	padding-right: 2px;
	height: 20px;
	border-bottom: 1px solid #DEDEDE;
	font-size: 10px;
}

.chatmembersfunctions a {
	color: #0054A7;
}

.chatmembersfunctions a:hover {
	color: #0054A7;
	text-decoration: underline;
}

.chatmembersfunctions_invite {
	display: none;
	text-align: left;
	position: absolute;
	background: #333333;
	width: 100%;
	opacity: 0.8;
	filter: alpha(opacity=80);
	max-height: 250px;
	overflow-x: hidden;
	overflow-y: auto;
}

.chatmembersfunctions_invite a {
	color: #FFFFFF;
	padding: 3px;
}

.online_status_chat {
	width: 24px;
	height: 24px;
	background: transparent url("<?php echo elgg_get_site_url(); ?>mod/elggchat/_graphics/online_status.png") no-repeat 0 0;
}

.online_status_idle {
	background-position: 0 -24px;
}

.online_status_inactive {
	background-position: 0 -48px;
}

.chatmessages {
	min-height: 250px;
	max-height: 400px;
	overflow-y: auto;
}

.elggchatinput {
	height: 22px;
	background: #FFFFFF url("<?php echo elgg_get_site_url(); ?>mod/elggchat/_graphics/chatwindow/chat_input.png") no-repeat 1px 50%;
	color: black;
	padding: 2px 2px 2px 18px;
	border-top: 1px solid #DEDEDE;
	border-bottom: 1px solid #DEDEDE;
}

.elggchatinput input {
	border: none;
	color: black;
	font-size: 100%;
	padding: 2px;
}

.elggchatinput input:focus {
	border: none;
	background: none;
}
