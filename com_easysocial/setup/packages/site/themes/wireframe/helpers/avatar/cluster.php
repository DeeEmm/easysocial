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
<div class="o-avatar-v2 <?php echo $class;?> <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? ' o-avatar-v2--rounded' : '';?>">
	<div class="o-avatar-v2__mobile"></div>
	
	<?php if ($anchorLink) { ?>
	<a class="o-avatar-v2__content" 
		href="<?php echo $permalink;?>"
		data-page-id="<?php echo $cluster->id;?>"
		data-id="<?php echo $cluster->id;?>"
		data-type="<?php echo $cluster->getTypePlural();?>"
		<?php if ($popbox) { ?>
		data-popbox="module://easysocial/cluster/popbox"
		<?php } ?>
	>
	<?php } else { ?>
	<div class="o-avatar-v2__content">
	<?php } ?>
		<img src="<?php echo $cluster->getAvatar();?>" alt="<?php echo $this->html('string.escape' , $cluster->getName() );?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
	<?php if ($anchorLink) { ?>
	</a>
	<?php } else { ?>
	</div>
	<?php } ?>
</div>