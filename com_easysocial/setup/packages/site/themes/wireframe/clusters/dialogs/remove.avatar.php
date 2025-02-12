<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{deleteButton}": "[data-delete-button]",
		"{form}": "[data-delete-avatar-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},
		"{deleteButton} click": function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_CLUSTERS_CONFIRM_REMOVE_AVATAR');?></title>
	<content>
		<p><?php echo JText::_('COM_EASYSOCIAL_'. strtoupper($clusterType) .'_CONFIRM_REMOVE_AVATAR_CONTENT');?></p>

		<form action="<?php echo JRoute::_('index.php');?>" data-delete-avatar-form>
		<?php echo $this->html('form.action', $clusterType, 'removeAvatar'); ?>
		<input type="hidden" name="id" value="<?php echo $id;?>" />
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-delete-button type="button" class="btn btn-es-default btn-sm btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_REMOVE_AVATAR_BUTTON');?></button>
	</buttons>
</dialog>
