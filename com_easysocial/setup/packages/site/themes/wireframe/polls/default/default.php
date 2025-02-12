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
<?php if ($user) { ?>
	<?php echo $this->html('cover.user', $user, 'polls'); ?>
<?php } ?>

<div class="es-container" data-es-polls data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/polls/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-polls-before-contents'); ?>

		<div data-contents>
			<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true)); ?>

			<?php echo $this->includeTemplate('site/polls/default/wrapper'); ?>
		</div>

		<?php echo $this->render('module', 'es-polls-after-contents'); ?>
	</div>
</div>
