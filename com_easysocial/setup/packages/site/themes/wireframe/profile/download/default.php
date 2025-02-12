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
	<div class="es-content gdpr">
		<div class="es-forms__group">
			<div class="es-forms__title">
				<?php echo $this->html('form.title', 'COM_ES_GDPR_REQUEST_DATA', 'h1'); ?>
			</div>

			<div class="es-forms__content">
				<?php echo $this->render('module', 'es-profile-downloadinformation-before-contents'); ?>

				<?php if (!$download->id) { ?>
				<p><?php echo JText::_('COM_ES_GDPR_DOWNLOAD_INFORMATION_DESC');?></p>

				<div class="o-form-group">
					<div class="gdpr-download-link t-text--center t-lg-mt--xl">
						<a href="javascript:void(0);" class="btn btn-primary" data-es-gdpr-request>
							<i class="fa fa-download"></i>&nbsp; <?php echo JText::_('COM_ES_GDPR_REQUEST_DOWNLOAD');?>
						</a>
					</div>
				</div>
				<?php } ?>

				<?php if ($download->id && ($download->isProcessing() || $download->isNew() || $download->isLocked())) { ?>
				<p><?php echo JText::_('COM_ES_GDPR_DOWNLOAD_INFORMATION_PROCESSING');?></p>
				<?php } ?>

				<?php if ($download->id && $download->isReady()) { ?>
				<p><?php echo JText::sprintf('COM_ES_GDPR_REQUEST_IS_READY_DESC', $download->getExpireDays());?></p>

				<div class="o-form-group">
					<div class="gdpr-download-link t-text--center t-lg-mt--xl t-lg-mb--xl">
						<a href="<?php echo $download->getDownloadLink();?>" target="_blank" class="btn btn-primary">
							<i class="fa fa-download"></i>&nbsp; <?php echo JText::_('COM_ES_GDPR_DOWNLOAD_MY_DATA');?>
						</a>
					</div>
				</div>
				<?php } ?>

				<?php echo $this->render('module', 'es-profile-downloadinformation-after-contents'); ?>
			</div>
		</div>
	</div>
</div>
