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
<a href="<?php echo $user->getPermalink();?>" title="<?php echo $this->html('string.escape', $user->getName());?>" class="<?php echo $class; ?>" style="<?php echo $customStyle;?>"
	<?php if ($popbox){ ?>
	data-popbox="module://easysocial/profile/popbox"
	data-popbox-position="<?php echo $position;?>"
	data-user-id="<?php echo $user->id;?>"
	<?php } ?>
	>
	<?php if ($avatar) { ?>
		<img src="<?php echo $user->getAvatar();?>" title="<?php echo $this->html('string.escape', $user->getName());?>" alt="<?php echo $this->html('string.escape', $user->getName());?>" />
	<?php } ?>

	<?php if ($userIcon) { ?>
		<i class="fa fa-user"></i>&nbsp;
	<?php } ?>

	<?php echo $user->getName();?></a>

	<?php if ($user->isVerified()) { ?><i class="es-verified" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_VERIFIED_USER');?>"></i><?php } ?>
