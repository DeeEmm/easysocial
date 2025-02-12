<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-album-taglist">
	<div><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_IN_THIS_ALBUM');?></div>

	<?php if ( empty($tags) ) { ?>
		<span><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_NO_TAGGED_PERSON'); ?></span>
	<?php } else { ?>
	<ul class="g-list-inline">
		<?php foreach ($tags as $tag) { ?>
			<?php $user = ES::user($tag->uid); ?>

			<?php if (!$user->isBlock()) { ?>
			<li>
				<?php echo $this->html('avatar.user', $user, 'sm'); ?>
			</li>
			<?php } ?>

		<?php } ?>
	</ul>
	<?php } ?>
</div>
