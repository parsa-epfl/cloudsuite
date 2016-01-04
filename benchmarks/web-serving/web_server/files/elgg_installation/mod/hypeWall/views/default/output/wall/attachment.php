<?php

namespace hypeJunction\Wall;

use hypeJunction\Util\Embedder;

$entity = elgg_extract('entity', $vars);

if (!elgg_instanceof($entity)) {
	return;
}

elgg_push_context('embed');
echo Embedder::getEmbedView($entity->getURL(), $vars);
elgg_pop_context();
