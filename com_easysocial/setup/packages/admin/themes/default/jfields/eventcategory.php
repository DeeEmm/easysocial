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
<span class="<?php echo $isJoomla4 ? 'input-group' : 'input-append'; ?>">
	<input type="text" class="input-medium" disabled="disabled" size="35" value="<?php echo $title; ?>" data-jfield-eventcategory-title />
	<a href="javascript:void(0);" class="btn btn-primary" data-jfield-eventcategory>
		<i class="icon-folder"></i> <?php echo JText::_('COM_ES_SELECT'); ?>
	</a>
	<a href="javascript:void(0);" class="btn btn-default" data-jfield-eventcategory-remove>
		<i class="icon-remove"></i>
	</a>
	<input type="hidden" id="<?php echo $id;?>_id" name="<?php echo $name;?>" value="<?php echo $value;?>" data-jfield-eventcategory-value />
</span>

<?php if (!$loaded) { ?>
<script>
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectEventCategory = function(obj) {
		$('[data-jfield-eventcategory-title]').val(obj.title);

		$('[data-jfield-eventcategory-value]').val(obj.id + ':' + obj.alias);

		EasySocial.dialog().close();
	}

	// Remove event category
	$('[data-jfield-eventcategory-remove]').on('click', function() {

		// Reset the category value
		$('[data-jfield-eventcategory-value]').val('');
		$('[data-jfield-eventcategory-title]').val('');

	});

	// Browse for event category button
	$('[data-jfield-eventcategory]').on('click', function() {
		console.log('clicked');

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/events/browseCategory', {
				'jscallback': 'selectEventCategory'
			})
		});
	});
});
</script>
<?php } ?>