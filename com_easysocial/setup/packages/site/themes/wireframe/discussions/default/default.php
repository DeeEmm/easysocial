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
<div class="es-container" data-es-discussions data-es-container data-id="<?php echo $cluster->id;?>">

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/discussions/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-discussions-before-contents'); ?>

		<div data-contents>
			<?php echo $this->html('listing.loader', 'listing', 8, 1); ?>

			<?php echo $this->includeTemplate('site/discussions/default/wrapper'); ?>
		</div>

		<?php echo $this->render('module', 'es-discussions-after-contents'); ?>
	</div>
</div>
