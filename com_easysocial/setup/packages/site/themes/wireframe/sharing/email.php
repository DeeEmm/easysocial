<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="clearfix" data-sharing-email <?php echo !empty($url) ? 'data-token="' . $url . '"' : '';?>>

	<div data-sharing-email-frame data-sharing-email-sending class="alert fade in" style="display: none;">
		<?php echo JText::_('COM_EASYSOCIAL_SHARING_EMAIL_SENDING'); ?>
	</div>

	<div data-sharing-email-frame data-sharing-email-done class="alert alert-success fade in" style="display: none;">
		<?php echo JText::_('COM_EASYSOCIAL_SHARING_EMAIL_DONE'); ?>
	</div>

	<div data-sharing-email-frame data-sharing-email-fail class="alert alert-error fade in" style="display: none;">
		<span data-sharing-email-fail-msg><?php echo JText::_('COM_EASYSOCIAL_SHARING_EMAIL_FAIL'); ?></span>
	</div>

	<div data-sharing-email-frame data-sharing-email-form class="es-sharing-form">
		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_EASYSOCIAL_SHARING_EMAIL_RECIPIENTS', 3, false); ?>

			<div data-sharing-email-recipients class="clearfix textboxlist">
				<input type="text" class="o-form-control input-sm textboxlist-textField" data-textboxlist-textField data-sharing-email-input />
			</div>
			<p class="help-block t-fs--sm t-text--muted"><?php echo JText::_('COM_EASYSOCIAL_SHARING_EMAIL_INFO'); ?></p>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_EASYSOCIAL_SHARING_EMAIL_MESSAGE', 3, false); ?>

			<?php echo $this->html('grid.textarea', 'contents', '', 'contents', array('data-sharing-email-content', 'placeholder="' . JText::_('COM_EASYSOCIAL_SHARING_EMAIL_PLACEHOLDER') . '"')); ?>
		</div>
	</div>

</div>
