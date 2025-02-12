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
<div class="es-card__avatar <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'es-card__avatar--rounded' : '';?> <?php echo $alignment == 'center' ? 'es-card__avatar--center' : '';?>">
	<div class="o-avatar-v2 o-avatar-v2--lg <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? ' o-avatar-v2--rounded' : '';?><?php echo $showOnline ? $onlineStatus : ''; ?><?php echo ($showOnline && $isOnlineMobile) ? ' is-mobile': ''; ?>"
	>
		<div class="o-avatar-v2__mobile"></div>
		<a class="o-avatar-v2__content" href="<?php echo $permalink;?>">
			<img src="<?php echo $avatarUrl; ?>" alt="<?php echo $this->html('string.escape', $title); ?>">
		</a>
	</div>
</div>
