<?php

/**
 * Displays status and upload forms
 */

namespace hypeJunction\Wall;

if (!elgg_is_logged_in()) {
	return;
}

if (!elgg_in_context('activity') && !elgg_in_context('wall')) {
	return;
}

$user = elgg_get_logged_in_user_entity();
$page_owner = elgg_get_page_owner_entity();

if ($page_owner->guid !== $user->guid) {
	$subtype = 'hjwall';
} else {
	$subtype = WALL_SUBTYPE;
}
// Make sure user can write to container before displaying the form
if (elgg_instanceof($page_owner) && !$page_owner->canWriteToContainer($user->guid, 'object', $subtype)) {
	return;
}

elgg_load_css('wall');
elgg_load_css('fonts.font-awesome');
elgg_load_css('fonts.open-sans');

elgg_require_js('framework/wall/init');

$user_icon = elgg_view_entity_icon($user, elgg_extract('size', $vars, 'medium'), array(
	'use_hover' => false,
	'link_class' => 'wall-poster-icon-block',
	'img_class' => 'wall-poster-avatar'
		));

if ($page_owner && $page_owner->guid !== $user->guid) {
	$page_owner_icon = elgg_view_entity_icon($page_owner, 'small', array(
		'use_hover' => false,
		'link_class' => 'wall-owner-icon-block',
		'img_class' => 'wall-owner-avatar'
	));
}

$default = elgg_get_plugin_setting('default_form', PLUGIN_ID);
if (!$default) {
	$default = 'status';
}

if (elgg_get_plugin_setting('status', PLUGIN_ID)) {
	elgg_register_menu_item('wall-filter', array(
		'name' => 'status',
		'text' => '<i class="wall-icon wall-icon-status"></i>',
		'title' => elgg_echo('wall:status'),
		'href' => '#wall-form-status',
		'link_class' => 'wall-tab',
		'selected' => ($default == 'status'),
		'priority' => 100
	));
	$class = 'wall-form';
	if ($default !== 'status') {
		$class .= ' hidden';
	}
	$forms = elgg_view_form('wall/status', array(
		'id' => 'wall-form-status',
		'class' => $class,
			), $vars);
}

if (elgg_get_plugin_setting('url', PLUGIN_ID)) {
	elgg_register_menu_item('wall-filter', array(
		'name' => 'url',
		'text' => '<i class="wall-icon wall-icon-url"></i>',
		'title' => elgg_echo('wall:url'),
		'href' => '#wall-form-url',
		'selected' => ($default == 'url'),
		'link_class' => 'wall-tab',
		'priority' => 150
	));
	$class = 'wall-form';
	if ($default !== 'url') {
		$class .= ' hidden';
	}
	$forms .= elgg_view_form('wall/url', array(
		'id' => 'wall-form-url',
		'class' => $class,
			), $vars);
}

if (elgg_get_plugin_setting('photo', PLUGIN_ID)) {
	elgg_register_menu_item('wall-filter', array(
		'name' => 'photo',
		'text' => '<i class="wall-icon wall-icon-photo"></i>',
		'title' => elgg_echo('wall:photo'),
		'href' => '#wall-form-photo',
		'selected' => ($default == 'photo'),
		'link_class' => 'wall-tab',
		'priority' => 200
	));
	$class = 'wall-form';
	if ($default !== 'photo') {
		$class .= ' hidden';
	}
	$forms .= elgg_view_form('wall/photo', array(
		'id' => 'wall-form-photo',
		'class' => $class,
		'enctype' => 'multipart/form-data',
			), $vars);
}

//if (elgg_get_plugin_setting('file', PLUGIN_ID)) {
//	elgg_register_menu_item('wall-filter', array(
//		'name' => 'file',
//		'text' => '<i class="wall-icon wall-icon-file"></i>',
//		'title' => elgg_echo('wall:file'),
//		'href' => '#wall-form-file',
//		'class' => 'wall-tab',
//		'priority' => 300
//	));
//	$forms .= elgg_view_form('wall/file', array(
//		'id' => 'wall-form-file',
//		'class' => 'wall-form hidden',
//		'enctype' => 'multipart/form-data'
//			), $vars);
//}

if (elgg_get_plugin_setting('content', PLUGIN_ID) && elgg_is_active_plugin('elgg_tokeninput')) {
	elgg_register_menu_item('wall-filter', array(
		'name' => 'content',
		'text' => '<i class="wall-icon wall-icon-content"></i>',
		'title' => elgg_echo('wall:content'),
		'href' => '#wall-form-content',
		'selected' => ($default == 'content'),
		'link_class' => 'wall-tab',
		'priority' => 300
	));
	$class = 'wall-form';
	if ($default !== 'content') {
		$class .= ' hidden';
	}
	$forms .= elgg_view_form('wall/content', array(
		'id' => 'wall-form-content',
		'class' => $class,
			), $vars);
}

$forms .= elgg_view('framework/wall/container/extend', $vars);

$tabs = elgg_view_menu('wall-filter', array(
	'sort_by' => 'priority'
		));

$class = (elgg_in_context('activity')) ? 'wall-river' : 'wall-to-wall';

if (elgg_in_context('widgets')) {
	$user_icon = $page_owner_icon = '';
	$forms = $tabs . $forms;
	echo elgg_view_image_block($user_icon . $page_owner_icon, $forms, array(
		'class' => "wall-container $class"
	));
} else {
	$forms = $tabs . '<div class="wall-bubble">' . $forms . '</div>';
	echo elgg_view_image_block($user_icon . $page_owner_icon, $forms, array(
		'class' => "wall-container wall-post $class"
	));
}
