Linkify
=======

[![Build Status](https://secure.travis-ci.org/misd-service-development/php-linkify.png)](http://travis-ci.org/misd-service-development/php-linkify)

Converts URLs and email addresses into clickable links. It works on both snippets of HTML (or plain text) and complete HTML pages.

There are many regex variations shared on the internet for performing this task, but few are robust. Linkify contains a large number of unit tests to counter this.

It does not cover every possible valid-yet-never-used URLs and email addresses in order to handle 'real world' usage (eg no 'gopher://'). This means, for example, that it copes better with punctuation errors.

Authors
-------

* Chris Wilkinson

It uses regex based on John Gruber's [Improved Liberal, Accurate Regex Pattern for Matching URLs](http://daringfireball.net/2010/07/improved_regex_for_matching_urls).

Installation
------------

`composer require misd/linkify`

Usage
-----

```php
$linkify = new \Misd\Linkify\Linkify();
$text = 'This is my text containing a link to www.example.com.';

echo $linkify->process($text);
```

Will output:

```html
This is my text containing a link to <a href="http://www.example.com">www.example.com</a>.
```

### Options

Options set on the constructor will be applied to all links. Alternatively you can place the options on a method call. The latter will override the former.

```php
$linkify = new \Misd\Linkify\Linkify(array('attr' => array('class' => 'foo')));
$text = 'This is my text containing a link to www.example.com.';

echo $linkify->process($text);
```

Will output:

```html
This is my text containing a link to <a href="http://www.example.com" class="foo">www.example.com</a>.
```

Whereas:

```php
$linkify = new \Misd\Linkify\Linkify(array('attr' => array('class' => 'foo')));
$text = 'This is my text containing a link to www.example.com.';

echo $linkify->process($text, array('attr' => array('class' => 'bar')));
```

Will output:

```html
This is my text containing a link to <a href="http://www.example.com" class="bar">www.example.com</a>.
```

Available options are:

#### `attr`

An associative array of HTML attributes to add to the link. For example:

```php
array('attr' => array('class' => 'foo', 'style' => 'font-weight: bold; color: red;')
```

#### `callback`

A closure to call with each url match. The closure will be called for each URL found with three parameters: the url, the caption and a boolean `isEmail` (if `$isEmail` is true, then `$url` is equals to `$caption`.

If the callback return a non-null value, this value replace the link in the resulting text. If null is returned, the usual `<a href="URL">CAPTION</a>` is used.

```php
$linkify = new \Misd\Linkify\Linkify(array('callback' => function($url, $caption, $isEmail) {
    return '<b>' . $caption . '</b>';
}));
echo $linkify->process('This link will be converted to bold: www.example.com.'));
```
