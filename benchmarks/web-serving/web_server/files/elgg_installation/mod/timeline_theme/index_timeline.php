<?php
/**
* timelinestyle
* 
* @package timelinestyle
* @author ColdTrick IT Solutions
* @copyright Coldtrick IT Solutions 2009
* @link http://www.coldtrick.com/
*/

require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");
elgg_set_context('timelinestyle');




// Display main admin menu
	// for distributed plugins, be sure to use elgg_echo() for internationalization
	$class = array('class' => 'ptm elgg-divide-bottom');
	
	$title = "timelinestyle:title";
 
	// start building the main column of the page
	$content = elgg_view_title(elgg_echo('timelinestyle:title'), $class);
 
	// add the form to this section
	$content .= elgg_view('timeline_theme/default');
 
	// optionally, add the content for the sidebar
	$sidebar = "";
 
	// layout the page
//	$body = elgg_view_layout('two_column_left_sidebar', array(
	$body = elgg_view_layout('two_column_left_sidebar', array(
   	//'title' => $title,
   	'content' => $content,
   	'sidebar' => $sidebar
	));
 
	// draw the page
	echo elgg_view_page(elgg_echo($title), $body);