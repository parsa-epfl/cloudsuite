define(function(require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	require('jquery.form');

	var wall = {
		distanceIncrement: 500, // 500 m

		/**
		 * Bind events to DOM elements
		 * @returns void
		 */
		init: function() {

			if (wall.initialized) {
				return;
			}

			$('body.wall-state-loading').on('click', function() {
				$(this).removeClass('wall-state-loading');
			});

			if (typeof navigator == 'undefined') {
				$('.wall-find-me').hide();
			}

			$('.wall-find-me').on('click', wall.findMe);
			$('.wall-tab').on('click', wall.switchTab);
			$('textarea[data-limit]').on('keyup keydown', wall.updateCounter);
			$('.wall-input-status').on('keyup', wall.parseUrl);
			$('.wall-url').on('blur focusout preview clear', wall.loadUrlPreview);
			$('.wall-form').removeAttr('onsubmit').on('submit', wall.formSubmit);

			wall.initialized = true;
		},
		/**
		 * If the geopositioning of the session is not set, try to obtain it
		 * using the browser geolocation service
		 *
		 * @link http://nominatim.openstreetmap.org/reverse Uses nominatim reverse geocoding service
		 * @see seCurrentPosition for caching logic
		 * @param object position
		 * @returns void
		 */
		findMe: function(e) {
			navigator.geolocation.getCurrentPosition(function(position) {
				if (typeof elgg.session.geopositioning == 'undefined') {
					elgg.session.geopositioning = {};
				}

				// Do not refresh position if distance is less than the increment constant
				if (wall.calculateDistance(position.coords.latitude, position.coords.longitude, elgg.session.geopositioning.latitude, elgg.session.geopositioning.longitude) > wall.distanceIncrement) {

					elgg.session.geopositioning.latitude = position.coords.latitude;
					elgg.session.geopositioning.longitude = position.coords.longitude;
					$.ajax({
						crossDomain: true,
						dataType: "jsonp",
						url: 'http://nominatim.openstreetmap.org/reverse',
						data: {
							format: 'json',
							lat: position.coords.latitude,
							lon: position.coords.longitude,
							addressdetails: 1,
							zoom: 10,
							json_callback: 'setGeopositioning'
						},
					});
				} else {
					setGeopositioning();
				}
			});
		},
		/**
		 * Calculate distance in metres between two geographicsl points
		 */
		calculateDistance: function(lat1, lon1, lat2, lon2) {
			var radlat1 = Math.PI * lat1 / 180;
			var radlat2 = Math.PI * lat2 / 180;
			var radlon1 = Math.PI * lon1 / 180;
			var radlon2 = Math.PI * lon2 / 180;
			var theta = lon1 - lon2;
			var radtheta = Math.PI * theta / 180;
			var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
			dist = Math.acos(dist);
			dist = dist * 180 / Math.PI;
			dist = dist * 60 * 1.1515 * 1.609344 * 1000;
			return dist;
		},
		/**
		 * Update session geopositioning and set wall location input values
		 * This is used as a jsonp callbcack for nominatim lookup
		 * @param object data
		 * @returns void
		 */
		setGeopositioning: function(data) {
			if (typeof elgg.session.geopositioning == 'undefined') {
				elgg.session.geopositioning = {};
			}

			if (data && data.display_name) {
				elgg.session.geopositioning.location = data.display_name;
			}

			if ($('.wall-location-tokeninput').length) {
				$('.wall-location-tokeninput').bind('update', function(e) {
					$(this).tokenInput("add", {
						label: elgg.session.geopositioning.location,
						value: elgg.session.geopositioning.location
					});
				}).trigger('update');
			}
			elgg.action('wall/geopositioning/update', {
				data: elgg.session.geopositioning,
			});
		},
		/**
		 * Switch wall form when tab link is clicked
		 * @param object e
		 * @returns void
		 */
		switchTab: function(e) {
			e.preventDefault();
			var $tab = $(this);
			$tab.closest('li').toggleClass('elgg-state-selected').siblings().removeClass('elgg-state-selected');
			var $form = $($tab.attr('href'));
			$('.wall-form').addClass('hidden');
			$form.removeClass('hidden');
		},
		/**
		 * Parse URLs from the user input and add them to the URL input field
		 * @param object e
		 * @returns void
		 */
		parseUrl: function(e) {

			var $form = $(this).closest('form');
			var $url = $form.find('.wall-url');
			if ($url.val()) {
				return;
			}

			var text = $(this).val();
			var match = text.match(/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig);
			if (!match) {
				return;
			}

			if (match instanceof Array) {
				var url = match[0];
			} else {
				var url = match;
			}

			if (url.length) {
				$url.val(url).trigger('preview');
			}
		},
		/**
		 * Loads the preview of the
		 * @param object e
		 * @returns void
		 */
		loadUrlPreview: function(e) {
			var $elem = $(this);
			var $form = $elem.closest('form');
			var $preview = $form.find('.wall-url-preview');
			var url = $elem.val();
			if (!url) {
				$preview.html('');
			} else if (url !== wall.loadedUrlPreview) {
				elgg.ajax('ajax/view/output/wall/url', {
					dataType: 'html',
					data: {
						value: url
					},
					beforeSend: function() {
						$preview.addClass('elgg-state-loading');
					},
					success: function(data) {
						$preview.html(data);
						if (typeof oembed !== 'undefined') {
							$preview.find('a[title^=oembed]').oembed(null, {
								embedMethod: 'fill',
								maxWidth: 500
							});
						}
						$elem.closest('.wall-input-url').show();
						wall.loadedUrlPreview = url;
						wall.loadedPreviewHtml = data;
					}
				});
			} else if (!$preview.html()) {
				$preview.html(wall.loadedPreviewHtml);
				if (typeof oembed !== 'undefined') {
					$preview.find('a[title^=oembed]').oembed(null, {
						embedMethod: 'fill',
						maxWidth: 500
					});
				}
			}
		},
		/**
		 * Submit a form via AJAX and populate the river with new entries
		 * @param object event
		 * @returns void
		 */
		formSubmit: function(event) {

			event.preventDefault();
			var $form = $(this);
			$form.ajaxSubmit({
				iframe: $form.is('[enctype^="multipart"]'),
				dataType: 'json',
				data: {
					'X-Requested-With': 'XMLHttpRequest', // simulate XHR
					container_guid: elgg.get_page_owner_guid(),
					river: $form.closest('.wall-container').is('.wall-river'),
					widget: $form.closest('.elgg-widgets').length
				},
				beforeSend: function() {
					$form.find('[type="submit"]').addClass('elgg-state-disabled').text(elgg.echo('wall:process:posting')).prop('disabled', true);
					$('body').addClass('wall-state-loading');
				},
				success: function(data) {
					if (data.status >= 0) {

						$form.resetForm();
						if ($('.elgg-input-tokeninput', $form).length) {
							$('.elgg-input-tokeninput', $form).bind('clear', function(e) {
								$(this).tokenInput("clear");
							}).trigger('clear');
						}
						$('.elgg-dropzone-preview', $form).html('');
						$('.token-input-dropdown').hide();
						$form.find('.wall-url').trigger('clear');
						$form.find('textarea:first').trigger('click');
						if (data.output) {
							if ($form.closest('.wall-container').is('.wall-river')) {
								var items = $(data.output).html();
								$(items).children('li').addClass('wall-item-new').bind('refresh.before', function(e) {
									$(this).remove();
								});
								$('.elgg-list-river').prepend($(items));
							} else {
								$('.wall-post-list,.wall-widget-list').prepend($('<li>').addClass('elgg-item').html(data.output));
							}
						}
					}
					if (data.system_messages) {
						elgg.register_error(data.system_messages.error);
						elgg.system_message(data.system_messages.success);
					}
				},
				error: function() {
					elgg.register_error(elgg.echo('wall:error:ajax'));
				},
				complete: function() {
					$('body').removeClass('wall-state-loading');
					$form.find('[type="submit"]').removeClass('elgg-state-disabled').text(elgg.echo('wall:post')).prop('disabled', false);
				}
			});
		},
		/**
		 * Update char limit counter
		 * @param {object} e
		 * @returns {void}
		 */
		updateCounter: function(e) {

			var $textarea = $(this);
			var limit = $textarea.data('limit');
			var remaining = limit - $textarea.val().length;
			var $form = $textarea.closest('form')
			var $counter = $form.find('[data-counter]').eq(0);
			$counter.find('[data-counter-indicator]').text(remaining);
			if (remaining < 0) {
				$counter.addClass('wall-status-counter-overflow');
				$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
			} else {
				$counter.removeClass('wall-status-counter-overflow');
				$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
			}
		}
	};

	return wall;
});
