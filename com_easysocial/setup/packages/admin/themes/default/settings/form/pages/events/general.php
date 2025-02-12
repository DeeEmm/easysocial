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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'events.enabled', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ENABLE_EVENTS'); ?>
				<?php echo $this->html('settings.toggle', 'events.ical', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ENABLE_ICAL_EVENTS'); ?>
				<?php echo $this->html('settings.toggle', 'events.invite.allowmembers', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ALLOW_MEMBERS_INVITE'); ?>
				<?php echo $this->html('settings.toggle', 'events.invite.nonfriends', 'COM_EASYSOCIAL_SETTINGS_ALLOW_INVITE_NON_FRIENDS'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_START_OF_WEEK'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'events.startofweek', $this->config->get('events.startofweek'), array(
							array('value' => 1, 'text' => JText::_('MONDAY')),
							array('value' => 2, 'text' => JText::_('TUESDAY')),
							array('value' => 3, 'text' => JTEXT::_('WEDNESDAY')),
							array('value' => 4, 'text' => JTEXT::_('THURSDAY')),
							array('value' => 5, 'text' => JTEXT::_('FRIDAY')),
							array('value' => 6, 'text' => JTEXT::_('SATURDAY')),
							array('value' => 0, 'text' => JTEXT::_('SUNDAY'))
						)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'events.feed.includeadmin', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_FEED_INCLUD_ADMIN'); ?>
				<?php echo $this->html('settings.toggle', 'events.reminder.enabled', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_EVENT_REMINDER'); ?>
				<?php echo $this->html('settings.toggle', 'events.sharing.showprivate', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_SOCIAL_SHARING_PRIVATE'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EVENTS_SETTINGS_NEARBY_EVENTS_RADIUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'events.nearby.radius', $this->config->get('events.nearby.radius'), ES::getNearbyRadiusOptions()); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'events.tag.nonfriends', 'COM_ES_EVENTS_SETTINGS_TAG_NONFRIENDS'); ?>
				<?php echo $this->html('settings.toggle', 'events.unfeatured.pastevent', 'COM_ES_EVENTS_SETTINGS_UNFEATURED_PAST_EVENTS'); ?>
				<?php echo $this->html('settings.toggle', 'events.editmoderation', 'COM_ES_EVENTS_MODERATE_EDITED_EVENTS'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_SETTINGS_RECURRING_EVENTS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'events.recurring.appendTitle', 'COM_ES_RECURRING_EVENTS_APPEND_ORIGINAL_DATE_TO_TITLE'); ?>

				<?php echo $this->html('settings.textbox', 'events.recurringlimit', 'COM_EASYSOCIAL_EVENTS_SETTINGS_RECURRING_LIMIT', '', [
						'postfix' => 'COM_EASYSOCIAL_EVENTS'
					], '', 'text-center'
				); ?>
			</div>
		</div>
	</div>
</div>
