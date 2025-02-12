<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$hasCover = $album->hasCover(true);

?>
<?php if ($hasCover || $photos) { ?>

<?php
	$sliderClass = '';
	if (count($photos) >= 3) {
		$sliderClass = ' embed-responsive--hover-slider3';
	} else if (count($photos) == 2) {
		$sliderClass = ' embed-responsive--hover-slider2';
	}
?>

<div data-es-photo-group="album:<?php echo $album->id; ?>">
	<a href="javascript:void(0);"
		<?php if ($hasCover) { ?>
			data-es-photo="<?php echo $album->getCoverObject()->id; ?>"
		<?php } else if ($photos) { ?>
			data-es-photo="<?php echo $photos[0]->id; ?>"
		<?php } ?>
		class="embed-responsive embed-responsive-16by9<?php echo $sliderClass; ?><?php echo ($photos) ? ' es-card__cover-popup-btn' : ''; ?>">
			<?php $counter = 1; ?>
			<?php if ($hasCover) { ?>
				<div class="embed-responsive-item embed-responsive-item--slide<?php echo $counter++; ?>" style="background-image: url(<?php echo $album->getCover('large'); ?>);">
				</div>
			<?php } ?>

			<?php foreach($photos as $photo) { ?>
				<?php
					if ($photo->id == $album->cover_id) {
						continue;
					}

					if ($counter > 3) {
						continue;
					}
				?>
				<div class="embed-responsive-item embed-responsive-item--slide<?php echo $counter++; ?>" style="background-image: url(<?php echo $photo->getSource('large'); ?>);"></div>
			<?php } ?>
	</a>
</div>
<?php } else { ?>
<div class="embed-responsive embed-responsive-16by9">
	<div class="o-empty">
		<div class="o-empty__content">
			<i class="o-empty__icon far fa-images"></i>
			<div class="o-empty__text"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_ALBUM_IS_EMPTY'); ?></div>
			<div class="o-empty__action">
				<a href="<?php echo $album->getPermalink();?>" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_ADD_PHOTO'); ?></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

