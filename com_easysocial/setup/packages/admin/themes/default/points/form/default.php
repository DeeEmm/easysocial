<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_POINTS_FORM_GENERAL'); ?>

				<div class="panel-body">
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_TITLE'); ?>

						<div class="col-md-7">
							<input type="text" class="o-form-control " value="<?php echo $point->title;?>" name="title" id="points-title" />
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_POINTS'); ?>

						<div class="col-md-7">
							<input type="text" class="o-form-control input-mini text-center" value="<?php echo $point->points;?>" name="points" id="points-points" />
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_ALIAS'); ?>

						<div class="col-md-7">
							<input type="text" class="o-form-control " value="<?php echo $point->alias;?>" name="alias" id="points-alias" />
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_DESCRIPTION'); ?>

						<div class="col-md-7">
							<textarea name="description" class="o-form-control "><?php echo $point->description;?></textarea>
						</div>
					</div>


					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_INTERVAL'); ?>

						<div class="col-md-7">
							<input type="text" class="o-form-control input-mini text-center" value="<?php echo $point->interval;?>" name="interval" id="points-interval" />
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_POINTS_FORM_DAILY_INTERVAL'); ?>

						<div class="col-md-7">
							<input type="text" class="o-form-control input-mini text-center" value="<?php echo $point->daily_interval;?>" name="daily_interval" id="points-daily-interval" />
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_STATE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'state', $point->state); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_POINTS_FORM_CREATED'); ?>

						<div class="col-md-7">
							<?php echo $this->html( 'form.calendar' , 'created' , $point->created ); ?>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="col-md-6">
			<?php if ($params) { ?>
				<div class="panel">
					<div class="panel-head">
						<b><?php echo JText::_('COM_EASYSOCIAL_POINTS_FORM_PARAMS');?></b>
					</div>

					<div class="panel-body">
					<?php foreach($params as $key => $param){ ?>
						<div class="form-group">
							<label for="points-<?php echo $key;?>" class="col-md-5">
								<?php echo $param['title']; ?>
								<i data-placement="bottom"
									data-title="<?php echo $this->html('string.escape', $param['title']);?>"
									data-content="<?php echo $this->html('string.escape', $param['desc']);?>"
									data-es-provide="popover"
									class="fa fa-question-circle pull-right"
									data-original-title=""></i>
							</label>
							<div class="col-md-7">
								<input type="text" name="params[<?php echo $key;?>]" class="o-form-control input-mini text-center"
									placeholder="<?php echo $param['default'];?>"
									value="<?php echo isset($param['value']) ? $param['value'] : $param['default'];?>"
								/>
							</div>
						</div>
					<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>

	</div>

	<?php echo $this->html('form.action', 'points'); ?>
	<input type="hidden" name="id" value="<?php echo $point->id; ?>" />
</form>
