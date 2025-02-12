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
<div class="tab-box tab-box-sidenav">
	<div class="tabbable">
		<div class="es-edit-panel__side">
			<?php $i = 0; ?>

			<ul class="nav nav-tabs nav-tabs-icons nav-tabs-side">
				<?php foreach ($steps as $step) { ?>
					<li class="es-edit-panel__tabs-item <?php echo $i == 0 ? ' active' : '';?>" data-stepnav data-for="<?php echo $step->id; ?>">
						<a href="#step-<?php echo $step->id;?>" class="es-edit-panel__tabs-link" data-es-toggle="tab"><?php echo $step->get( 'title' );?></a>
					</li>
					<?php $i++; ?>
				<?php } ?>

				<li class="es-edit-panel__tabs-item" data-stepnav data-for="setting">
					<a href="#setting" class="es-edit-panel__tabs-link" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_ACCOUNT_SETTING');?></a>
				</li>
			</ul>
		</div>
		<div class="tab-content tab-content-side">
			<?php $x = 0;?>

			<?php foreach ($steps as $step) { ?>
				<div id="step-<?php echo $step->id;?>" class="tab-pane <?php echo $x == 0 ? ' active' : '';?>" data-profile-adminedit-fields-content data-stepcontent data-for="<?php echo $step->id; ?>">
					<div class="panel">
						<div class="es-edit-panel__form-wrap">
							<div class="o-form-horizontal">
							<?php foreach ($step->fields as $field) { ?>
								<?php if (!empty($field->output)) { ?>
								<div class="<?php echo $field->isConditional() ? 't-hidden' : ''; ?>"
									data-isconditional="<?php echo $field->isConditional(); ?>"
									data-conditions="<?php echo ES::string()->escape($field->getConditions(false)); ?>"
									data-conditions-logic="<?php echo $field->getConditionsLogic(); ?>"
									data-field-item="<?php echo $field->element; ?>"
									data-id="<?php echo $field->id; ?>"
									data-required="<?php echo $field->required; ?>"
									data-name="<?php echo SOCIAL_FIELDS_PREFIX . $field->id; ?>"
								>
									<?php echo $field->output; ?>
								</div>
								<?php } ?>

								<?php if (!$field->getApp()->id) { ?>
								<div class="alert alert-danger"><?php echo JText::_('COM_EASYSOCIAL_FIELDS_INVALID_APP'); ?></div>
								<?php } ?>
							<?php } ?>
							</div>
						</div>
					</div>

				</div>
				<?php $x++; ?>
			<?php } ?>

			<div id="setting" class="tab-pane" data-profile-adminedit-fields-content data-stepcontent data-for="setting">
				<div class="panel">
					<div class="es-edit-panel__form-wrap">
						<div class="o-form-horizontal">
							<div class="es-snackbar"><?php echo JText::_('COM_EASYSOCIAL_USERS_ACCOUNT_SETTING'); ?></div>

							<div class="o-form-group">
								<label for="require_reset" class="o-control-label">
									<?php echo JText::_('COM_EASYSOCIAL_USERS_ACCOUNT_REQUIRE_RESET'); ?>:
									<i class="fa fa-question-circle t-lg-pull-right"
										<?php echo $this->html( 'bootstrap.popover' , JText::_( 'COM_EASYSOCIAL_USERS_ACCOUNT_REQUIRE_RESET' ) , JText::_( 'COM_EASYSOCIAL_USERS_ACCOUNT_REQUIRE_RESET_HELP' ) , 'bottom' ); ?>
									></i>
								</label>

								<div class="o-control-input">
									<?php echo $this->html( 'grid.boolean' , 'require_reset' , isset($user->require_reset) ? $user->require_reset : '0' , 'require_reset' ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
