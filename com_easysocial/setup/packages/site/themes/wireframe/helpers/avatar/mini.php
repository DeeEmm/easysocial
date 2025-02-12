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
<div class="o-avatar-v2 <?php echo $avatarTextStyle; ?> <?php echo $sizeClass; ?> <?php echo $class; ?> <?php echo $avatarStyle; ?>">
	<div class="o-avatar-v2__mobile"></div>
	<?php if ($anchorLink) { ?>
		<a href="<?php echo $permalink;?>" class="o-avatar-v2__content"
			<?php if ($attribs) { ?>
				<?php echo $attribs; ?>
			<?php } else { ?>
			title="<?php echo $this->html('string.escape' , $title);?>"
			alt="<?php echo $this->html('string.escape' , $title);?>"
			<?php } ?>
		>
	<?php } else { ?>
		<div class="o-avatar-v2__content"
			<?php if ($attribs) { ?>
				<?php echo $attribs; ?>
			<?php } else { ?>
			title="<?php echo $this->html('string.escape', $title);?>"
			alt="<?php echo $this->html('string.escape', $title);?>"
			<?php } ?>
		>
	<?php }?>
			<?php if ($avatar && !$avatarText) { ?>
			<div class="embed-responsive embed-responsive-1by1 t-width--100 t-height--100">
				<div class="embed-responsive-item" data-avatar-image style="background-image: url('<?php echo $avatar;?>');"></div>
			</div>
			<?php } ?>

			<?php if (!$avatar && $avatarText) { ?>
				<?php echo $avatarText; ?>
			<?php } ?>

	<?php if ($anchorLink) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php }?>
</div>
