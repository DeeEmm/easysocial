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
<dialog>
	<width>450</width>
	<height>250</height>
	<selectors type="json">
	{
		"{submitButton}": "[data-submit-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{message}": "[data-reject-message]",
		"{deleteAd}": "[data-delete-ads]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}

	</bindings>
	<title><?php echo JText::_('COM_ES_AD_DIALOG_REJECT_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_ES_AD_DIALOG_REJECT_CONFIRMATION'); ?></p>

		<p style="min-height: 80px;">
			<textarea class="input-xlarge" name="reason" data-reject-message style="width: 100%;min-height: 80px;" placeholder="<?php echo JText::_('COM_ES_ADS_REJECT_MESSAGE_PLACEHOLDER');?>"></textarea>
		</p>

		<div class="o-form-group t-lg-mt--lg">
			<div class="o-checkbox">
				<input type="checkbox" id="deleteAd" name="email" value="1" data-delete-ads />
				<label for="deleteAd"><?php echo JText::_('Also delete the ad from the site');?></label>
			</div>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_ES_REJECT_BUTTON'); ?></button>
	</buttons>
</dialog>
