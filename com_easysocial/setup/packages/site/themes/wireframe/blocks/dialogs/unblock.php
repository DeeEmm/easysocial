<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>450</width>
	<height>150</height>
	<title><?php echo JText::_('COM_EASYSOCIAL_UNBLOCK_USER_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_EASYSOCIAL_UNBLOCK_USER_DIALOG_CONTENT', '<b>' . $target->getName() . '</b>'); ?></p>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-unblock-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_UNBLOCK_USER_BUTTON'); ?></button>
	</buttons>
</dialog>
