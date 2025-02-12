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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_BADGES_FORM_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_BADGE_IMAGE'); ?>

					<div class="col-md-7">
						<?php if ($badge->avatar) { ?>
						<div class="es-img-holder">
							<img src="<?php echo $badge->getAvatar();?>" width="128" />
						</div>
						<?php } ?>

						<input type="file" name="image" id="image" class="input" style="width:265px;" data-uniform />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_TITLE'); ?>

					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $badge->title;?>" name="title" id="title" placeholder="<?php echo JText::_('COM_EASYSOCIAL_ACCESS_RULE_RULE_TITLE_PLACEHOLDER', true);?>" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_ALIAS'); ?>
					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $badge->alias;?>" name="alias" id="alias" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_ACCESS_RULE_RULE_TITLE_PLACEHOLDER' , true );?>" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_ACHIEVE_TYPE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'achieve_type', $badge->achieve_type, array(
							array('value' => 'frequency', 'text' => 'COM_EASYSOCIAL_BADGES_ACHIEVE_TYPE_FREQUENCY'),
							array('value' => 'points', 'text' => 'COM_EASYSOCIAL_BADGES_ACHIEVE_TYPE_POINTS')
						), 'achieve_type', 'data-es-badges-achieve-type'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $badge->achieve_type == 'frequency' ? 'hidden' : ''?>" data-es-badges-points>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_POINTS_THRESHOLD'); ?>
					<div class="col-md-7">
						<input type="text" class="input-mini text-center o-form-control" value="<?php echo $badge->points_threshold;?>" id="points_threshold" name="points_threshold" /> <?php echo JText::_('points'); ?>
					</div>
				</div>				

				<div class="form-group <?php echo $badge->achieve_type == 'frequency' ? 'hidden' : ''?>" data-es-badges-points>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_POINTS_INCREASE_RULE'); ?>
					<div class="col-md-7">
					<?php echo $this->html('grid.selectlist', 'points_increase_rule', $badge->points_increase_rule, $pointsIncreaseSelection); ?>
					</div>
				</div>

				<div class="form-group <?php echo $badge->achieve_type == 'frequency' ? 'hidden' : ''?>" data-es-badges-points>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_POINTS_DECREASE_RULE'); ?>
					<div class="col-md-7">
					<?php echo $this->html('grid.selectlist', 'points_decrease_rule', $badge->points_decrease_rule, $pointsDecreaseSelection); ?>
					</div>
				</div>

				<div class="form-group <?php echo $badge->achieve_type == 'points' ? 'hidden' : ''?>" data-es-badges-frequency>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_FREQUENCY'); ?>
					<div class="col-md-7">
						<input type="text" class="input-mini text-center o-form-control" value="<?php echo $badge->frequency;?>" id="frequency" name="frequency" /> <?php echo JText::_( 'COM_EASYSOCIAL_TIMES' ); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_DESCRIPTION'); ?>
					<div class="col-md-7">
						<textarea name="description" id="description" class="o-form-control"><?php echo $badge->description;?></textarea>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_HOW_TO'); ?>
					<div class="col-md-7">
						<textarea name="howto" id="howto" class="o-form-control"><?php echo $badge->howto;?></textarea>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_CREATED'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.calendar', 'created', $badge->created, 'created', '', false, 'DD-MM-YYYY', false, true, true); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_BADGES_FORM_STATE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $badge->state); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<?php if ($badge->id) { ?>
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_BADGES_STATS'); ?>

			<div class="panel-body">
				<table class="table table-striped table-noborder">
					<tbody>
						<tr>
							<td width="20%">
								<?php echo JText::_( 'COM_EASYSOCIAL_BADGES_FORM_COMMAND' );?>:
							</td>
							<td>
								<strong><?php echo $badge->command; ?></strong>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'COM_EASYSOCIAL_BADGES_FORM_EXTENSION' ); ?>:
							</td>
							<td>
								<strong><?php echo $badge->getExtensionTitle(); ?></strong>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'COM_EASYSOCIAL_BADGES_FORM_ACHIEVERS' ); ?>:
							</td>
							<td>
								<strong><?php echo $badge->getTotalAchievers();?> <?php echo JText::_( 'COM_EASYSOCIAL_ACHIEVERS' ); ?></strong>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<?php echo $this->html('form.action', 'badges', ''); ?>
<input type="hidden" name="id" value="<?php echo $badge->id; ?>" />
<input type="hidden" name="extension" value="<?php echo $badge->extension;?>" />
<input type="hidden" name="command" value="<?php echo $badge->command;?>" />
</form>
