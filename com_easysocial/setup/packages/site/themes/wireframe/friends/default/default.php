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
<?php echo $this->html('cover.user', $user, 'friends'); ?>

<div class="es-container" data-es-friends-wrapper data-userid="<?php echo $user->id;?>" data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/friends/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content" data-wrapper>
		<?php echo $this->render('module', 'es-friends-before-contents'); ?>

		<?php echo $this->html('listing.loader', 'listing', 4, 2, array('snackbar' => true)); ?>

		<div data-contents>
		<?php if ($filter == 'invites') { ?>
			<?php echo $this->includeTemplate('site/friends/default/invites', array('user' => $user, 'pagination' => $pagination)); ?>
		<?php } else { ?>
			<?php echo $this->includeTemplate('site/friends/default/items', array('user' => $user, 'pagination' => $pagination)); ?>
		<?php } ?>
		</div>

		<?php echo $this->render('module', 'es-friends-after-contents'); ?>
	</div>
</div>
