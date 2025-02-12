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
		<?php if (count($steps) > 1) { ?>
		<div class="es-edit-panel__side">
			<ul class="nav nav-tabs nav-tabs-icons nav-tabs-side">
				<?php $x = 0;?>
				<?php foreach ($steps as $step) { ?>
					<li class="es-edit-panel__tabs-item <?php echo $x == 0 ? 'active' : '';?>">
						<a href="#step-<?php echo $step->id;?>" class="es-edit-panel__tabs-link" data-es-toggle="tab"><?php echo $step->_('title');?></a>
					</li>
					<?php $x++; ?>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>

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
						<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php $x++; ?>
		<?php } ?>
		</div>
	</div>
</div>
