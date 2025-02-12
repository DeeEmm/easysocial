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
<div class="es-container" data-profile-edit data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/edit/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<form method="post" action="<?php echo JRoute::_('index.php'); ?>" class="es-forms" data-profile-fields-form autocomplete="off">

			<?php echo $this->render('module' , 'es-profile-edit-before-contents'); ?>

			<div class="tab-content">
				<?php $i = 0; ?>
				<?php foreach ($steps as $step) { ?>
				<div class="tab-content__item step-content step-<?php echo $step->id;?><?php echo ($i == 0 && !$activeStep) || ($activeStep && $activeStep == $step->id) ? ' active' :'';?>"
					 data-profile-edit-fields-content data-id="<?php echo $step->id; ?>"
				>
					<?php if ($step->fields){ ?>
					<div class="es-forms__group">
						<div class="es-forms__content">
							<div class="o-form-horizontal">
								<?php foreach ($step->fields as $field) { ?>
									<?php echo $this->loadTemplate('site/registration/steps/field', array('field' => $field, 'errors' => '')); ?>

									<?php if (!$field->getApp()->id) { ?>
									<div class="o-alert o-alert--danger"><?php echo JText::_('COM_EASYSOCIAL_FIELDS_INVALID_APP'); ?></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<?php $i++; ?>
				<?php } ?>

				<?php if ($this->config->get('honeypot.profile')) { ?>
					<?php echo $this->html('form.honeypot'); ?>
				<?php } ?>

				<?php foreach ($oauthClients as $client) { ?>
					<?php echo $this->loadTemplate('site/profile/edit/' . $client->getType(), array('client' => $client)); ?>
				<?php } ?>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions" data-profile-actions>

					<?php if ($this->my->hasCommunityAccess()) { ?>
						<div class="t-pull-left">
							<a href="<?php echo $this->my->getPermalink();?>" class="btn btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
						</div>

						<?php if ($editLogic == 'steps' && !$isLastStep) { ?>
						<div class="t-pull-left t-lg-ml--md">
							<button type="button" class="btn btn-es-primary-o" data-profile-fields-save-close>
								<?php echo JText::_('COM_ES_PROFILE_UPDATE_CLOSE_BTN'); ?>
							</button>
						</div>
						<?php } ?>
					<?php } ?>

					<div class="t-pull-right">
						<?php if ($editLogic == 'steps') { ?>
							<button type="button" class="btn btn-es-primary" data-profile-fields-save>
								<?php echo ($isLastStep) ? JText::_('COM_ES_PROFILE_UPDATE_COMPLETE_BTN') : JText::_('COM_ES_PROFILE_UPDATE_NEXT_BTN');?>
							</button>
						<?php } ?>

						<?php if ($editLogic != 'steps') { ?>
							<button type="button" class="btn btn-es-primary"<?php echo (!$this->my->hasCommunityAccess()) ? ' data-profile-fields-save' : ' data-profile-fields-save-close'; ?> >
								<?php echo JText::_('COM_ES_UPDATE');?>
							</button>
						<?php } ?>
					</div>
				</div>
			</div>

			<?php echo $this->render('module' , 'es-profile-edit-after-contents'); ?>

			<?php echo $this->html('form.hidden', 'Itemid', $this->input->get('Itemid', 0, 'int')); ?>
			<?php echo $this->html('form.hidden', 'profileId', $profile->id); ?>
			<?php echo $this->html('form.hidden', 'workflowId', $workflow->id); ?>

			<?php echo $this->html('form.hidden', 'userId', (int) $user->id); ?>

			<?php if ($editLogic == 'steps') { ?>
				<?php echo $this->html('form.hidden', 'stepId', $steps[0]->id); ?>
				<?php echo $this->html('form.hidden', 'nextStepId', ''); ?>
			<?php } ?>

			<?php echo $this->html('form.action', 'profile', 'save'); ?>
			<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check />
		</form>
	</div>
</div>
