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
<div class="es-container">
	<div class="es-content">
		<form method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">
			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('form.title', 'COM_ES_SUBMIT_VERIFICATION', 'h1'); ?>
				</div>

				<div class="es-forms__content">
					<?php echo $this->render('module', 'es-profile-submitverification-before-contents'); ?>

					<p>
						<?php echo JText::_('COM_ES_VERIFICATIONS_' . strtoupper($type) . '_SUBMIT_VERIFICATION_INFO');?></p>

					<div class="o-form-group">
						<?php echo $this->html('form.label', 'COM_ES_VERIFICATION_REQUEST_MESSAGE', 3, false); ?>

						<div class="o-control-input">
						<?php echo $this->html('grid.textarea', 'message', '', 'message', array('placeholder="' . JText::_('COM_ES_VERIFICATION_MESSAGE_PLACEHOLDER') . '"')); ?>
						</div>
					</div>

					<?php echo $this->render('module', 'es-profile-submitverification-after-contents'); ?>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a href="<?php echo ESR::profile();?>" class="btn btn-es-default-o t-lg-pull-left"><?php echo JText::_('COM_ES_CANCEL');?></a>
					<button class="btn btn-es-primary-o t-lg-pull-right" data-save-button><?php echo JText::_('COM_ES_SEND_REQUEST');?></button>
				</div>
			</div>
			<?php echo $this->html('form.action', 'verifications', 'save'); ?>
			<?php echo $this->html('form.hidden', 'type', $type); ?>
			<?php echo $this->html('form.hidden', 'uid', $obj->id);?>
		</form>
	</div>
</div>
