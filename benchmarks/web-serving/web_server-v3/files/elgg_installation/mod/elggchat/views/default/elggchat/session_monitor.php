<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Builds the ElggChat Toolbar
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

$basesec = elgg_get_plugin_setting("chatUpdateInterval","elggchat");
if (!$basesec) {
	$basesec = 5;
}
$maxsecs = elgg_get_plugin_setting("maxChatUpdateInterval","elggchat");
if (!$maxsecs) {
	$maxsecs = 30;
}

$sound = elgg_get_plugin_setting("enableSounds","elggchat");
if(empty($sound)) {
	$sound = "no";
}
if ($sound == "yes") {
	elgg_load_js('elggchat_sound');
}

$flash = elgg_get_plugin_setting("enableFlashing","elggchat");
if (empty($flash)) {
	$flash = "no";
}

elgg_require_js('elggchat_scroll');

?>

<script type="text/javascript">

var basesec = <?php echo $basesec;?>;
var maxsecs = <?php echo $maxsecs;?>;
var delay = 1000;

var secs;
var processing = false;
var pollingPause = false;

var lastTimeDataReceived = new Date().getTime();

function InitializeTimer(){
	// Set the length of the timer, in seconds
	secs = basesec;
	tick();
	<?php if($flash == "yes"){?>
		blink_new();
	<?php }?>
}

function blink_new(){
	$(".elggchat_session_new_messages").toggleClass("elggchat_session_new_messages_blink");
	self.setTimeout("blink_new()", 1000);
}

function tick(){
	if(!pollingPause){
		if(!processing){
			if (secs == 0){
				checkForSessions();
			} else {
				secs = secs - 1;
			}
		} else {
			resetTimer();
		}
		self.setTimeout("tick()", delay);
	}
}

function resetTimer(){
	// if needed apply multiplier
	var currentTimeStamp = new Date().getTime();
	var timeDiff = (currentTimeStamp - lastTimeDataReceived) / 1000;

	var interval = Math.ceil((Math.sqrt(Math.pow(basesec * 10 / 2, 2) + (2 * basesec * 10 * timeDiff)) - (basesec * 10 / 2)) / (basesec * 10));
	// reset secs
	secs = basesec * interval;
	if(secs > maxsecs){
		secs = maxsecs;
	}
}

function inviteFriends(sessionid){
	var currentChatWindow = $("#" + sessionid + " .chatmembersfunctions_invite");
	if(currentChatWindow.css("display") != "block"){
		currentChatWindow.html("");
		$("#elggchat_friends_picker .chatmemberinfo").each(function(){
			var friend = $(this).find("a");
			if(!($("#" + sessionid + " .chatmembers a[rel='" + friend.attr('rel') + "']").length > 0)){
				newFriend = "<a href='javascript:addFriend(" + sessionid + ", " + friend.attr('rel') + ")'>";
				newFriend += friend.html();
				newFriend += "</a><br />";
				currentChatWindow.append(newFriend);
			}
		});
	}
	currentChatWindow.slideToggle();
}

function addFriend(sessionid, friend){
	elgg.action('elggchat/invite', {
		data: {
			chatsession: sessionid,
			friend: friend
		},
		success: function() {
			$("#" + sessionid + " .chatmembersfunctions_invite").toggle();
			checkForSessions();
			$("#" + sessionid + " input[name='chatmessage']").focus();
		}
	});
}

function leaveSession(sessionid){
	if(confirm("<?php echo elgg_echo('elggchat:chat:leave:confirm');?>")){
		eraseCookie("elggchat_session_" + sessionid);
		var current = readCookie("elggchat");
		if(current == sessionid){
			eraseCookie("elggchat");
		}
		elgg.action('elggchat/leave', {
			data: {
				chatsession: sessionid
			},
			success: function() {
				$("#" + sessionid).remove();
				checkForSessions();
			}
		});
	}
}

function elggchat_toolbar_resize(){
	$("#elggchat_toolbar_right").css("width", $(window).width() - $("#toggle_elggchat_toolbar").width());
}

function toggleChatToolbar(speed){
	$('#elggchat_toolbar_right').toggle(speed);
	$('#toggle_elggchat_toolbar').toggleClass('minimizedToolbar');

	if($('#toggle_elggchat_toolbar').hasClass('minimizedToolbar')){
		createCookie("elggchat_toolbar_minimized", "true");
		pollingPause = true;
		$('#toggle_elggchat_toolbar').attr("title", "<?php echo elgg_echo("elggchat:toolbar:maximize");?>");
	} else {
		pollingPause = false;
		checkForSessions();
		tick();
		eraseCookie("elggchat_toolbar_minimized");
	$('#toggle_elggchat_toolbar').attr("title", "<?php echo elgg_echo("elggchat:toolbar:minimize");?>");
	}
}

