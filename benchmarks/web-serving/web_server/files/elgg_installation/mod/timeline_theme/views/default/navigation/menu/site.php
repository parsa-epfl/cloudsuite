<?php
/**
 * Site navigation menu
 *
 * @uses $vars['menu']['default']
 * @uses $vars['menu']['more']
 */
/*
$default_items = elgg_extract('default', $vars['menu'], array());
$more_items = elgg_extract('more', $vars['menu'], array());

echo '<ul class="category-top">';

foreach ($default_items as $menu_item) {
	echo elgg_view('navigation/menu/elements/item', array('item' => $menu_item));

}



echo '</ul>';

// TM: moved autside the above class so that more links should be in its own class with no overflow hidden css property 
echo '<ul class="category-top-more">';

if ($more_items) {


        echo '<ul class="elgg-menu elgg-menu-site category-menu-site-default clearfix">';
        
	echo '<li class="category-more-more">';

	$more = elgg_echo('more');
	echo "<a href=\"#\">$more</a>";
	
	
	
	// TM: removed the class part. Check original code
	echo elgg_view('navigation/menu/elements/section', array(
		'class' => 'category-menu category-menu-site category-menu-site-more', 
		'items' => $more_items,
	));
	
	echo '</li>';
	
	echo '</ul>';
}

echo '</ul>';

*/

/**
 * Site  Timeline custom navigation menu
 *
 * @uses $vars['menu']['default']
 * @uses $vars['menu']['more']
 */

$default_items = elgg_extract('default', $vars['menu'], array());
$more_items = elgg_extract('more', $vars['menu'], array());

echo '<ul class="top_navigation left">';
echo '<li class="splitter"></li>';
foreach ($default_items as $menu_item) {
	echo elgg_view('navigation/menu/elements/item', array('item' => $menu_item));
        echo '<li class="splitter"></li>';
}

if ($more_items) {
	echo '<li class="elgg-more">';

	$more = elgg_echo('more');
	echo "<a href=\"#\">$more</a>";
	
	echo elgg_view('navigation/menu/elements/section', array(
		'class' => 'elgg-menu elgg-menu-site elgg-menu-site-more', 
		'items' => $more_items,
	));
	
	echo '</li>';
}
echo '</ul>';
