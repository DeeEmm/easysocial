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
<div class="es-container" data-es-users data-es-container>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/users/default/mobile.filters');?>
	<?php } ?>

	<?php echo $this->html('html.sidebar'); ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-users-before-contents'); ?>

		<div data-contents>
			<?php echo $this->html('listing.loader', 'listing', 8, 1, array('sortbar' => true, 'snackbar' => true)); ?>

			<?php echo $this->includeTemplate('site/users/default/wrapper'); ?>
		</div>

		<?php echo $this->render('module', 'es-users-after-contents'); ?>
	</div>
</div>
