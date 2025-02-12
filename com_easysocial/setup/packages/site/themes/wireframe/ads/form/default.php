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
		<form method="post" action="<?php echo JRoute::_('index.php');?>" enctype="multipart/form-data" data-ad-form>
			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('html.snackbar', !$ad->id ? 'COM_ES_CREATE_AD' : 'COM_ES_EDIT_AD'); ?>
				</div>

				<div class="es-forms__content">
					<div class="t-lg-mb--lg">
						<?php if (!$ad->id) { ?>
							<?php echo JText::_('COM_ES_AD_CREATE_INFO'); ?>
						<?php } ?>

						<?php if ($ad->isPublished()) { ?>
							<?php echo JText::_('COM_ES_AD_EDIT_INFO'); ?>
						<?php } ?>

						<?php if ($ad->isDraft() && $reject) { ?>
							<?php echo JText::_('COM_ES_AD_EDIT_DRAFT'); ?>

							<div class="o-alert o-alert--warning t-lg-mt--lg">
								<?php echo $reject->message;?>
							</div>
						<?php } ?>
					</div>

					<div class="o-form-horizontal">
						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_COVER_TITLE', 3, false); ?>

							<div class="o-control-input">
								<?php if ($ad->cover) { ?>
								<div class="t-lg-mb--lg">
									<img src="<?php echo $ad->getCover();?>" width="320" />
								</div>
								<div class="t-lg-mb--lg">
									<?php echo JText::_('COM_ES_AD_COVER_TIPS'); ?>
								</div>
								<?php } ?>

								<div class="o-input-group">
									<input class="o-form-control" type="text" readonly data-cover-title />

									<span class="o-input-group__btn">
										<span class="btn btn-es-default btn-file" data-browse-button>
											<?php echo JText::_('FIELDS_USER_COVER_BROWSE_FILE'); ?>&hellip;
											<input type="file" name="cover" data-cover accept="image/png, image/jpeg"/>
										</span>
									</span>
								</div>
								<div class="es-fields-error-note" data-cover-error><?php echo JText::_('COM_ES_ADS_EMPTY_COVER'); ?></div>
								<?php echo $this->html('form.help', 'COM_ES_ADS_COVER_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_HEADLINE', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.inputbox', 'title', $this->html('string.escape', $ad->title), 'title', ['data-ads-title']); ?>

								<div class="es-fields-error-note" data-title-error><?php echo JText::_('COM_ES_ADS_EMPTY_TITLE'); ?></div>

								<?php echo $this->html('form.help', 'COM_ES_AD_HEADLINE_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_URL', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.inputbox', 'link', $this->html('string.escape', $ad->link), 'url', []); ?>

								<?php echo $this->html('form.help', 'COM_ES_AD_URL_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_INTRODUCTION', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.textarea', 'intro', $this->html('string.escape', $ad->intro), 'intro', []); ?>

								<?php echo $this->html('form.help', 'COM_ES_AD_INTRODUCTION_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_CONTENT', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.textarea', 'content', $this->html('string.escape', $ad->content), 'content', []); ?>

								<?php echo $this->html('form.help', 'COM_ES_AD_CONTENT_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_BUTTON_TYPE', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('form.dropdown', 'button_type', [
										'0' => 'COM_ES_ADS_FORM_NO_BUTTON',
										'1' => 'COM_ES_ADS_FORM_BUTTON_LISTEN_NOW',
										'2' => 'COM_ES_ADS_FORM_BUTTON_SHOP_NOW',
										'3' => 'COM_ES_ADS_FORM_BUTTON_SIGN_UP',
										'4' => 'COM_ES_ADS_FORM_BUTTON_SUBSCRIBE',
										'5' => 'COM_ES_ADS_FORM_BUTTON_LEARN_MORE'
								], $ad->button_type); ?>

								<?php echo $this->html('form.help', 'COM_ES_AD_BUTTON_TYPE_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_START_DATE', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('form.calendar', 'start_date', $ad->start_date, 'start_date', '', true, 'YYYY-MM-DD HH:mm'); ?>

								<?php echo $this->html('form.help', 'COM_ES_AD_START_DATE_TIPS'); ?>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_ES_AD_END_DATE', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('form.calendar', 'end_date', $ad->end_date, 'end_date', '', true, 'YYYY-MM-DD HH:mm'); ?>
								<?php echo $this->html('form.help', 'COM_ES_AD_END_DATE_TIPS'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="es-forms__actions t-lg-mt--lg">
				<div class="o-form-actions">
					<button class="btn btn-es-primary t-pull-right" data-save-button>
						<?php echo JText::_('COM_ES_SUBMIT_FOR_APPROVAL');?>
					</button>
				</div>
			</div>

			<?php echo $this->html('form.hidden', 'id', $ad->id); ?>
			<?php echo $this->html('form.action', 'ads', 'save'); ?>
		</form>
	</div>
</div>
