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
		<form method="post" action="<?php echo JRoute::_('index.php');?>" enctype="multipart/form-data" data-advertiser-form>
			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('html.snackbar', 'COM_ES_ADVERTISER_ACCOUNT_TITLE'); ?>
				</div>

				<div class="es-forms__content">
					<p class="t-lg-mb--lg">
						<?php if ($inProgress) { ?>
							<?php echo JText::_('COM_ES_ADVERTISER_SUBMISSION_UNDER_MODERATION'); ?>
						<?php } else { ?>
							<?php if ($advertiser) { ?>
								<?php echo JText::_('COM_ES_ADVERTISER_CREATED_INFO');?>
							<?php } else { ?>
								<?php echo JText::_('COM_ES_ADVERTISER_INFO');?>
							<?php } ?>
						<?php } ?>
					</p>

					<div class="o-form-horizontal">
						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_ADVERTISER_COMPANY_LOGO'); ?>

							<div class="o-control-input">
								<?php if ($advertiser) { ?>
								<div>
									<img src="<?php echo $advertiser->getLogo();?>" width="128" />
								</div>
								<?php } ?>

								<?php if (!$inProgress) { ?>
								<div class="o-input-group <?php echo !$advertiser ? '' : 't-lg-mt--lg';?>">
									<input class="o-form-control" type="text" readonly data-logo-title />

									<span class="o-input-group__btn">
										<span class="btn btn-es-default btn-file" data-browse-button>
											<?php echo JText::_('FIELDS_USER_COVER_BROWSE_FILE'); ?>&hellip;
											<input type="file" name="logo" data-logo />
										</span>
									</span>
								</div>

								<p class="help-block" data-link-notice>
									<?php echo JText::_('COM_ES_ADVERTISER_COMPANY_LOGO_TIPS');?>
								</p>
								<?php } ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_ADVERTISER_COMPANY_TITLE'); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.inputbox', 'company', $inProgress || $advertiser ? $this->html('string.escape', $advertiser->getCompanyName()) : '', 'company-title', [
									'placeholder="' . JText::_('COM_ES_ADVERTISER_COMPANY_TITLE_PLACEHOLDER') . '"',
									$inProgress ? 'disabled="disabled"' : ''
								]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php if (!$inProgress) { ?>
			<div class="es-forms__actions t-lg-mt--lg">
				<div class="o-form-actions">
					<button class="btn btn-es-primary t-pull-right" data-save-button>
						<?php echo JText::_('COM_ES_SUBMIT_FOR_APPROVAL');?>
					</button>
				</div>
			</div>
			<?php } ?>

			<?php echo $this->html('form.action', 'advertiser', 'save'); ?>
		</form>
	</div>
</div>
