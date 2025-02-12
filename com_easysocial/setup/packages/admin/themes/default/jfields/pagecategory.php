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
	<input type="text" class="input-medium" disabled="disabled" size="35" value="<?php echo $title; ?>" data-jfield-pagecategory-title />
	<a href="javascript:void(0);" class="btn btn-primary" data-jfield-pagecategory>
		<i class="icon-folder"></i> <?php echo JText::_('COM_ES_SELECT'); ?>
	</a>
	<a href="javascript:void(0);" class="btn btn-default" data-jfield-pagecategory-remove>
		<i class="icon-remove"></i>
	</a>
	<input type="hidden" id="<?php echo $id;?>_id" name="<?php echo $name;?>" value="<?php echo $value;?>" data-jfield-pagecategory-value />
</span>

<?php if (!$loaded) { ?>
<script>
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectPageCategory = function(obj) {
		$('[data-jfield-pagecategory-title]').val(obj.title);

		$('[data-jfield-pagecategory-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-pagecategory-remove]').on('click', function() {

		// Reset the category value
		$('[data-jfield-pagecategory-value]').val('');
		$('[data-jfield-pagecategory-title]').val('');

	});

	$('[data-jfield-pagecategory]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/pages/browseCategory', {
				'jscallback': 'selectPageCategory'
			})
		});
	});

});
</script>
<?php } ?>