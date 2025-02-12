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
	<height>250</height>
	<title><?php echo JText::_('COM_EASYSOCIAL_BLOCK_USER_DIALOG_TITLE'); ?></title>
	<content>
		<p class="t-fs--sm"><?php echo JText::sprintf('COM_EASYSOCIAL_BLOCK_USER_DIALOG_CONTENT', '<b>' . $target->getName() . '</b>'); ?></p>

		<textarea data-block-reason class="o-form-control t-lg-mt--md" style="width: 100%;height: 100px;" placeholder="<?php echo JText::_('COM_EASYSOCIAL_BLOCK_USER_DIALOG_PLACEHOLDER');?>"></textarea>

		<div class="t-lg-mt--md">
			<?php echo JText::_('COM_EASYSOCIAL_BLOCK_USER_FOOTNOTE');?>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-block-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_BLOCK_USER_BUTTON'); ?></button>
	</buttons>
</dialog>
