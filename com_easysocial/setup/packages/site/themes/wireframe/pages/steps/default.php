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

<?php if (!$this->my->isSiteAdmin() && $this->my->getAccess()->get('pages.moderate')) { ?>
	<div class="o-alert o-alert--warning">
		<?php echo JText::_('COM_EASYSOCIAL_PAGES_SUBJECT_TO_APPROVAL'); ?>
	</div>
<?php } ?>

<div class="es-container" data-es-pages-create>
	<div class="es-content">

		<?php echo $this->html('html.steps', $steps, $currentStep, 
						array('link' => ESR::pages(array('layout' => 'create')), 'tooltip' => 'COM_EASYSOCIAL_PAGES_SELECT_PAGE_CATEGORY'), 
						array('tooltip' => 'COM_EASYSOCIAL_PAGES_PAGE_CREATION_COMPLETED')
					); ?>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" enctype="multipart/form-data" class="es-forms" data-form>
			<div class="es-forms__group">
				<div class="es-forms__content">
					<div class="o-form-horizontal">
						<?php if ($fields) { ?>
							<?php foreach ($fields as $field) { ?>
								<?php echo $this->loadTemplate('site/registration/steps/field', array('field' => $field, 'errors' => $errors)); ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<?php if ($currentStep != 1) { ?>
					<button type="button" class="btn btn-es-default-o pull-left" data-previous><?php echo JText::_('COM_EASYSOCIAL_PREVIOUS_BUTTON'); ?></button>
					<?php } ?>

					<button type="button" class="btn btn-es-primary t-lg-pull-right" data-next>
						<?php echo $currentIndex === $totalSteps || $totalSteps < 2 ? JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON') : JText::_('COM_EASYSOCIAL_CONTINUE_BUTTON');?>
					</button>

					<span class="t-lg-pull-right t-lg-mr--xl t-lg-mt--md"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATIONS_REQUIRED');?></span>
				</div>
			</div>

			<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check />
			<input type="hidden" name="currentStep" value="<?php echo $currentIndex; ?>" />
			
			<?php echo $this->html('form.action', 'pages', 'store'); ?>
		</form>
	</div>
</div>
