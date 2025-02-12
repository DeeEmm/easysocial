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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_SETTINGS_PROFILE'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SETTINGS_DEFAULT_AVATAR'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('user_avatar') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="user_avatar">
										<i class="fa fa-times"></i>
									</a>
								</div>
								<img src="<?php echo ES::getDefaultAvatar('user', 'medium'); ?>" width="64" height="64" data-image-source data-default="<?php echo ES::getDefaultAvatar('user', 'medium', true);?>" />
							</div>
						</div>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="user_avatar" id="user_avatar" class="input" style="width:265px;" data-uniform />
						</div>

						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_SETTINGS_DEFAULT_AVATAR_SIZE_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SETTINGS_DEFAULT_COVER'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('user_cover') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="user_cover">
										<i class="fa fa-times"></i>
									</a>
								</div>
								<img src="<?php echo ES::getDefaultCover('user'); ?>" width="256" height="98" data-image-source data-default="<?php echo ES::getDefaultCover('user', true);?>" />
							</div>
						</div>

						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="user_cover" id="user_cover" class="input" style="width:265px;" data-uniform />
						</div>

						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_SETTINGS_DEFAULT_COVER_SIZE_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_PROFILE_DEFAULT_DISPLAY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'users.profile.display', $this->config->get('users.profile.display'), array(
							array('value' => 'timeline', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_PROFILE_DISPLAY_TIMELINE'),
							array('value' => 'about', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_PROFILE_DISPLAY_ABOUT')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_USERS_PROFILE_SIDEBAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'users.profile.sidebar', $this->config->get('users.profile.sidebar'), array(
							array('value' => 'hidden', 'text' => 'COM_ES_HIDDEN'),
							array('value' => 'left', 'text' => 'COM_ES_LEFT'),
							array('value' => 'right', 'text' => 'COM_ES_RIGHT')
						));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_DISPLAY_PROFILE_COVER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'users.layout.cover', $this->config->get('users.layout.cover'));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_DISPLAY_PROFILE_TITLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'users.layout.profiletitle', $this->config->get('users.layout.profiletitle'));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_USERS_SETTINGS_DISPLAY_LASTONLINE_TITLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'users.layout.lastonline', $this->config->get('users.layout.lastonline'));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_DISPLAY_BADGES'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'users.layout.badges', $this->config->get('users.layout.badges'));?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_DISPLAY_AGE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'users.layout.age', $this->config->get('users.layout.age'));?>
					</div>
				</div>
				
				<?php echo $this->html('settings.toggle', 'users.layout.address', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_DISPLAY_ADDRESS'); ?>
				<?php echo $this->html('settings.toggle', 'users.layout.gender', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_DISPLAY_GENDER'); ?>
				<?php echo $this->html('settings.toggle', 'users.layout.points', 'COM_ES_THEMES_WIREFRAME_FIELD_DISPLAY_POINTS'); ?>
				<?php echo $this->html('settings.toggle', 'users.layout.joindate', 'COM_ES_THEMES_WIREFRAME_FIELD_DISPLAY_JOINDATE'); ?>
				<?php echo $this->html('settings.toggle', 'users.layout.apps', 'COM_EASYSOCIAL_THEMES_WIREFRAME_DASHBOARD_SHOW_APP_BROWSE'); ?>
				<?php echo $this->html('settings.toggle', 'users.layout.sidebarapps', 'COM_ES_DISPLAY_APPS_SIDEBAR'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">

	</div>

</div>
