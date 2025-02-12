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
				<?php echo $this->html('settings.toggle', 'groups.enabled', 'COM_EASYSOCIAL_GROUPS_SETTINGS_ENABLE_GROUPS'); ?>
				<?php echo $this->html('settings.toggle', 'groups.invite.nonfriends', 'COM_EASYSOCIAL_SETTINGS_ALLOW_INVITE_NON_FRIENDS'); ?>
				<?php echo $this->html('settings.toggle', 'groups.sharing.showprivate', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_SOCIAL_SHARING_PRIVATE'); ?>
				<?php echo $this->html('settings.toggle', 'groups.tag.nonfriends', 'COM_ES_GROUPS_SETTINGS_TAG_NONFRIENDS'); ?>
				<?php echo $this->html('settings.toggle', 'groups.verification.enabled', 'COM_ES_GROUPS_ENABLE_VERIFIED_PAGES'); ?>
				<?php echo $this->html('settings.toggle', 'groups.verification.requests', 'COM_ES_USERS_ALLOW_VERIFICATION_REQUESTS'); ?>
				<?php echo $this->html('settings.toggle', 'groups.editmoderation', 'COM_ES_GROUPS_MODERATE_EDITED_GROUPS'); ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GROUPS_SETTINGS_NEARBY_GROUPS_RADIUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'groups.nearby.radius', $this->config->get('groups.nearby.radius'), ES::getNearbyRadiusOptions()); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_DIGEST_SETTINGS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GROUP_MEMBERS_DEFAULT_DIGEST_SETTINGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'groups.digest.default', $this->config->get('groups.digest.default'), array(
								array('value' => SOCIAL_DIGEST_DEFAULT, 'text' => 'COM_ES_DIGEST_DEFAULT'),
								array('value' => SOCIAL_DIGEST_DAILY, 'text' => 'COM_ES_DIGEST_DAILY'),
								array('value' => SOCIAL_DIGEST_WEEKLY, 'text' => 'COM_ES_DIGEST_WEEKLY'),
								array('value' => SOCIAL_DIGEST_MONTHLY, 'text' => 'COM_ES_DIGEST_MONTHLY')
						)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_GROUPS_ACTIVITIES'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GROUPS_MEMBER_LEAVE_BEHAVIOR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'groups.activities.removedMember', $this->config->get('groups.activities.removedMember'), array(
									array('value' => 'remove', 'text' => 'COM_ES_DELETE_ACTIVITIES'),
									array('value' => 'keep', 'text' => 'COM_ES_KEEP_ACTIVITIES')
								)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'groups.feed.includeadmin', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_FEED_INCLUD_ADMIN'); ?>
			</div>
		</div>
	</div>
</div>