function startSession(friendGUID){
	elgg.action('elggchat/create', {
		data: {
			invite: friendGUID
		},
		success: function(data) {
			if(data){
				checkForSessions();
				openSession(data);
			}
		}
	});
}

function toggleFriendsPicker(){
	$("#elggchat_friends_picker").slideToggle();
}

function scroll_to_bottom(sessionid){
	$("#" + sessionid + " .chatmessages").scrollTo('max', 1000);
}

function notify_new_message(){
	<?php if($sound == "yes"){?>
		var buzzer = new buzz.sound("<?php echo elgg_get_site_url(); ?>mod/elggchat/sound/new_message", {
				formats: [ "ogg", "mp3", "m4a" , "wav"],
				autoplay: true,
				loop: false
			});
	<?php }?>
}

function checkForSessions(firsttime){
	if (typeof firsttime == "undefined") {
		firsttime = false;
	}

	// Starting the work, so stop the timer
	processing = true;
	var tokens = elgg.security.addToken("action/elggchat/poll");
	$.getJSON("<?php echo elgg_get_site_url(); ?>" + tokens, function(data){
	if(typeof(data.sessions) != "undefined"){
		var current = readCookie("elggchat");
		$.each(data.sessions, function(i, session){
			var sessionExists = false;
			$("#" + i).each(function(){
				sessionExists = true;
			});
			if(i != current || sessionExists == false){

				var newSession = "";

				newSession += "<a href='javascript:openSession(" + i + ")'>" + session.name + "</a><div class='elgg-icon elgg-icon-delete-alt' style='float:right' onclick='leaveSession(" + i + ")' title='<?php echo elgg_echo("elggchat:chat:leave");?>'></div>";
				newSession += "<div class='chatsessiondatacontainer'>";
				newSession += "<div class='chatsessiondata'>";
				newSession += "<div class='chatmembers'><table>";
				if(typeof(session.members) != "undefined"){
					$.each(session.members, function(memNum, member){
						newSession += member;
					});
				}

				newSession += "</table></div>";
				newSession += "<div class='chatmembersfunctions'><a href='javascript:inviteFriends(" + i + ")'><?php echo elgg_echo("elggchat:chat:invite"); ?></a>";

				newSession += "</div><div class='chatmembersfunctions_invite'></div>";

				newSession += "<div class='chatmessages'>";
				if(typeof(session.messages) != "undefined"){
					$.each(session.messages, function(msgNum, msg){
						newSession += msg;
					});
				}
				newSession += "</div>";
				newSession += "<div class='elggchatinput'>";
				newSession += "<form>";
				newSession += "<input name='chatsession' type='hidden' value='" + i + "'></input>";
				newSession += "<input name='chatmessage' type='text' autocomplete='off'></input>";
				newSession += "</form>";
				newSession += "</div>";
				newSession += "</div>";
				newSession += "</div>";
				if(sessionExists){
					$("#" + i).html(newSession);
				} else {
					newSession = "<div class='session' id='" + i + "'>" + newSession + "</div>";
					$("#elggchat_sessions").append(newSession);
				}

			} else {
				$("#" + i + ">a").html(session.name);
				var membersData = "";
				if(typeof(session.members) != "undefined"){
					$.each(session.members, function(memNum, member){
						membersData += member;
					});
				}
				$("#" + i + " .chatmembers").html("<table>" + membersData + "</table>");

				var messageData = "";
				var cookie = readCookie("elggchat_session_" + i);

				var lastKnownMsgId = 0;
				if(cookie > 0){
					var lastKnownMsgId = parseInt(readCookie("elggchat_session_" + i));
				}

				if(typeof(session.messages) != "undefined"){
					$.each(session.messages, function(msgNum, msg){
						if(msgNum > lastKnownMsgId || lastKnownMsgId == NaN){
							messageData += msg;
							lastTimeDataReceived = new Date().getTime();
						}
					});
				}
				$("#" + i + " .chatmessages").append(messageData);
			}
		});

		// search for new data
		$(".session").each(function(){

			var sessionid = $(this).attr("id");
			var lastKnownMsgId = parseInt(readCookie("elggchat_session_" + sessionid));
			var newestMsgId = parseInt($("#" + sessionid + " .chatmessages div:last").attr("id"));
			if(newestMsgId > lastKnownMsgId || !lastKnownMsgId){
				if($(this).find(".chatsessiondatacontainer").css("display") != "block" && newestMsgId){
					if(!($("#" + sessionid).hasClass("elggchat_session_new_messages")) && !firsttime){
						notify_new_message();
					}
					$("#" + sessionid).addClass("elggchat_session_new_messages");

					lastTimeDataReceived = new Date().getTime();
				}
			}
		});

		// register submit events on message input
		$(".elggchatinput form").unbind("submit");
		$(".elggchatinput form").bind("submit", function(){
			var input = $.trim($(this).find("input[name='chatmessage']").val());

			if(input != ""){
				var url = elgg.security.addToken("<?php echo elgg_get_site_url();?>action/elggchat/post_message");
				$.post(url, $(this).serialize(), function(data){
					checkForSessions();
				});
			}
			// empty input field
			$(this).find("input[name='chatmessage']").val("");

			return false;
		});

		if(current){
			if($("#" + current + " .chatsessiondatacontainer").css("display") != "block"){
				openSession(current);
			}
			var cookie = readCookie("elggchat_session_" + current);
			if(cookie > 0){
				var lastKnownMsgId = parseInt(cookie);
			} else {
				var lastKnownMsgId = 0;
			}
			var newestMsgId = parseInt($("#" + current + " .chatmessages div:last").attr("id"));

			if(newestMsgId > lastKnownMsgId){
				scroll_to_bottom(current);
				createCookie("elggchat_session_" + current, newestMsgId);
			}
		}
	}

	// build friendspicker
	$("#elggchat_friends a").html("<?php echo elgg_echo("elggchat:friendspicker:info");?> (" + data.friends.online.length + ")");
	if(typeof(data.friends) != "undefined"){
		$("#elggchat_friends_picker").html("");
		var show_offline = "<?php echo elgg_get_plugin_user_setting("show_offline_user", 0, "elggchat")?>";

		var tableDataOnline = "";
		var numOnline = data.friends.online.length;
		$.each(data.friends.online, function(i, friend){
			tableDataOnline += friend;
		});

		var tableDataOffline = "";
		var numOffline = data.friends.offline.length;
		$.each(data.friends.offline, function(i, friend){
			tableDataOffline += friend;
		});

		if ((numOnline < 1) && (show_offline!="yes")) {
			$("#elggchat_friends_picker").append("<?php echo elgg_echo('elggchat:friendspicker:nofriends');?>");
		} else {
			if (numOnline>0) {
				$("#elggchat_friends_picker").append("<h3 class='settings'><?php echo elgg_echo('elggchat:friendspicker:online');?> (" + numOnline + ")</h3><table>"  + tableDataOnline + "</table>");
			}
			if ((numOffline>0) && (show_offline=="yes")) {
				$("#elggchat_friends_picker").append("<h3 class='settings'><?php echo elgg_echo('elggchat:friendspicker:offline');?> (" + numOffline + ")</h3><table>" + tableDataOffline + "</table>");
			}
		}
		$("#elggchat_friends_picker a").each(function(){
			$(this).attr("href","javascript:startSession(" + this.rel + "); toggleFriendsPicker();");
		});
	}

	// Done with all the work
	resetTimer();
	processing = false;
	});
}

