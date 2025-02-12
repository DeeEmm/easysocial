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
<form action="index.php" method="post" name="adminForm" id="adminForm" data-ed-form>
	<div class="row">
		<div class="col-md-4">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_THEMES_EDIT_FILE_INFO'); ?>

				<div class="panel-body o-form-horizontal">
					<div class="o-form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_SELECT_FILE', false); ?>

						<div class="o-control-input">
							<select name="file" class="o-form-control" data-files-selection data-es-select>
								<option value=""><?php echo JText::_('COM_EASYSOCIAL_THEMES_DROPDOWN_SELECT_FILE'); ?></option>
								<?php foreach ($files as $group => $files) { ?>
									<optgroup label="<?php echo ucfirst($group);?>">
										<?php foreach ($files as $file) { ?>
										<option value="<?php echo $file->id;?>" <?php echo $id == $file->id ? 'selected="selected"' : '';?>
										<?php echo $file->modified ? 'style="background-color: #d3f1d7;"' : '';?>
										>
											<?php echo $file->title;?> <?php echo $file->modified ? JText::_('(Modified)') : ''; ?>
										</option>
										<?php } ?>
									</optgroup>
								<?php } ?>
							</select>
						</div>
					</div>

					<?php if ($item) { ?>
					<div class="o-form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_MODIFIED', false); ?>

						<div class="o-control-input">
							<?php if ($item->modified) { ?>
								<span class="t-text--success">
									<i class="far fa-edit"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_THEMES_EDIT_FILE_MODIFIED'); ?>
								</span>
							<?php } else {  ?>
								<span><?php echo JText::_('COM_EASYSOCIAL_THEMES_EDIT_FILE_NOT_MODIFIED'); ?></span>
							<?php } ?>
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_PATH', false); ?>

						<div class="o-control-input">
							<input type="text" class="o-form-control disabled" value="<?php echo $item->absolute;?>" />
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_OVERRIDE_PATH', false); ?>

						<div class="o-control-input">
							<input type="text" class="o-form-control disabled" value="<?php echo $item->override;?>" />
						</div>
					</div>

					<div class="o-form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_CUSTOM_NOTES'); ?>

						<div class="o-control-input">
							<?php if ($table->notes) { ?>
							<div class="t-lg-mb--sm"><?php echo JText::_('COM_EASYSOCIAL_THEMES_CUSTOM_NOTES_AVAILABLE'); ?></div>
							<?php } ?>

							<?php echo $this->html('grid.textarea', 'notes', $table->notes); ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="col-md-8">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_THEMES_EDIT_FILE_EDITOR'); ?>
				<div class="panel-body">
					<?php if ($item) { ?>
						<?php echo $editor->display('contents', htmlentities($item->contents), '100%', '400px', 80, 20, false, null, null, null, array('syntax' => 'php', 'filter' => 'raw')); ?>
					<?php } else { ?>
					<div class="is-empty">
						<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_THEMES_EMPTY_SELECT_FILE', 'fa-database'); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

	</div>

	<input type="hidden" name="id" value="<?php echo $id;?>" />
	<input type="hidden" name="element" value="<?php echo $element;?>" />
	<?php echo $this->html('form.action', 'themes', 'themes'); ?>
</form>
