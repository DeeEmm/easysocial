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
					<?php echo $this->html('avatar.mini', $profile->getTitle(), ESR::profile(array('layout' => 'switchProfileEdit', 'profile_id' => $profile->id)), $profile->getAvatar(SOCIAL_AVATAR_LARGE), 'lg'); ?>
					<?php } ?>
				</div>
				<div class="o-flag__body">
					<b class=" t-mb--sm"><a href="<?php echo ESR::profile(array('layout' => 'switchProfileEdit', 'profile_id' => $profile->id));?>"><?php echo $profile->get('title');?></a></b>
					<div class=" t-mb--sm"><?php echo $profile->get('description');?></div>
				</div>
			</div>
		</div>
		<div class="es-card__ft es-card--border">
			<a href="<?php echo ESR::profile(array('layout' => 'switchProfileEdit' , 'profile_id' => $profile->id));?>" class="btn btn-es-primary pull-right">
				<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_SWITCH_BUTTON' ); ?>
			</a>
		</div>
	</div>

</li>


