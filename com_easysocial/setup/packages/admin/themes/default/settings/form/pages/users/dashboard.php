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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_SETTINGS_DASHBOARD'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_DEFAULT_START_ITEM'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'users.dashboard.start', $this->config->get('users.dashboard.start'), array(
							array('value' => 'me', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_ME_AND_FRIENDS'),
							array('value' => 'everyone', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_EVERYONE'),
							array('value' => 'following', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_FOLLOWING'),
						)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'users.dashboard.startsession', 'COM_ES_SETTINGS_USERS_REMEMBER_START_ITEM'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_USERS_SETTINGS_DASHBOARD_SIDEBAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'users.dashboard.sidebar', $this->config->get('users.dashboard.sidebar'), array(
							array('value' => 'left', 'text' => 'COM_ES_LEFT'),
							array('value' => 'right', 'text' => 'COM_ES_RIGHT')
						));?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'users.dashboard.everyone', 'COM_EASYSOCIAL_USERS_SETTINGS_DASHBOARD_SHOW_EVERYONE'); ?>
				<?php echo $this->html('settings.toggle', 'users.dashboard.customfilters', 'COM_EASYSOCIAL_USERS_SETTINGS_DASHBOARD_SHOW_CUSTOM_FILTERS'); ?>
				<?php echo $this->html('settings.toggle', 'users.dashboard.guest', 'COM_ES_USERS_SETTINGS_DASHBOARD_FOR_GUEST'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
