<?php

$options = get_input('options');
$offset = (int) get_input('offset');

$options['count'] = true;
$count = elgg_get_river($options);
$options['count'] = false;

if ($count <= ($offset + $options['limit']))
{
	$options['limit'] = $count - $offset;
	$returnData['istheremore'] = 0;
}
else
{
	$returnData['istheremore'] = 1;
}

$options['pagination'] = false;
$options['offset'] = $offset;
$returnData['content'] = elgg_list_river($options);
$returnData['valid'] = 1;
	
echo json_encode($returnData);

exit;