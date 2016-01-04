<?php

/**
 * User Walls
 *
 * @package hypeJunction
 * @subpackage Wall
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace hypeJunction\Wall;

const PLUGIN_ID = 'hypeWall';
const PAGEHANDLER = 'wall';

define('WALL_MODEL', elgg_get_plugin_setting('model', PLUGIN_ID));
define('WALL_MODEL_WALL', 1);
define('WALL_MODEL_WIRE', 2);

define('WALL_SUBTYPE', (WALL_MODEL == WALL_MODEL_WIRE) ? 'thewire' : 'hjwall');

define('WALL_GEOPOSITIONING', elgg_get_plugin_setting('geopositioning', PLUGIN_ID));
define('WALL_TAG_FRIENDS', elgg_get_plugin_setting('tag_friends', PLUGIN_ID));

require_once __DIR__ . '/vendors/autoload.php';

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');
elgg_register_event_handler('upgrade', 'system', __NAMESPACE__ . '\\upgrade');

function init() {

	/**
	 * Handle pages and URLs
	 */
	elgg_register_page_handler(PAGEHANDLER, __NAMESPACE__ . '\\page_handler');
	elgg_register_plugin_hook_handler('entity:url', 'object', __NAMESPACE__ . '\\url_handler');
	
	/**
	 * Add wall posts to search
	 */
	elgg_register_entity_type('object', 'hjwall');
	/**
	 * JS, CSS and Views
	 */
	elgg_extend_view('css/elgg', 'css/framework/wall/stylesheet.css');

	elgg_extend_view('page/layouts/widgets', 'framework/wall/requirejs');

	// Load fonts
	elgg_register_css('fonts.font-awesome', '/mod/' . PLUGIN_ID . '/vendors/fonts/font-awesome.css');
	elgg_register_css('fonts.open-sans', '/mod/' . PLUGIN_ID . '/vendors/fonts/open-sans.css');

	// Add User Location to config
	elgg_extend_view('js/initialize_elgg', 'js/framework/wall/config');

	/**
	 * Views
	 */
	// Display wall form
	elgg_extend_view('page/layouts/elements/filter', 'framework/wall/container', 100);

	// AJAX view to load URL previews
	elgg_register_ajax_view('output/wall/url');

	/**
	 * Register actions
	 */
	$actions_path = __DIR__ . '/actions/';
	elgg_register_action('wall/status', $actions_path . 'wall/status.php');
	elgg_register_action('wall/photo', $actions_path . 'wall/photo.php');
	elgg_register_action('wall/file', $actions_path . 'wall/file.php');
	elgg_register_action('wall/content', $actions_path . 'wall/content.php');
	elgg_register_action('wall/url', $actions_path . 'wall/url.php');

	elgg_register_action('wall/upload', $actions_path . 'wall/upload.php');

	elgg_register_action('wall/delete', $actions_path . 'wall/delete.php');
	elgg_register_action('wall/remove_tag', $actions_path . 'wall/remove_tag.php');

	elgg_register_action('wall/geopositioning/update', $actions_path . 'wall/geopositioning/update.php', 'public');

	/**
	 * Register hooks
	 */
	//elgg_register_plugin_hook_handler('permissions_check', 'object', __NAMESPACE__ . '\\permissions_check');
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', __NAMESPACE__ . '\\container_permissions_check');

	/* Performance fix - Remove specialized menu handlers.
	elgg_register_plugin_hook_handler('register', 'menu:river', __NAMESPACE__ . '\\river_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:entity', __NAMESPACE__ . '\\entity_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', __NAMESPACE__ . '\\owner_block_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', __NAMESPACE__ . '\\user_hover_menu_setup');
	*/
	elgg_register_widget_type('wall', elgg_echo('wall'), elgg_echo('wall:widget:description'));

	elgg_register_plugin_hook_handler('get_views', 'ecml', __NAMESPACE__ . '\\get_ecml_views');

	/* Performance fix - Remove special formatting of wire posts.
	elgg_register_plugin_hook_handler('view', 'object/thewire', __NAMESPACE__ . '\\hijack_wire');
	elgg_register_plugin_hook_handler('view', 'river/object/thewire/create', __NAMESPACE__ . '\\hijack_wire_river');
	*/

	/**
	 * Notifications
	 */
	elgg_register_event_handler('publish', 'object', __NAMESPACE__ . '\\send_custom_notifications');

	elgg_register_notification_event('object', 'hjwall', array('publish'));
	elgg_register_plugin_hook_handler('prepare', 'notification:publish:object:hjwall', __NAMESPACE__ . '\\prepare_notification_message');

	elgg_register_notification_event('object', 'thewire', array('publish'));
	elgg_register_plugin_hook_handler('prepare', 'notification:publish:object:thewire', __NAMESPACE__ . '\\prepare_notification_message');

	/**
	 * Group tools
	 */
	add_group_tool_option('wall', elgg_echo('wall:groups:enable'), false);
	elgg_extend_view('groups/tool_latest', 'framework/wall/group_module');
}

/**
 * Run upgrade scripts
 */
function upgrade() {
	include_once __DIR__ . '/lib/upgrades.php';
}
