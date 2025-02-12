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
<?php echo $this->html('cover.user', $user, 'followers'); ?>

<div class="es-container" data-es-followers data-es-container data-id="<?php echo $user->id;?>">
	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/followers/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content" data-followers-wrapper>
		<?php echo $this->html('listing.loader', 'listing', 4, 2, array('snackbar' => true)); ?>

		<?php echo $this->render('module', 'es-followers-before-contents'); ?>

		<?php echo $this->includeTemplate('site/followers/default/items'); ?>

		<?php echo $this->render('module', 'es-followers-after-contents'); ?>
	</div>
</div>
