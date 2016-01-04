<?php

namespace hypeJunction\Filestore;

use ElggEntity;

/**
 * Handles entity cover images
 *
 * @package    HypeJunction
 * @subpackage Filestore
 */
class CoverHandler extends IconHandler {

	/**
	 * Get cover size config
	 *
	 * @param ElggEntity $entity     Entity whose icons are being handled
	 * @param array      $icon_sizes An array of predefined icon sizes
	 * @return array
	 */
	protected static function getIconSizes($entity, $icon_sizes = array()) {

		$type = $entity->getType();
		$subtype = $entity->getSubtype();

		$defaults = array(
			'master' => array(
				'h' => 370,
				'w' => 1000,
				'upscale' => true,
				'square' => false,
			)
		);

		if (is_array($icon_sizes)) {
			$icon_sizes = array_merge($defaults, $icon_sizes);
		} else {
			$icon_sizes = $defaults;
		}

		return elgg_trigger_plugin_hook('entity:cover:sizes', $type, array(
			'entity' => $entity,
			'subtype' => $subtype,
				), $icon_sizes);
	}

}
