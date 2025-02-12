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
<li>
	<div class="es-card">
		<div class="es-card__bd">
			<div class="o-flag" data-behavior="sample_code">
				<div class="o-flag__image o-flag--top t-lg-pr--lg">
					<?php if ($this->config->get('registrations.layout.avatar')) { ?>
						<?php echo $this->html('avatar.mini', $profile->getTitle(), 'javascript:void(0);', $profile->getAvatar(SOCIAL_AVATAR_LARGE), 'lg', '', array('data-oauth-profile-submit', 'data-id="' . $profile->id . '"')); ?>
					<?php } ?>
				</div>
				<div class="o-flag__body">
					<b class=" t-mb--sm"><a href="javascript:void(0);"><?php echo $profile->get('title');?></a></b>
					<div class=" t-mb--sm"><?php echo $profile->get('description');?></div>

					<?php if ($profile->getRegistrationType(false, true) == 'approvals' || $profile->getRegistrationType(false, true) == 'verify' || $profile->getRegistrationType(false, true) == 'confirmation_approval') { ?>
						<div class="">* <?php echo $profile->getRegistrationType(SOCIAL_TRANSLATE_REGISTRATION, true); ?></div>
					<?php } ?>

					<?php if ($profile->getMembersCount() && $this->config->get('registrations.layout.users')) { ?>
					<div class="profile-members">
						<div class="">
							<hr />
							<div class="list-profile-type-peep t-fs--sm">
								<?php echo JText::sprintf( 'COM_EASYSOCIAL_REGISTRATIONS_OTHER_PROFILE_MEMBERS' , $profile->getMembersCount() );?>
							</div>

							<?php if( $profile->users ){ ?>
							<ul class="g-list-inline profile-users">
								<?php foreach( $profile->users as $user ){ ?>
								<li data-es-provide="tooltip" data-original-title="<?php echo $this->html( 'string.escape' , $user->getName() );?>" class="t-lg-mb--md t-lg-mr--md">
									<?php echo $this->html('avatar.mini', $user->getName(), $user->getPermalink(), $user->getAvatar(SOCIAL_AVATAR_SMALL), 'sm', '', 'pull-left'); ?>
								</li>
								<?php } ?>
							</ul>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="es-card__ft es-card--border">
			<button class="btn btn-es-primary btn-medium pull-right" data-oauth-profile-submit data-id="<?php echo $profile->id;?>"><?php echo JText::_( 'COM_EASYSOCIAL_SELECT_THIS_PROFILE_BUTTON' ); ?></button>
		</div>
	</div>
</li>