function openSession(id){
	$("#"+ id).removeClass("elggchat_session_new_messages");
	var current = $("#" + id + " .chatsessiondatacontainer").css("display");
	eraseCookie("elggchat");
	$("#elggchat_sessions .chatsessiondatacontainer").hide();
	if(current != "block"){
		createCookie("elggchat", id);
		var last = parseInt($("#" + id + " .chatmessages div:last").attr("id"));
		createCookie("elggchat_session_" + id, last);
		$("#" + id + " .chatsessiondatacontainer").toggle();
	}
	scroll_to_bottom(id);
	$("#" + id + " input[name='chatmessage']").focus();
}

/** Cookie Functions */
function createCookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		var expires = "Expires=" + date.toGMTString() + "; ";
	} else {
		var expires = "";
	}
	document.cookie = name + "=" + value + "; " + expires + "Path=/;";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');

	for(var i = 0; i < ca.length; i++){
		var c = ca[i];

		while (c.charAt(0) == ' '){
			c = c.substring(1, c.length);
		}

		if (c.indexOf(nameEQ) == 0){
			return c.substring(nameEQ.length, c.length);
		}
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name, "", -1);
}

$(document).ready(function(){
	if(readCookie("elggchat_toolbar_minimized")){
		toggleChatToolbar(0);
	}

	$(window).resize(function(){
		elggchat_toolbar_resize();
	});
	elggchat_toolbar_resize();
	InitializeTimer();
	checkForSessions(true);
});

</script>

<div id="elggchat_toolbar">
	<div id="elggchat_toolbar_right">
		<div id="elggchat_sessions"></div>

		<div id="elggchat_friends">
			<a href="javascript:toggleFriendsPicker();"></a>
			<div id="elggchat_friends_picker"></div>
		</div>

		<div id="elggchat_extensions">
			<?php
				if(elgg_get_plugin_setting("enableExtensions", "elggchat") == "yes") {
					echo elgg_view("elggchat/extensions");
				}
			?>
		</div>
	</div>

	<div id="toggle_elggchat_toolbar" class="toggle_elggchat_toolbar" onclick="toggleChatToolbar('slow')" title="<?php echo elgg_echo("elggchat:toolbar:minimize");?>"></div>
</div>
