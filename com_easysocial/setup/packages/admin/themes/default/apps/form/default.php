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
<form name="ez-fields" id="adminForm" method="post" action="index.php">
	<div class="row">
		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_APP_CONFIGURATION'); ?>

				<div class="panel-body">
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_AUTHOR', false); ?>

						<div class="col-md-7">
							<?php echo $meta->author;?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_VERSION', false); ?>

						<div class="col-md-7">
							<?php echo $meta->version;?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_ABOUT', false); ?>

						<div class="col-md-7">
							<?php echo $this->html('string.truncate', JText::_($meta->desc));?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_APP_TITLE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('grid.inputbox', 'title', JText::_($app->title)); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_APP_STATE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'state', $app->state, '', $app->system ? 'disabled="disabled"' : ''); ?>
						</div>
					</div>

					<?php if ($showDefaultSetting) { ?>
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_APP_DEFAULT'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'default', $app->default); ?>
						</div>
					</div>
					<?php } ?>

					<?php if ($app->type != SOCIAL_APPS_TYPE_FIELDS && $app->hasAccessSettings()) { ?>
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_APP_ACCESS_CONTROL', true); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.' . $app->getAclType(), 'access[]', 'access', $selectedAccess, array('multiple' => true)); ?>

							<div class="help-block small">
								<?php echo JText::_('COM_EASYSOCIAL_APP_ACCESS_CONTROL_HELP');?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<?php $form = $app->renderForm('admin', $app->getParams(), 'params', true); ?>

			<?php if ($form) { ?>
			<div>
				<?php echo $form;?>
			</div>
			<?php } ?>
		</div>
	</div>

	<?php if ($customRedirect) { ?>
		<?php echo $this->html('form.hidden', 'customRedirect', $customRedirect); ?>
	<?php } ?>

	<?php echo $this->html('form.action', 'apps'); ?>

	<input type="hidden" name="id" value="<?php echo $app->id;?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
