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
	<width>450</width>
	<height>250</height>
	<title><?php echo JText::_('COM_ES_CLUSTERS_REJECT_DIALOG_TITLE_' . strtoupper($cluster->getType())); ?></title>
	<content>
			<p>
				<?php echo JText::_('COM_ES_CLUSTERS_REJECT_DIALOG_CONTENT_' . strtoupper($cluster->getType()));?>
			</p>

			<div class="o-form-group" style="min-height: 80px;">
				<textarea class="o-form-control" name="reason" data-reject-message style="width: 100%;min-height: 80px;" placeholder="<?php echo JText::_('COM_ES_CLUSTERS_REJECT_PLACEHOLDER_REASON');?>"></textarea>
			</div>
			
			<div class="o-form-group">
				<div class="o-checkbox">
					<input type="checkbox" id="sendRejectEmail" name="email" value="1" data-send-email/>
					<label for="sendRejectEmail"><?php echo JText::_('COM_ES_CLUSTERS_APPROVE_DIALOG_SEND_EMAIL');?></label>
				</div>

				<div class="o-checkbox">
					<input type="checkbox" id="deleteUser" name="delete" value="1" data-delete-cluster/>
					<label for="deleteUser"><?php echo JText::_('COM_ES_CLUSTERS_REJECT_ALSO_DELETE_' . strtoupper($cluster->getType()));?></label>
				</div>
			</div>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-reject-button type="button" class="btn btn-sm btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON'); ?></button>
	</buttons>
</dialog>
