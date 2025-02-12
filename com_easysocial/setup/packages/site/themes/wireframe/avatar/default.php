<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="o-avatar-v2 o-avatar-v2--md">
	<div class="o-avatar-v2__mobile"></div>
	<a href="<?php echo $permalink;?>" class="o-avatar-v2__content" 
		data-popbox="module://easysocial/profile/popbox"
		data-user-id="<?php echo $user->id;?>"
	>
		<img alt="<?php echo $this->html('string.escape', $user->getName());?>" src="<?php echo $user->getAvatar();?>" width="64" height="64" />
	</a>
</div>