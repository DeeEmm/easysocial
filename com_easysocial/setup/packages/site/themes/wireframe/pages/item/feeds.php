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
<?php if ($customFilter) { ?>
<div class="es-snackbar2">
	<div class="es-snackbar2__context">
		<div class="es-snackbar2__title">
			<?php echo JText::_($customFilter->title);?>
		</div>
	</div>

	<?php if ($page->canCreateStreamFilter()) { ?>
	<div class="es-snackbar2__actions">
		<a href="javascript:void(0);" class="btn btn-sm btn-es-default-o"  data-edit-filter data-id="<?php echo $customFilter->id; ?>" data-type="<?php echo $customFilter->utype; ?>">
			<?php echo JText::_('COM_ES_EDIT');?>
		</a>
	</div>
	<?php } ?>
</div>
<?php } ?>

<?php if (!$customFilter && isset($type) && $type == 'moderation') { ?>
	<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_PENDING_POSTS'); ?>
<?php } ?>

<?php echo $stream->html(); ?>
