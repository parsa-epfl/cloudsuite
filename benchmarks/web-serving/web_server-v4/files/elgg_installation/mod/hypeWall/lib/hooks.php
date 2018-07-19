<?php

namespace hypeJunction\Wall;

use Elgg_Notifications_Notification;
use ElggMenuItem;
use ElggRiverItem;

/**
 * Give wall posts their own URL
 *
 * @param string $hook		Equals 'entity:url'
 * @param string $type		Equals 'object'
 * @param string $return	Current URL
 * @param array $params		Additional params
 * @return string			Filtered URL
 */
function url_handler($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (elgg_instanceof($entity, 'object', 'hjwall')) {
		$container = $entity->getContainerEntity();
		if (elgg_instanceof($container, 'group')) {
			return elgg_normalize_url(PAGEHANDLER . '/group/' . $container->guid . '/' . $entity->guid);
		} else if (elgg_instanceof($container, 'user')) {
			return elgg_normalize_url(PAGEHANDLER . '/owner/' . $container->username . '/' . $entity->guid);
		}
	}

	return $return;
}

/**
 * Allow users to post on each other's walls
 * Container here is the wall, and can be a user or group
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * @return boolean
 */
function container_permissions_check($hook, $type, $return, $params) {
	$container = elgg_extract('container', $params);
	$user = elgg_extract('user', $params);
	$subtype = elgg_extract('subtype', $params);

	if ($subtype !== 'hjwall') {
		return $return;
	}

	if (elgg_instanceof($container, 'user') && elgg_instanceof($user, 'user')) {
		if ($container->isFriend($user)) {
			return true;
		} else {
			$third_party_wall_global = elgg_get_plugin_setting('third_party_wall', PLUGIN_ID);
			$third_party_wall_user = elgg_get_plugin_user_setting('third_party_wall', $container->guid, PLUGIN_ID);

			if ($third_party_wall_global && $third_party_wall_user) {
				return true;
			}
		}
	}

	return $return;
}

/**
 * Add actions to the wall post menu
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'menu:entity'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array			Updated menu
 */
function entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (elgg_instanceof($entity, 'object', 'hjwall') || elgg_instanceof($entity, 'object', 'thewire')) {

		$logged_in = elgg_get_logged_in_user_entity();
		if (check_entity_relationship($logged_in->guid, 'tagged_in', $entity->guid)) {
			$return[] = ElggMenuItem::factory(array(
						'name' => 'remove_tag',
						'text' => elgg_echo('wall:remove_tag'),
						'title' => elgg_echo('wall:remove_tag'),
						'priority' => 800,
						'href' => "action/wall/remove_tag?guid=$entity->guid",
						'is_action' => true,
			));
		}

		if ($entity->canEdit()) {
			if (elgg_instanceof($entity, 'object', 'hjwall')) {
				$action = "action/wall/delete?guid=$entity->guid";
			} else if (elgg_instanceof($entity, 'object', 'thewire')) {
				$action = "action/thewire/delete?guid=$entity->guid";
			}
			if ($action) {
				$return[] = ElggMenuItem::factory(array(
							'name' => 'delete',
							'text' => elgg_view_icon('delete'),
							'title' => elgg_echo('wall:delete'),
							'priority' => 900,
							'href' => $action,
							'is_action' => true,
							'link_class' => 'elgg-requires-confirmation'
				));
			}
		}

		if ($params['handler'] == 'wall') {
			foreach ($return as $key => $item) {
				if ($item instanceof ElggMenuItem && $item->getName() == 'edit') {
					unset($return[$key]);
				}
			}
		}
	}

	return $return;
}

/**
 * Allow users to delete and remove tags from the river
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'menu:river'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array			Updated menu
 */
function river_menu_setup($hook, $type, $return, $params) {

	$item = elgg_extract('item', $params);

	if (!($item instanceof ElggRiverItem)) {
		return $return;
	}

	$object = $item->getObjectEntity();

	if (elgg_instanceof($object, 'object', 'hjwall') || elgg_instanceof($object, 'object', 'thewire')) {

		$logged_in = elgg_get_logged_in_user_entity();
		if (check_entity_relationship($logged_in->guid, 'tagged_in', $object->guid)) {
			$return[] = ElggMenuItem::factory(array(
						'name' => 'remove_tag',
						'text' => elgg_echo('wall:remove_tag'),
						'title' => elgg_echo('wall:remove_tag'),
						'priority' => 800,
						'href' => "action/wall/remove_tag?guid=$object->guid",
						'is_action' => true,
			));
		}
	}

	if ($object->canEdit()) {
		if (elgg_instanceof($object, 'object', 'hjwall')) {
			$action = "action/wall/delete?guid=$object->guid";
		} else if (elgg_instanceof($object, 'object', 'thewire')) {
			$action = "action/thewire/delete?guid=$object->guid";
		}
		if ($action) {
			$return[] = ElggMenuItem::factory(array(
						'name' => 'delete',
						'text' => elgg_view_icon('delete'),
						'title' => elgg_echo('wall:delete'),
						'priority' => 900,
						'href' => $action,
						'is_action' => true,
						'link_class' => 'elgg-requires-confirmation'
			));
		}
	}

	return $return;
}

