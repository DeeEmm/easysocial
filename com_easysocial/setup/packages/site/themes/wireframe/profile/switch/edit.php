<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-profile-edit data-es-container>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/switch/mobile.info'); ?>
	<?php } ?>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/switch/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">

		<?php echo $this->render('module' , 'es-profile-edit-before-contents'); ?>

		<div class="profile-wrapper" data-profile-edit-fields>
			<form method="post" action="<?php echo JRoute::_('index.php'); ?>" class="o-form-horizontal" data-profile-fields-form autocomplete="off">
				<div class="es-profile-edit-form">
					<div class="tab-content profile-content">
						<?php $i = 0; ?>
						<?php foreach ($steps as $step) { ?>
						<div class="step-content step-<?php echo $step->id;?> <?php if ($i == 0) { ?>active<?php } ?>" data-profile-edit-fields-content data-id="<?php echo $step->id; ?>">
							<?php if ($step->fields){ ?>
								<?php foreach ($step->fields as $field){ ?>
									<?php echo $this->loadTemplate('site/registration/steps/field', array('field' => $field, 'errors' => '')); ?>

									<?php if (!$field->getApp()->id) { ?>
										<div class="o-alert o-alert--danger"><?php echo JText::_('COM_EASYSOCIAL_FIELDS_INVALID_APP'); ?></div>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						</div>
						<?php $i++; ?>
						<?php } ?>

					</div>
				</div>

				<div class="o-form-actions" data-profile-actions>

					<div class="t-lg-pull-left">
						<a href="<?php echo $this->my->getPermalink();?>" class="btn btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
					</div>

					<div class="pull-right">
						<button type="button" class="btn btn-es-primary" data-profile-switch-save><?php echo JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SAVE_TO_COMPLETE');?></button>
					</div>
				</div>

				<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check/>
				<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', 0, 'int');?>" />
				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="profile" />
				<input type="hidden" name="task" value="completeSwitch" />
				<input type="hidden" name="newProfileId" value="<?php echo $profile->id; ?>" />
				<input type="hidden" name="<?php echo ES::token();?>" value="1" />
			</form>
		</div>

		<?php echo $this->render('module' , 'es-profile-edit-after-contents'); ?>
	</div>
</div>
