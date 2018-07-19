<?php

namespace hypeJunction\Wall;

use hypeJunction\Util\Embedder;

$value = elgg_extract('value', $vars);

elgg_push_context('embed');
echo Embedder::getEmbedView($value, $vars);
elgg_pop_context();