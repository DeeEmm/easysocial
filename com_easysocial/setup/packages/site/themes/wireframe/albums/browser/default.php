<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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

	<?php echo $lib->heading();?>

	<?php if ($this->isMobile() && $cluster) { ?>
	<a class="btn btn-es-default-o btn-sm t-lg-mb--lg" href="<?php echo $cluster->getPermalink();?>">
		&larr; <?php echo JText::sprintf('COM_EASYSOCIAL_BACK_TO_' . strtoupper($cluster->getType()));?>
	</a>
	<?php } ?>

	<div class="es-container es-media-browser layout-album" data-layout="album" data-album-browser="<?php echo $uuid; ?>" data-es-container>
		<?php echo $this->html('html.loading'); ?>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/albums/browser/mobile.filters'); ?>
		<?php } ?>

		<div class="es-content" data-wrapper>
			<?php echo $this->html('listing.loader', 'card', 2, 4, array('snackbar' => true, 'sortbar' => true, 'pictureOnly' => true)); ?>

			<?php echo $this->render('module', 'es-albums-before-contents'); ?>

			<div class="es-album-browser-content" data-album-browser-content>
				<?php echo $content; ?>
			</div>

			<?php echo $this->render('module', 'es-albums-after-contents'); ?>
		</div>
	</div>
</div>
