hypeWall
=========

Rich user interface for sharing status updates, links and content via user walls.

[Help me maintain my hardware!](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=P7QA9CFMENBKA)

## Features

- URL-parsing with embeddable content view
- Geotagging (based on browser location)
- Inline multi-file upload
- Friend tagging
- Content attachments

## Versioning

- Use 2.3.x for Elgg 1.9
- Use 2.2.x for Elgg 1.8

Master branch is 1.9-compatible


## Acknowledgements / Credits

* Reverse geocoding is performed via Nominatim
http://wiki.openstreetmap.org/wiki/Nominatim

* As always, best in kind FontAwesome
http://fontawesome.io/


## Dependencies

* For best results, install hypeEmbed, ECML and configure Iframely
https://github.com/hypeJunction/hypeEmbed
https://github.com/Elgg/ecml

* For drag&drop uploads, install elgg_dropzone
https://github.com/hypeJunction/elgg_dropzone

* Tokenizing Autocomplete is required for this plugin to run properly
https://github.com/hypeJunction/elgg_tokeninput


## Notes

* Reverse geocoding (i.e. browser position coordinates to human readable address)
will not work in https. Implement a custom solution using a paid/free/proprietary
service that does the same


## Developer Notes

* You can extend wall tabs and forms via ```'framework/wall/container/extend'``` view

* If you have other plugins that are performing river updates in real time,
take a look at ```'refresh', 'river'``` triggered by in ```js/framework/wall/status```


## Screenshots

![alt text](https://raw.github.com/hypeJunction/hypeWall/master/screenshots/form-url.png "Form with a URL")
![alt text](https://raw.github.com/hypeJunction/hypeWall/master/screenshots/form-photos.png "Instant photo upload")
![alt text](https://raw.github.com/hypeJunction/hypeWall/master/screenshots/form-content.png "Attaching content")
![alt text](https://raw.github.com/hypeJunction/hypeWall/master/screenshots/river.png "River view")