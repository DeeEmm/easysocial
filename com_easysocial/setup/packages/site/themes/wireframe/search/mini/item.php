<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-search-mini-result-list__item" data-search-item
	data-search-item-id="<?php echo $item->id; ?>"
	data-search-item-type="<?php echo $item->utype; ?>"
	data-search-item-typeid="<?php echo $item->uid; ?>"
	data-search-custom-name="<?php echo $item->title; ?>"
	data-search-custom-avatar="<?php echo $item->image; ?>"
	>

	<a href="<?php echo $item->link; ?>">
		<?php 
			$imageUri = $item->image;
			if ($item->utype == 'EasySocial.Users') {
				$imageUri = ES::user($item->uid)->getAvatar(SOCIAL_AVATAR_SMALL);
			}
		?>
		<?php echo $this->html('avatar.mini', $item->title, '', $imageUri, 'xs', ' t-lg-mr--md', '', false); ?>

		<span class="es-search-mini-result-name">
			<?php if (ESJString::strlen($item->title) > 20) { ?>
				<?php echo ESJString::substr($item->title, 0, 20); ?> <?php echo JText::_('COM_EASYSOCIAL_ELLIPSES'); ?>
			<?php } else { ?>
				<?php echo $item->title; ?>
			<?php } ?>
		</span>
	</a>
</div>
