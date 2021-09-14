<?php
/**
 * A topbar link to return to original user.
 *
 * @uses $vars['user_guid'] The GUID of the original user
 */

$original_user_guid = elgg_extract('user_guid', $vars);
$original_user = get_user($original_user_guid);
if (!$original_user) {
	return;
}

$logged_in_user = elgg_get_logged_in_user_entity();
echo elgg_view('output/img', [
	'src' => $logged_in_user->getIconURL('topbar'),
	'alt' => $logged_in_user->getDisplayName(),
]);

echo elgg_view_icon('long-arrow-right');

echo elgg_view('output/img', [
	'src' => $original_user->getIconURL('topbar'),
	'alt' => $original_user->getDisplayName(),
]);
