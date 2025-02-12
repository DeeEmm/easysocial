<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_STREAM_BACKGROUND_GENERAL'); ?>

				<div class="panel-body">
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_TITLE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.text', 'title', null, $table->title, array()); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_TYPE'); ?>

						<div class="col-md-7">
							<select class="o-form-control" name="params[type]" data-background-type>
								<option value="gradient" <?php echo $params->get('type') == 'gradient' ? 'selected="selected"' : '';?>><?php echo JText::_('Gradient'); ?></option>
								<option value="solid" <?php echo $params->get('type') == 'solid' ? 'selected="selected"' : '';?>><?php echo JText::_('Solid'); ?></option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_FIRST_COLOUR'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.colorpicker', 'params[first_color]', $params->get('first_color', '#ce9ffc'), '#ce9ffc'); ?>
						</div>
					</div>

					<div class="form-group <?php echo $params->get('type', 'gradient') == 'solid' ? 't-hidden' : '';?>" data-second-color>
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_SECOND_COLOUR'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.colorpicker', 'params[second_color]', $params->get('second_color', '#7367f0'), '#7367f0'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_TEXT_COLOUR'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.colorpicker', 'params[text_color]', $params->get('text_color', '#FFFFFF'), '#FFFFFF'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_CREATED'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.calendar', 'created', $table->created ? $table->created : ES::date()->toSql(), 'created', '', false, 'DD-MM-YYYY', false, true, true); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_STREAM_BACKGROUND_PUBLISHED'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'state', $table->state); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'background', ''); ?>
	<?php echo $this->html('form.hidden', 'id', $table->id); ?>
</form>
