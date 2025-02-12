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
?>
<div class="es-cards__item"
	data-album-item="<?php echo $album->uuid(); ?>"
	data-album-id="<?php echo $album->id; ?>"
	data-album-nextstart="<?php echo isset($nextStart) ? $nextStart : '-1' ; ?>"
	data-album-uid="<?php echo $lib->uid;?>"
	data-album-type="<?php echo $lib->type;?>"
>
	<div class="es-card es-card--album-item<?php echo !$photos ? ' is-empty' : ''; ?><?php echo (($hasPassword && !$verifyPassword) && !$isMine) ? ' is-locked' : ''; ?>">
		<?php if (($hasPassword && !$verifyPassword) && !$isMine) { ?>
			<div class="es-card__hd">
				<span class="embed-responsive embed-responsive-16by9 es-card__cover-lock-icon">
					<div class="embed-responsive-item embed-responsive-item--slide1" style="">
					</div>
				</span>
			</div>
		<?php } else { ?>
			<div class="es-card__hd">
				<?php echo $this->includeTemplate('site/albums/items/cover', array('album' => $album, 'photos' => $photos)); ?>
			</div>
		<?php } ?>
		<div class="es-card__bd es-card--border">
			<div class="es-card__title">
				<a href="<?php echo $album->getPermalink();?>" data-album-view-button><?php echo $album->_('title'); ?></a>
			</div>
			<?php if (($hasPassword && !$verifyPassword) && !$isMine) {?>
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
			<?php } ?>

			<div class="es-card__meta">
				<ol class="g-list--horizontal">
					<li class="g-list__item">
						<?php echo $this->html('html.user', $album->user_id); ?>
					</li>

					<?php if ($album->hasDate()) { ?>
					<li class="t-lg-p--sm">&bull;</li>
					<li class="g-list__item">
						<?php echo $this->html('string.date', $album->getAssignedDate(), "COM_EASYSOCIAL_ALBUMS_DATE_FORMAT"); ?>
					</li>
					<?php } ?>
				</ol>
			</div>

			<?php if ($album->getLocation() && $this->config->get('photos.location')) { ?>
				<?php $location = $album->getLocation(); ?>
				<div class="es-card__meta">
					<?php echo $this->html('html.location', $album->getLocationLib());?>
				</div>
				<?php echo $this->html('html.map', $album->getLocationLib());?>
			<?php } ?>
		</div>
		<div class="es-card__ft es-card--border">
			<div class="t-lg-pull-left">
				<ul class="g-list-inline">
					<li class="t-lg-mr--lg">
						<i class="fa fa-heart"></i>&nbsp; <?php echo $album->getLikesCount();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="fa fa-comment"></i>&nbsp; <?php echo $album->getCommentsCount();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="far fa-image"></i>&nbsp; <?php echo $album->getTotalPhotos();?>
					</li>
					<li class="t-lg-mr--lg">
						<i class="fa fa-eye"></i>&nbsp; <?php echo $album->hits;?>
					</li>
				</ul>
			</div>
		</div>
	</div>

</div>
