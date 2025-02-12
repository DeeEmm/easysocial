<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="es-dialog es-dialog--workflow type-html active t-hidden<?php echo $installedFields ? '' : ' no-tabs'; ?>" data-field-browser>
	<div class="es-dialog-modal<?php echo $installedFields ? '' : ' is-empty'; ?>" style="width: 810px; height: 560px;">
		<div class="es-dialog-header">
			<div class="es-dialog-header__grid">
				<div class="es-dialog-header__cell">
					<div class="es-wf-dialog-title"><?php echo JText::_('COM_ES_BROWSE_FIELDS_TITLE');?></div>
				</div>
				<div class="es-dialog-close-button" data-field-browser-close><i class="fa fa-close"></i></div>
			</div>
			<div class="es-dialog-tabs es-dialog-tabs--space-evenly">
			<?php $page = 0; ?>
			<?php if ($installedFields) { ?>
				<?php foreach ($installedFields as $fieldType => $value) { ?>
					<?php if ($fieldType != 'hidden') { ?>
						<?php
							if (!$installedFields->hidden[$fieldType]) {
								$page++;
							}
						?>
						<div class="es-dialog-tabs__item<?php echo $page == 1 ? ' active' : ''; ?><?php echo $installedFields->hidden[$fieldType] ? ' t-hidden' : ''; ?>" id="<?php echo $fieldType; ?>" data-field-type-tab>
							<a href="javascript:void(0);" class="es-dialog-tabs__link">
								<?php echo JText::_('COM_ES_FIELD_' . strtoupper($fieldType)); ?>
							</a>
						</div>

						<?php if ($fieldType != 'standard') { ?>
						<div class="es-dialog-tabs__item divider"></div>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
			</div>
			<div data-message-group data-message="<?php echo JText::_('COM_ES_WORKFLOW_FIELD_ADDED'); ?>" style="line-height: 18px;margin: 16px 16px 0"></div>
		</div>
		<div class="es-dialog-body">
			<div class="es-dialog-container">
				<?php $page = 0; ?>
				<?php if ($installedFields) { ?>
					<?php foreach ($installedFields as $fieldType => $fields) { ?>
						<?php if ($fieldType != 'hidden') { ?>
							<?php
								if (!$installedFields->hidden[$fieldType]) {
									$page++;
								}
							?>
							<div class="es-dialog-content <?php echo $page != 1 ? 't-hidden' : ''; ?>" id="<?php echo $fieldType; ?>" data-field-type-content>
								<div class="es-wf-fields">
									<div class="es-wf-fields__note">
										<?php echo JText::_('COM_ES_FIELD_' . strtoupper($fieldType) . '_DESC'); ?>
									</div>

									<div class="es-wf-fields-list" data-field-browser-items>
									<?php foreach ($fields as $field) { ?>
										<div class="es-wf-fields-list__item<?php echo $field->hidden ? ' t-hidden' : ''; ?>"
											data-field-browser-item
											data-id="<?php echo $field->id; ?>"
											data-field-title="<?php echo $field->title; ?>"
											data-field-element="<?php echo $field->element; ?>"
											data-unique="<?php echo $field->isUnique(); ?>"
										>
											<a href="javascript:void(0);"><?php echo $field->title; ?></a>
										</div>
									<?php } ?>
									</div>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
				<?php } ?>

				<div class="o-loader"></div>
				<div class="o-empty">
					<div class="o-empty__content"><i class="o-empty__icon fa fa-exclamation-triangle"></i>
						<div class="o-empty__text">
							<span class="es-dialog-error-message"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_NO_FIELDS_AVAILABLE') ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
