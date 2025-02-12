<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<span class="input-append">
	<div data-field-message style="font-size:13px;" class="alert">Please select a profile type under the <b>Details</b> tab.</div>

	<div data-field-wrapper>
	</div>

	<input type="hidden" id="<?php echo $id;?>" name="<?php echo $name;?>" value="<?php echo $value;?>" data-jfield-fields-value />
</span>

<div data-field-template style="margin-bottom: 5px; display: none;">

	<a href="javascript:void(0);" class="btn btn-default" data-jfield-field-remove style="margin-left:2px; margin-right:2px">
		<i class="icon-minus"></i>
	</a>
	<a href="javascript:void(0);" class="btn btn-default" data-jfield-field-add>
		<i class="icon-new"></i>
	</a>
</div>

<script>
EasySocial.require()
.done(function($) {

	customfields = null;

	init = function () {
		// lets check if there is any existing value or not.

		var value = $('[data-jfield-fields-value]').val();
		var profileId = $('[data-jfield-profile-value]').val();

		if (value) {
			if (profileId) {
				window.hideFieldMessage();
				
				EasySocial.ajax('admin/controllers/fields/getExportFields', {
					"id": profileId,
					"type": 'user'
				}).done(function(fields) {
					if (fields !== undefined) {
						window.setCustomFields(fields);
					}

					// now lets populate the items.
					var ids = value.split(',');

					$.each(ids, function(idx, item) {
						window.populateCustomFields(fields, item);
					});

				});
			}
		}

		if (!value && profileId) {
			// mean user already select the profile type and save the menu. however
			// user has yet configure the custom fields. Lets populate the custom fields forms.
			window.hideFieldMessage();
			window.fetchExportFields(profileId);
		}

	}

	window.hideFieldMessage = function() {
		$('[data-field-message]').hide();
	}

	window.fetchExportFields = function(profileId) {

		if (profileId === undefined || !profileId) {
			return;
		}

		EasySocial.ajax('admin/controllers/fields/getExportFields', {
			"id": profileId,
			"type": 'user'
		}).done(function(fields) {
			if (fields !== undefined) {
				window.setCustomFields(fields);
				window.populateCustomFields(fields);
			}
		});
	}

	window.setCustomFields = function(fields) {
		window.customfields = fields;
	}

	window.populateCustomFields = function(fields, selected) {

		// hide the empty profile messsage
		$('[data-field-message]').hide();

		// need to update the field selection in template row.
		var tmpl = $('[data-field-template]').clone();

		$(tmpl).removeAttr('data-field-template');
		$(tmpl).attr('data-field-item', '');
		$(tmpl).show();

		// create new select element
		var select = $('<select data-jfield-field-item />');

		var o = new Option('-- Select Custom Field --', '');
		$(o).html('-- Select Custom Field --');
		$(select).append(o);

		// now repopulate the option based.
		$.each(fields, function(idx, item) {

			var o = new Option(item.title, item.id);
			/// jquerify the DOM object 'o' so we can use the html method
			$(o).html(item.title);
			$(select).append(o);

		});

		$(tmpl).prepend(select);
		$(select).show();

		if (selected !== null) {
			$(select).val(selected);
		}

		// for chosen lib, we need to use Joomla one instead.
		jQuery(select).chosen({
			width: "220px",
			disable_search: true,
			allow_single_deselect : true
		}).change(function() {
			updateFieldsValue();
		});

		// bind buttons
		$(tmpl).find('[data-jfield-field-add]').on('click', function() {
			// populate new selection
			window.populateCustomFields(window.customfields);
		});

		$(tmpl).find('[data-jfield-field-remove]').on('click', function() {
			// check there are how many fields added.
			if ($('[data-jfield-field-item]').length <= 1) {
				return;
			}

			$(this).closest('[data-field-item]').remove();
			updateFieldsValue();
		});


		$('[data-field-wrapper]').append(tmpl);
	}

	updateFieldsValue = function() {
		var ids = [];

		$('[data-jfield-field-item]').each(function(idx, item) {
			var id = $(item).val();

			if (id) {
				ids.push(id);
			}

		});

		$('[data-jfield-fields-value]').val(ids.join(','));
	}


	init();

});
</script>
