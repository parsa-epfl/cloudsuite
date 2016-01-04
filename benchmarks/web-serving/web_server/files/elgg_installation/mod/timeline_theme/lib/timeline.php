<?php

/**
* Get page components to list a user's or all blogs.
*
* @param int $container_guid The GUID of the page owner or NULL for all blogs
* @return array
*/

function timeline_get_page_content_list($container_guid = NULL) {

$return = array();

$return['filter_context'] = $container_guid ? 'mine' : 'all';

$options = array(
'type' => 'object',
'subtype' => 'blog',
'full_view' => false,
'no_results' => elgg_echo('blog:none'),
);

$current_user = elgg_get_logged_in_user_entity();

if ($container_guid) {
// access check for closed groups
elgg_group_gatekeeper();

$options['container_guid'] = $container_guid;
$container = get_entity($container_guid);
if (!$container) {

}
$return['title'] = elgg_echo('blog:title:user_blogs', array($container->name));

$crumbs_title = $container->name;
elgg_push_breadcrumb($crumbs_title);

if ($current_user && ($container_guid == $current_user->guid)) {
$return['filter_context'] = 'mine';
} else if (elgg_instanceof($container, 'group')) {
$return['filter'] = false;
} else {
// do not show button or select a tab when viewing someone else's posts
$return['filter_context'] = 'none';
}
} else {
$return['filter_context'] = 'all';
$return['title'] = elgg_echo('blog:title:all_blogs');
elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('blog:blogs'));
}

elgg_register_title_button();

$return['content'] = elgg_list_entities($options);

return $return;
}



































/**
 * Get page components to show blogs with publish dates between $lower and $upper
 *
 * @param int $owner_guid The GUID of the owner of this page
 * @param int $lower      Unix timestamp
 * @param int $upper      Unix timestamp
 * @return array
 */
function timeline_get_page_content_archive($owner_guid, $lower = 0, $upper = 0) {

	$now = time();

	$owner = get_entity($owner_guid);
	elgg_set_page_owner_guid($owner_guid);

	$crumbs_title = $owner->name;
	if (elgg_instanceof($owner, 'user')) {
		$url = "blog/owner/{$owner->username}";
	} else {
		$url = "blog/group/$owner->guid/all";
	}
	elgg_push_breadcrumb($crumbs_title, $url);
	elgg_push_breadcrumb(elgg_echo('blog:archives'));

	if ($lower) {
		$lower = (int)$lower;
	}

	if ($upper) {
		$upper = (int)$upper;
	}

	$options = array(
		'type' => 'object',
		'subtype' => 'blog',
		'full_view' => FALSE,
	);

	if ($owner_guid) {
		$options['container_guid'] = $owner_guid;
	}

	// admin / owners can see any posts
	// everyone else can only see published posts
	if (!(elgg_is_admin_logged_in() || (elgg_is_logged_in() && $owner_guid == elgg_get_logged_in_user_guid()))) {
		if ($upper > $now) {
			$upper = $now;
		}

		$options['metadata_name_value_pairs'] = array(
			array('name' => 'status', 'value' => 'published')
		);
	}

	if ($lower) {
		$options['created_time_lower'] = $lower;
	}

	if ($upper) {
		$options['created_time_upper'] = $upper;
	}

	$list = elgg_list_entities_from_metadata($options);
	if (!$list) {
		$content = elgg_echo('blog:none');
	} else {
		$content = $list;
	}

	$title = elgg_echo('date:month:' . date('m', $lower), array(date('Y', $lower)));

	return array(
		'content' => $content,
		'title' => $title,
		'filter' => '',
	);
}