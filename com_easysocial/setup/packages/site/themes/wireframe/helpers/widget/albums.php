<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($albums) { ?>
	<div class="es-side-albums-list">
		<?php foreach ($albums as $album) { ?>
		<div class="mod-es-albums-item <?php echo !$album->photos ? 'is-empty' : '';?>">
			<?php if (($album->hasPassword() && !ES::albums($album->uid, $album->type, $album->id)->verifyPassword()) && !$album->isMine()) { ?>
				<div class="es-card__hd es-card es-card--album-item is-locked">
					<span class="embed-responsive embed-responsive-16by9 es-card__cover-lock-icon">
						<div class="embed-responsive-item embed-responsive-item--slide1" style="">
						</div>
					</span>
				</div>
				<div class="mod-es-title">
					<a href="<?php echo $album->getPermalink(); ?>"><b><?php echo $this->html('string.escape', $album->get('title'));?></b></a>
				</div>
				<div>
					<div class="t-lg-mb--sm t-font-weight--bold">
						<i class="fas fa-lock t-text--muted"></i>
						<?php echo JText::_('COM_ES_PRIVATE_ALBUM'); ?>
					</div>
					<div class="t-lg-mb--md">
						<?php echo JText::_('COM_ES_ENTER_ALBUM_PASSWORD_DESCRIPTION'); ?>
					</div>
					<form class="" method="POST" action="<?php echo JRoute::_('index.php');?>">
						<div class="">
							<div class="form-inline">
								<div class="o-input-group">
									<input type="password" class="o-form-control" name="albumpassword_<?php echo $album->id; ?>" id="albumpassword_<?php echo $album->id; ?>"placeholder="<?php echo JText::_('COM_ES_ENTER_ALBUM_PASSWORD_PLACEHOLDER', true);?>">
									<span class="o-input-group__btn">
										<button class="btn btn-es-default">
											<?php echo JText::_('COM_ES_VIEW_PRIVATE_ALBUM');?>
										</button>
									</span>
								</div>

								<input type="hidden" name="option" value="com_easysocial" />
								<input type="hidden" name="controller" value="albums" />
								<input type="hidden" name="task" value="authorize" />
								<input type="hidden" name="id" value="<?php echo $album->id; ?>" />
								<input type="hidden" name="view" value="albums">
								<?php echo $this->html('form.token'); ?>
							</div>
						</div>
					</form>
				</div>
			<?php } else { ?>
				<?php if ($album->photos) { ?>
					<div class="es-photos photos-<?php echo $album->totalPhotos; ?> pattern-tile"
					data-es-photo-group="<?php echo isset($album) && !empty($album) ? 'album:' . $album->id : ''; ?>">
						<?php foreach ($album->photos as $photo) { ?>
						<div class="es-photo ar-16x9">
							<a title="<?php echo $this->html('string.escape', $photo->title); ?>"
							   data-es-photo="<?php echo $photo->id;?>"
							   href="<?php echo $photo->getPermalink();?>"
							   class="fit-width">
								<u><b>
								<img alt="<?php echo $this->html('string.escape', $photo->title);?>"
									src="<?php echo $photo->getSource();?>">
								</b></u>
							</a>
						</div>
						<?php } ?>
					</div>
				<?php } ?>
				<div class="o-empty o-empty--height-no o-empty--bg-no">
					<div class="">
						<i class="o-empty__icon fa fa-picture-o"></i>
						<div class="o-empty__text"><?php echo JText::_('COM_EASYSOCIAL_NO_PHOTOS_AVAILABLE'); ?></div>
					</div>
				</div>
				<div class="mod-es-title">
					<a href="<?php echo $album->getPermalink(); ?>"><b><?php echo $this->html('string.escape', $album->get('title'));?></b></a>
				</div>
				<div class="mod-es-meta">
					<?php echo JText::sprintf('COM_EASYSOCIAL_WIDGETS_ALBUMS_BY', $this->html('html.user', $album->getCreator())); ?>
				</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
<?php } else { ?>
<div class="t-text--muted">
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>
