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
<div class="wrapper-for-full-height">
	<?php if (!isset($heading)) { ?>
		<?php echo $lib->heading(); ?>
	<?php } ?>

	<div data-photo-browser="<?php echo $uuid; ?>" data-album-id="<?php echo $album->id; ?>" class="es-container es-photo-browser es-media-browser" data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->output('site/photos/mobile'); ?>
		<?php } ?>

		<div class="es-content" data-photo-browser-wrapper>
			<?php echo $this->html('listing.loader', 'card', 1, 1, array('snackbar' => true, 'sortbar' => true)); ?>

			<?php echo $this->render('module', 'es-photos-before-contents'); ?>

			<div data-photo-browser-content>
				<?php echo $content; ?>
			</div>

			<?php echo $this->render('module', 'es-photos-after-contents'); ?>
		</div>
	</div>
</div>