/**
 * Setup owner block menu
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'menu:owner_block'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array			Updated menu
 */
function owner_block_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (elgg_instanceof($entity, 'user')) {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'wall',
					'text' => elgg_echo('wall'),
					'href' => PAGEHANDLER . "/owner/{$entity->username}"
		));
	} else if (elgg_instanceof($entity, 'group') && $entity->wall_enable == 'yes') {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'wall',
					'text' => elgg_echo('wall:groups'),
					'href' => PAGEHANDLER . "/group/{$entity->guid}"
		));
	}

	return $return;
}

/**
 * Add a shortcut link to the user hover menu
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'menu:user_hover'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array			Updated menu
 */
function user_hover_menu_setup($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);

	if (elgg_instanceof($entity, 'user')) {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'wall',
					'text' => ($entity->canWriteToContainer(0, 'object', 'hjwall')) ? elgg_echo('wall:write') : elgg_echo('wall:view'),
					'href' => PAGEHANDLER . "/owner/{$entity->username}",
		));
	}
	return $return;
}

/**
 * Add views in which ECML should be rendered
 *
 * @param string $hook		Equals 'get_views'
 * @param string $type		Equals 'ecml'
 * @param array $views		Current list of views
 * @param array $params		Additional params
 * @return array			Updated lsit of views
 */
function get_ecml_views($hook, $type, $views, $params) {
	$views['output/wall/url'] = elgg_echo('wall:ecml:url');
	$views['output/wall/attachment'] = elgg_echo('wall:ecml:attachment');
	$views['river/elements/layout'] = elgg_echo('wall:ecml:river');
	return $views;
}

/**
 * Hijack wire views to display more meaningful content
 *
 * @param string $hook	Equals 'view'
 * @param string $type	Equals 'object/thewire'
 * @param string $return HTML
 * @param array $params  Additional params
 * @uses $params['vars']
 * @return string
 */
function hijack_wire($hook, $type, $return, $params) {

	$vars = elgg_extract('vars', $params);
	$entity = elgg_extract('entity', $vars);

	if ($entity->method == 'wall') {
		if (elgg_in_context('thewire')) {
			$return .= elgg_view('object/thewire/extras', $vars);
		} else {
			$return = elgg_view('object/hjwall', $vars);
		}
	}

	error_log('hijack_wire ===> hook = '.$hook.' type = '.$type.' return = '.$return.' params = '.$params);
	return $return;
}

/**
 * Hijack wire river views to display more meaningful content
 *
 * @param string $hook	Equals 'view'
 * @param string $type	Equals 'river/object/thewire/create'
 * @param string $return HTML
 * @param array $params  Additional params
 * @uses $params['vars']
 * @return string
 */
function hijack_wire_river($hook, $type, $return, $params) {

	$vars = elgg_extract('vars', $params);
	$item = elgg_extract('item', $vars);
	if (!$item instanceof \ElggRiverItem) {
		return $return;
	}

	$entity = $item->getObjectEntity();
	if ($entity->method == 'wall') {

		error_log('hijack_wire_river ===> hook = '.$hook.' type = '.$type.' return = '.$return.' params = '.$params);
		return elgg_view('river/object/hjwall/create', $vars);
	}

	return $return;
}

/**
 * Prepare a notification for when the wall post or wire is created
 *
 * @param string $hook
 * @param string $type
 * @param Elgg_Notifications_Notification $notification
 * @param array $params
 * @return null
 */
function prepare_notification_message($hook, $type, $notification, $params) {

	$event = elgg_extract('event', $params);
	$entity = $event->getObject();
	$recipient = elgg_extract('recipient', $params);
	$language = elgg_extract('language', $params);
	$method = elgg_extract('method', $params);

	if (elgg_instanceof($entity, 'object', 'hjwall') || (elgg_instanceof($entity, 'object', 'thewire') && $entity->origin == 'wall')) {

		$poster = $entity->getOwnerEntity();
		$wall_owner = $entity->getContainerEntity();

		$target = elgg_echo("wall:target:{$entity->getSubtype()}");

		if ($poster->guid == $wall_owner->guid) {
			$ownership = elgg_echo('wall:ownership:own', array($target), $language);
		} else if ($wall_owner->guid == $recipient->guid) {
			$ownership = elgg_echo('wall:ownership:your', array($target), $language);
		} else {
			$ownership = elgg_echo('wall:ownership:owner', array($wall_owner->name, $target), $language);
		}

		$notification->subject = elgg_echo('wall:new:notification:subject', array($poster->name, $ownership), $language);
		$notification->summary = elgg_view('output/url', array(
			'text' => elgg_echo('wall:new:notification:summary', array($ownership), $language),
			'href' => $entity->getURL(),
		));
		$notification->body = elgg_echo('wall:new:notification:message', array(
			$poster->name,
			$ownership,
			format_wall_message($entity, true),
			$entity->getURL()
				), $language);
	}

	return $notification;
}
