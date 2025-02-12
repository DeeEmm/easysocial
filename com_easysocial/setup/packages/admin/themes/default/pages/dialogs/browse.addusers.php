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
	<width>700</width>
	<height>500</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{submitButton}": "[data-submit-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PAGES_ADD_MEMBERS_DIALOG_TITLE'); ?></title>
	<content type="text"><?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=users&tmpl=component&multiple=1&profile=-1&group=-1&published=all&jscallback=addMembers&excludeClusterMembers=1&clusterId=<?php echo isset($clusterId) ? $clusterId : '';?></content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON'); ?></button>
	</buttons>
</dialog>
