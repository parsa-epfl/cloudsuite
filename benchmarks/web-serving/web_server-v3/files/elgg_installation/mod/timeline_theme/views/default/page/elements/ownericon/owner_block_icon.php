<?php

/**
* Elgg owner block
* Displays page ownership information
*
* @package Elgg
* @subpackage Core
*
*/
/**
* Elgg user display
*
* @uses $vars['entity'] ElggUser entity
* @uses $vars['size'] Size of the icon
*/

$entity = $vars['entity'];
$size = elgg_extract('size', $vars, 'tiny');

$mwenyewe = elgg_view_entity_icon($entity, $size, $vars);


elgg_push_context('owner_block');

// groups and other users get owner block
$owner = elgg_get_page_owner_entity();

$user = elgg_get_page_owner_entity();

$icon = elgg_view_entity_icon($user, 'medium', array(
	'use_hover' => false,
	'use_link' => false,
	'id' => 'photo-header',
));

 



if ($owner instanceof ElggGroup || $owner instanceof ElggUser) {

//        $header = elgg_view_entity_icon($owner, 'medium'
          $header = elgg_view_entity_icon($owner, 'medium', array(
                                                        'use_hover' => false,
                                                         'use_link' => false,
                                                      'is_trusted' => true,
                                                          
                                                ));
        

        
}


 
 $html = <<<HTML
	

	<div id="photo-elggheader" class="">
	
	$header
	$mwenyewe
	
	</div>
		

 			
HTML;


 
// echo $html;
 

?>