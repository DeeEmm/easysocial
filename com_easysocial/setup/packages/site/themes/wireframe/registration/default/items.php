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
<div class="o-box t-lg-mb--lg">
	<div class="">
		<div class="o-media o-media--top">
			<?php if ($this->config->get('registrations.layout.avatar')) { ?>
			<div class="o-media__image">
				<?php echo $this->html('avatar.mini', $profile->getTitle(), '', $profile->getAvatar(SOCIAL_AVATAR_LARGE), 'lg', '', '', false); ?>
			</div>
			<?php } ?>

			<div class="o-media__body">
				<div>
					<a href="<?php echo ESR::registration(array('controller' => 'registration', 'task' => 'selectType' , 'profile_id' => $profile->id));?>">
						<b><?php echo $profile->get('title');?></b>
					</a>
				</div>
				<div>
					<?php echo $profile->get('description');?>
				</div>
				<?php if ($profile->getRegistrationType() == 'approvals' || $profile->getRegistrationType() == 'verify' || $profile->getRegistrationType() == 'confirmation_approval') { ?>
				<hr />
				<div>* <?php echo $profile->getRegistrationType(SOCIAL_TRANSLATE_REGISTRATION); ?></div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="o-box--border">
		<div class="o-grid o-grid--center">
			<?php if ($profile->getMembersCount() && $this->config->get('registrations.layout.users')) { ?>
			<div class="o-grid__cell">
				<div class="t-fs--sm">
					<?php echo JText::sprintf('COM_EASYSOCIAL_REGISTRATIONS_OTHER_PROFILE_MEMBERS', $profile->getMembersCount());?>
				</div>
				<div class="es-avatar-list">
					<?php foreach ($profile->users as $user) { ?>
					<div class="o-avatar-list__item t-lg-mb--no">
						<?php echo $this->html('avatar.user', $user, 'sm', true, false); ?>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>

			<div class="o-grid__cell o-grid__cell-right">
				<a href="<?php echo ESR::registration(array('controller' => 'registration' , 'task' => 'selectType' , 'profile_id' => $profile->id));?>" class="btn btn-es-primary">
					<?php echo JText::_('COM_EASYSOCIAL_JOIN_NOW_BUTTON'); ?>
				</a>
			</div>
		</div>
	</div>
</div>
