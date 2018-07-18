<?php

$options = get_input('options');
$count = (int) get_input('count');

$options['count'] = true;
$newcount = elgg_get_river($options);
$options['count'] = false;

$returnData['count'] = $newcount;
$limit = $newcount - $count;

if ($limit > 0) 
{
	$options['pagination'] = false;
	$options['offset'] = 0;	
	$options['limit'] = 1;	
	$returnData['content'] = elgg_list_river($options);
	$returnData['valid'] = 1;
}
else 
{
	$returnData['valid'] = 0;
}
	
echo json_encode($returnData);

exit;