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

$isOnline = '';
$isMobile = '';

if ($showOnlineState) {
	$isOnline = $user->isOnline() ? ' is-online' : ' is-offline';
	$isMobile = $user->isOnlineMobile() ? ' is-mobile' : '';
}
?>
<div class="o-avatar-v2 <?php echo $customClass;?> <?php echo $class;?> <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? ' o-avatar-v2--rounded' : '';?><?php echo $isOnline; ?><?php echo $isMobile; ?>">
	<div class="o-avatar-v2__mobile"></div>

	
	<?php if ($anchorLink) { ?>
	<a class="o-avatar-v2__content" 
		href="<?php echo $user->getPermalink();?>"
		data-user-id="<?php echo $user->id;?>"
		<?php if ($showPopbox) { ?>
		data-popbox="module://easysocial/profile/popbox"
		<?php } ?>
		<?php if ($popboxPosition) { ?>
			data-popbox-position="<?php echo $popboxPosition;?>"
		<?php } ?>
	>
	<?php } else { ?>
	<div class="o-avatar-v2__content">
	<?php } ?>
		<img src="<?php echo $avatar;?>" alt="<?php echo $this->html('string.escape' , $user->getName() );?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
	<?php if ($anchorLink) { ?>
	</a>
	<?php } else { ?>
	</div>
	<?php } ?>

</div>


