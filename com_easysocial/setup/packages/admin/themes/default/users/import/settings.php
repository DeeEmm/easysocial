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

$total = count($data);
?>
<form name="adminForm" id="adminForm" method="post" data-table-grid enctype="multipart/form-data">
	<div class="row">

		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_FROM_CSV_SETTINGS'); ?>

				<div class="panel-body">

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_TO_PROFILE', true, '', 5); ?>

						<div class="col-md-7">
							<div class="t-lg-mt--md">
								<p><?php echo $profile->getTitle(); ?></p>
							</div>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_AUTO_GENERATE_PASSWORD'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'import_autopassword', isset($previousData['import_autopassword']) ? $previousData['import_autopassword'] : false, '', 'data-password-settings-toggle'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_AUTO_APPROVE_USER'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'import_autoapprove', isset($previousData['import_autoapprove']) ? $previousData['import_autoapprove'] : false); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6<?php echo isset($previousData['import_autopassword']) ? ' t-hidden' : ''; ?>" data-password-settings>
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_PASSWORD_SETTINGS'); ?>

				<div class="panel-body">
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_PASSWORD_TYPE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('grid.selectlist', 'import_passwordtype', isset($previousData['import_passwordtype']) ? $previousData['import_passwordtype'] : 'plain', array(
									array('value' => 'plain', 'text' => 'COM_ES_IMPORT_USERS_PLAIN_TEXT'),
									array('value' => 'encrypted', 'text' => 'COM_ES_IMPORT_USERS_JOOMLA_ENCRYPTED')
								)); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_FROM_CSV_FIELDS_SETTINGS'); ?>

				<div class="panel-body t-lg-p--no">
					<div class="app-table-wrapper">
						<table class="app-table table">
							<thead>
								<tr>
									<th colspan="1"></th>
									<?php for ($i = 0; $i < $totalColumn; $i++) { ?>
									<th colspan="2">
										<div class="o-select-group o-select-group--inline">
											<select name="field_id[<?php echo $i; ?>]" id="field_id[<?php echo $i; ?>]" class="o-form-control" data-field-select data-id="<?php echo $i; ?>">
												<option value="0"<?php echo $selectedFields && $selectedFields[$i] == '0' ? ' selected="selected"' : ''; ?>>-- <?php echo JText::_('COM_ES_SELECT_FIELD_COLUMN'); ?> --</option>

												<option disabled="disabled">-- <?php echo JText::_('COM_ES_JOOMLA_USER_COLUMN'); ?> --</option>
												<?php foreach ($joomlaUserColumn as $joomlaColumn) { ?>
												<option value="<?php echo $joomlaColumn; ?>" data-selection-field data-id="<?php echo $joomlaColumn; ?>" <?php echo $selectedFields && $selectedFields[$i] == $joomlaColumn ? ' selected="selected"' : ''; ?>>
													<?php echo JText::_($joomlaColumn); ?>
												</option>
												<?php } ?>
												<option disabled="disabled">-- <?php echo JText::_('COM_ES_EASYSOCIAL_FIELDS'); ?> --</option>
												<?php foreach ($customFields as $field) { ?>
												<option value="<?php echo $field->id; ?>" data-type="<?php echo $field->getType(); ?>" data-selection-field data-id="<?php echo $field->id; ?>"<?php echo $selectedFields && $selectedFields[$i] == $field->id ? ' selected="selected"' : ''; ?>>
													<?php echo JText::_($field->getTitle()); ?><?php echo $field->isCore() && $field->element != 'avatar' ? ' (Required)*' : ''; ?>
												</option>
												<?php } ?>
											</select>
											<label class="o-select-group__drop"></label>
										</div>
									</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php $count = 1; ?>
								<?php foreach ($data as $items) { ?>
								<tr>
									<td colspan="1"><?php echo $count; ?></td>
									<?php foreach ($items as $item) { ?>
									<td colspan="2"><?php echo $item; ?></td>
									<?php } ?>
								</tr>
								<?php $count++; $total--; ?>
									<?php if ($count > 10) { ?>
										<tr>
											<td colspan="<?php echo $totalColumn * 2; ?>" style="text-align:center;"><?php echo JText::sprintf('COM_ES_AND_MORE_ITEMS', $total); ?></td>
										</tr>
										<?php break; ?>
									<?php } ?>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="form-group">
					</div>

					<div class="o-form-actions t-lg-m--no">
						<button class="btn btn-primary btn-sm pull-right" type="submit"><?php echo JText::_('COM_ES_CONFIRM_FIELD_SELECTION');?> &raquo;</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.token'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="users" />
	<input type="hidden" name="task" value="importSettings" />
	<input type="hidden" name="profileId" value="<?php echo $profile->id; ?>" />
	<input type="hidden" name="passwordFieldId" value="<?php echo $passwordFieldId; ?>" />
</form>
