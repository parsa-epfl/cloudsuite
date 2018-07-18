cmp-util
========
Util classes for hypeJunction plugins

Includes:
* *hypeJunction\Util\Embedder* - transforms URLs into embeddable HTML markup (uses iframely.com for metatag parsing)
* *hypeJunction\Util\Extractor* - extracts usernames, URLs, hashtags, and emails and replaces them with HTML <a> markup

Use composer to include these in your project
```json
{
	"require": {
		"hypejunction/cmp-util": "@stable"
	}
}
```