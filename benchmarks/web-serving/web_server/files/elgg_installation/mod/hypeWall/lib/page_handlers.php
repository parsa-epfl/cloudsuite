<?php

namespace hypeJunction\Wall;

/**
 * Handler walls and posts
 *
 * User wall:	wall/owner/<username>
 * Post:		wall/post/<guid>
 *
 * @param array $page
 * @return boolean
 */
function page_handler($page) {

	elgg_push_breadcrumb(elgg_echo('wall'), PAGEHANDLER);

	switch ($page[0]) {
		default :
			$user = elgg_get_logged_in_user_entity();
			forward(PAGEHANDLER . "/owner/$user->username");
			break;

		case 'user' :
		case 'owner' :
			$username = elgg_extract(1, $page);
			$owner = get_user_by_username($username);
			if (!$owner) {
				return false;
			}

			elgg_set_page_owner_guid($owner->guid);

			$title = elgg_echo('wall:owner', array($owner->name));
			elgg_push_breadcrumb($title, PAGEHANDLER . "/owner/$owner->username");

			if (isset($page[2])) {
				$post = get_entity($page[2]);
				if (elgg_instanceof($post)) {
					elgg_push_breadcrumb($post->title);
					$content = elgg_view_entity_list(array($post), array(
						'list_class' => 'wall-post-list',
						'full_view' => true
					));
					$layout = elgg_view_layout('one_sidebar', array(
						'title' => $title,
						'content' => $content,
					));
					echo elgg_view_page($title, $layout);
					return true;
				}
			}

			$content = elgg_view("framework/wall/owner");
			$layout = elgg_view_layout('content', array(
				'title' => $title,
				'content' => $content,
				'filter' => false,
			));
			echo elgg_view_page($title, $layout);
			return true;
			break;

		case 'post' :
			$guid = $page[1];
			$post = get_entity($guid);

			if (!elgg_instanceof($post)) {
				return false;
			}

			forward($post->getURL());
			break;

		case 'group' :
			$guid = elgg_extract(1, $page);
			$group = get_entity($guid);
			if (!elgg_instanceof($group, 'group')) {
				return false;
			}

			elgg_set_page_owner_guid($group->guid);

			$title = elgg_echo('wall:owner', array($group->name));
			elgg_push_breadcrumb($title, PAGEHANDLER . "/group/$group->guid");

			if (isset($page[2])) {
				$post = get_entity($page[2]);
				if (elgg_instanceof($post)) {
					elgg_push_breadcrumb($post->title);
					$content = elgg_view_entity_list(array($post), array(
						'list_class' => 'wall-post-list',
						'full_view' => true
					));
					$layout = elgg_view_layout('one_sidebar', array(
						'title' => $title,
						'content' => $content,
					));
					echo elgg_view_page($title, $layout);
					return true;
				}
			}

			$content = elgg_view("framework/wall/group");
			$layout = elgg_view_layout('content', array(
				'title' => $title,
				'content' => $content,
				'filter' => false,
			));
			echo elgg_view_page($title, $layout);
			return true;
			break;
	}

	return false;
}
