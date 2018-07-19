<?php

/**
 * File utilities
 *
 * @package    HypeJunction
 * @subpackage Wall
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace hypeJunction\Filestore;

const PLUGIN_ID = 'hypeFilestore';

require_once __DIR__ . '/vendors/autoload.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Initialize the plugin
 *
 * @return void
 */
function init() {

	/**
	 * JS/CSS
	 */
	elgg_register_css('cropper', '/mod/' . PLUGIN_ID . '/vendors/cropper/dist/cropper.min.css');
	elgg_define_js('cropper', array(
		'src' => '/mod/' . PLUGIN_ID . '/vendors/cropper/dist/cropper.min.js',
		'deps' => array('jquery')
	));

	/**
	 * Tests
	 */
	elgg_register_plugin_hook_handler('unit_test', 'system', __NAMESPACE__ . '\\unit_test');
}

/**
 * Run unit tests
 *
 * @param string $hook   Equals 'unit_test'
 * @param string $type   Equals 'system'
 * @param array  $value  An array of unit test locations
 * @param array  $params Additional params
 * @return array Updated array of unit test locations
 */
function unit_test($hook, $type, $value, $params) {

	$path = elgg_get_plugins_path();
	//$value[] = $path . PLUGIN_ID . '/tests/UploadHandlerTest.php';
	//$value[] = $path . PLUGIN_ID . '/tests/IconHandlerTest.php';
	//$value[] = $path . PLUGIN_ID . '/tests/CoverHandlerTest.php';

	return $value;
}
