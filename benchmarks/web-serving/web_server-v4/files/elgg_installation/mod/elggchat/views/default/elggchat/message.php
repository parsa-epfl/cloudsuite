<?php
$in=array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si');
$out=array('<a href="$1" target="_new" rel=nofollow>$1</a> ','<a href="http://$1" rel=\'nofollow\' target="_new">$1</a>');
$message = $vars['message'];
$offset = $vars['offset'];
$user = $vars['message_owner'];

$smiley_url = elgg_get_site_url() . "action/elggchat/get_smiley?" . "smiley=";

$smileys = array(":(|)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/monkey.gif") . "'>",
	"=D" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/smile.gif") . "'>",
	"=)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/smile.gif") . "'>",
	":)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/smile.gif") . "'>",
	";)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/wink.gif") . "'>",
	":(" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/frown.gif") . "'>",
	":D" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/grin.gif") . "'>",
	"x-(" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/angry.gif") . "'>",
	"B-)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/cool.gif") . "'>",
	":'(" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/cry.gif") . "'>",
	"\m/" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/rockout.gif") . "'>",
	":-o" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/shocked.gif")."'>",
	":-/" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/slant.gif") . "'>",
	":-|" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/straightface.gif") . "'>",
	":P" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/tongue.gif") . "'>",
	":-D" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/nose_grin.gif") . "'>",
	";-)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/wink_nose.gif") . "'>",
	";^)" => "<img src='" . elgg_add_action_tokens_to_url($smiley_url . "animated_smileys/wink_big_nose.gif") . "'>",
);

if ($message->access_id != ACCESS_PRIVATE || $user->guid == elgg_get_logged_in_user_guid()) {
	$result = "";
	if ($message->name == ELGGCHAT_MESSAGE) {
		$result .= "<div name='message' id='" .  $offset . "' class='messageWrapper'>";

		$result .= "<table><tr><td rowspan='2'>";
		$result .= elgg_view('output/img', array('src' => elgg_format_url($user->getIconURL('tiny')), 'class' => 'messageIcon'));
		$result .= "</td><td class='messageName'>" . $user->name . "</td></tr>";

		$result .= "<tr><td>";

		$message->value=preg_replace( $in,$out, $message->value);
		$result .= str_ireplace(array_keys($smileys), array_values($smileys), $message->value);
		$result .= "</td></tr></table>";
		$result .= "</div>";
	} elseif ($message->name == ELGGCHAT_SYSTEM_MESSAGE) {
		$result .= "<div name='message' id='" .  $offset . "' class='systemMessageWrapper'>";
		$result .= $message->value;
		$result .= "</div>";
	}

	echo $result;
}
?>