cmp-filestore
=============
Filestore classes for hypeJunction plugins

Includes:
* ```hypeJunction\Filestore\UploadHandler``` - handles file uploads

Use composer to include these in your project
```json
{
	"require": {
		"hypejunction/cmp-filestore": "@stable"
	}
}
```

Hooks:

* ```'entity:icon:sizes', $type``` - use this hook to specify an array of icon sizes

Custom icon sizes:

* Sample configuration icon sizes array

```php

$config['icon_sizes'] = array(
	'custom' => array(
		'w' => 200,
		'h' => 25,
		'metadata_name' => 'custom_thumbnail',
		'croppable' => true,
	),
);

