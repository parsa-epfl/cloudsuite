<?php


 
 /**
 * Elgg secondary sidebar contents
 *
 * echo elgg_view('page/elements/sidebar_alt', $vars); 
 * You can override, extend, or pass content to it
 *
 * @uses $vars['sidebar_alt] HTML content for the alternate sidebar
 */
/*
$sidebar = elgg_extract('sidebar_alt', $vars, '');

echo $sidebar;
*/
 echo elgg_view('page/elements/sidebar_alt', $vars);

 ?>