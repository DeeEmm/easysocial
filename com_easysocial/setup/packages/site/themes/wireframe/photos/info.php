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
<div data-photo-info>
	<div class="es-media-info es-photo-info">
		<div data-photo-title class="es-media-title es-photo-title">
			<a data-photo-title-link href="<?php echo $photo->getPermalink();?>"><?php echo $this->html('string.escape', $photo->get('title')); ?></a>
		</div>

		<div data-photo-caption class="es-media-caption es-photo-caption">
			<?php echo $this->html('string.truncater', nl2br($photo->get('caption')), 250); ?>
			<span data-photoinfo-tag-list-item-group>
			<?php if (!empty($tags) && $this->config->get('photos.tagging')) { ?>
				<br />
				<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_IN_THIS_PHOTO"); ?>
				&mdash;
				<?php $totalTags = count($tags); ?>
				<?php for ($i = 0; $i < $totalTags; $i++) { ?>
					<?php echo $this->includeTemplate('site/photos/info.taglist.item', array('tag' => $tags[$i], 'comma' => ($i !== 0))); ?>
				<?php } ?>
			<?php } ?>
			</span>
		</div>

		<small>
			<span data-photo-date class="es-photo-date">
				<?php echo $this->html('string.date', $photo->getAssignedDate(), 'COM_EASYSOCIAL_PHOTOS_DATE_FORMAT', $photo->hasAssignedDate() ? false : true); ?>
			</span>
			<span class="es-time-lapse">
				<b>·</b>
				<span>
				<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($photo->created)->toLapsed(); ?>
				</span>
			</span>
			<?php if ($photo->getLocation() && $this->config->get('photos.location')) { ?>
				<span data-photo-location class="es-photo-location">
					<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_TAKEN_AT"); ?>
					<?php echo $this->html('html.location', $photo->getLocationLib());?>
				</span>
			<?php } ?>
		</small>
	</div>
	<?php if ($photo->getLocation() && $this->config->get('photos.location')) { ?>
		<?php echo $this->html('html.map', $photo->getLocationLib());?>
	<?php } ?>
</div>
