<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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

	<title><?php echo JText::_('COM_ES_CLUSTERS_APPROVE_DIALOG_TITLE_' . strtoupper($cluster->getType())); ?></title>
	<content>
		<p class="t-lg-mb--xl">
			<?php echo JText::_('COM_ES_CLUSTERS_APPROVE_DIALOG_CONTENT_' . strtoupper($cluster->getType()));?>
		</p>

		<div class="o-form-group">
			<div class="o-checkbox">
				<input type="checkbox" id="sendConfirmationMail" class="mr-5" checked="checked" name="email" data-send-email/>
				<label for="sendConfirmationMail"><?php echo JText::_('COM_ES_CLUSTERS_APPROVE_DIALOG_SEND_EMAIL');?></label>
			</div>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-approve-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></button>
	</buttons>
</dialog>
