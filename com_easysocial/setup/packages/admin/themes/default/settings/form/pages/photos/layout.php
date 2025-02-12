<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_LAYOUT'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PHOTOS_SETTINGS_ORDERING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'photos.layout.ordering', $this->config->get('photos.layout.ordering'), array(
								array('value' => 'asc', 'text' => 'COM_ES_PHOTOS_SETTINGS_ORDERING_VALUE_DESC'),
								array('value' => 'desc', 'text' => 'COM_ES_PHOTOS_SETTINGS_ORDERING_VALUE_ASC')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_SIZE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'photos.layout.size', $this->config->get('photos.layout.size'), array(
								array('value' => 'large', 'text' => 'COM_EASYSOCIAL_PHOTOS_SETTINGS_SIZE_LARGE'),
								array('value' => 'thumbnail', 'text' => 'COM_EASYSOCIAL_PHOTOS_SETTINGS_SIZE_THUMBNAIL')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ASPECT_RATIO'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'photos.layout.ratio', $this->config->get('photos.layout.ratio'), array(
								array('value' => '4x3', 'text' => '4:3'),
								array('value' => '16x9', 'text' => '16:9'),
								array('value' => '1x1', 'text' => '1:1')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_MODE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'photos.layout.mode', $this->config->get('photos.layout.mode'), array(
								array('value' => 'cover', 'text' => 'COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_MODE_STRETCH_TO_FILL'),
								array('value' => 'contain', 'text' => 'COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_MODE_STRETCH_TO_FIT')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_THRESHOLD'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'photos.layout.threshold', $this->config->get('photos.layout.threshold'), '', array('class' => 'input-short text-center'));?>
						&nbsp; <?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_THRESHOLD_UNIT'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PHOTOS_SETTINGS_USER_PROFILE_ALBUM_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'photos.layout.albumlimit', $this->config->get('photos.layout.albumlimit'), '', array('class' => 'input-short text-center'));?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_PHOTOS_SETTINGS_LAYOUT_ALBUMS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'photos.show.nonfriend', 'COM_ES_PHOTOS_SETTINGS_SHOW_PHOTOS_FROM_NON_FRIENDS'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_PHOTOS_SETTINGS_LAYOUT_ACTION_HEADER'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PHOTOS_ACTION_HEADER_AFFIX_OFFSET'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'photos.layout.affix.offset', $this->config->get('photos.layout.affix.offset'), '', array('class' => 'input-short text-center'));?>
						&nbsp; <?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_RESIZE_THRESHOLD_UNIT'); ?>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
