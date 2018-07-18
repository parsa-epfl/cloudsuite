<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * Action to get Google Talk based smileys
 *
 * @package elggchat
 * @author ColdTrick IT Solutions
 * @copyright Coldtrick IT Solutions 2009-2014
 * @link http://www.coldtrick.com/
 *
 * for Elgg 1.8 and newer by iionly (iionly@gmx.de)
 * @copyright iionly 2014
 * @link https://github.com/iionly
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$base_folder = "elggchat/_graphics/";
$smiley = get_input("smiley");

if ($smiley) {

	$filename = elgg_get_plugins_path() . $base_folder . $smiley;
	$contents = @file_get_contents($filename);

	header("Cache-Control: no-cache, no-store, must-revalidate");

	echo $contents;
}
exit();
?>