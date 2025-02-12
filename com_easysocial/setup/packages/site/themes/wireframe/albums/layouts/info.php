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
<div data-album-info>
	<div class="es-media-info es-album-info">
		<h1 data-album-title class="es-media-title es-album-title">
			<a href="<?php echo $album->getPermalink();?>" data-album-view-button><?php echo $album->_('title'); ?></a>
		</h1>
		<?php if ($options['view'] != 'all') { ?>
		<div data-album-caption class="es-media-caption es-album-caption">
			<?php echo $this->html('string.truncater', nl2br($album->_('caption')), 250); ?>
		</div>
		<?php } ?>

		<small>
			<?php if ($options['view'] == 'all') { ?>
			<span class="es-album-author mr-5">
				<i class="fa fa-user"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_ALBUMS_CREATED_BY', $this->html('html.user', $album->user_id, true)); ?>
			</span>
			<?php } ?>

			<?php if ($album->hasDate()) { ?>
			<span data-album-date class="es-album-date"><i class="fa fa-calendar"></i> <?php echo $this->html('string.date', $album->getAssignedDate(), "COM_EASYSOCIAL_ALBUMS_DATE_FORMAT"); ?></span>
			<?php } ?>

			<?php if ($album->location && $this->config->get('photos.location')) { ?>
			<span data-album-location class="es-album-location">
				<?php echo JText::_("COM_EASYSOCIAL_ALBUMS_TAKEN_AT"); ?>
				<?php echo $this->html('html.location', $album->getLocationLib());?>
			</span>
			<?php } ?>
		</small>
	</div>
	<?php if ($album->location && $this->config->get('photos.location')) { ?>
		<?php echo $this->html('html.map', $album->getLocationLib());?>
	<?php } ?>
</div>
