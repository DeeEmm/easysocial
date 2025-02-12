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
	<input type="text" class="input-medium" disabled="disabled" size="35" value="<?php echo $title; ?>" data-jfield-user-title />
	<a class="btn btn-primary" data-jfield-user href="javascript:void(0);">
		<i class="icon-user"></i> <?php echo JText::_('COM_ES_SELECT'); ?>
	</a>
	<a class="btn btn-default" data-jfield-user-cancel>
		<i class="icon-cancel-2"></i>
	</a>

	<input type="hidden" id="<?php echo $id;?>_id" name="<?php echo $name;?>" value="<?php echo $value;?>" data-jfield-user-value />
</span>

<?php if (!$loaded) { ?>
<script>
EasySocial
.require()
.library('dialog')
.done(function($) {
	var titleField = $('[data-jfield-user-title]');
	var valueField = $('[data-jfield-user-value]');
	var browseButton = $('[data-jfield-user]');
	var cancelButton = $('[data-jfield-user-cancel]');

	window.selectUser = function(obj) {
		titleField.val(obj.title);
		valueField.val(obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	};

	cancelButton.on('click', function() {
		titleField.val('<?php echo JText::_('COM_EASYSOCIAL_JFIELD_SELECT_USER', true);?>');
		valueField.val('');
	});

	browseButton.on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/users/browse', {
				'dialogTitle': '<?php echo JText::_( 'COM_EASYSOCIAL_USERS_BROWSE_USERS_DIALOG_TITLE' );?>',
				'jscallback' : 'selectUser'
			})
		});
	});

});
</script>
<?php } ?>