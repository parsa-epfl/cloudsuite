define(function (require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	var spinner = require('elgg/spinner');
	
	var get_checkboxes = function () {
		return $('#admin-users-unvalidated-bulk .elgg-input-checkbox[name="user_guids[]"]');
	};
	
	var bulk_submit = function() {
		
		var $checkboxes = get_checkboxes().filter(':checked');
		if (!$checkboxes.length) {
			return false;
		}
		
		var $form = $('#admin-users-unvalidated-bulk');
		$form.prop('action', $(this).prop('href'));
		
		spinner.start();
		$form.submit();
		
		return false;
	};
	
	elgg.register_hook_handler('init', 'system', function() {
		$(document).on('click', '#uservalidationbyemail-bulk-resend', bulk_submit);
	});
});
