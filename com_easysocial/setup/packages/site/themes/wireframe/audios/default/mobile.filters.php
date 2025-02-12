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
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', $activePlaylist || $activeGenre ? false : true, 'far fa-compass'); ?>
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_AUDIO_PLAYLISTS', 'playlist', $activePlaylist ? true : false, 'fa fa-headphones'); ?>

						<?php if ($canCreateFilter && $browseView) { ?>
							<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_CUSTOM_FILTERS', 'hashtags', ($filter == 'customFilter' && $activeCustomFilter), 'fa fa-hashtag'); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_ES_AUDIO_GENRES', 'genres', $activeGenre ? true : false, 'fa fa-drum', true); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($allowCreation) { ?>
			<?php
			$filterActions = array();
			$filterActions[] = $this->html('mobile.filterAction', 'COM_ES_AUDIO_ADD_AUDIO', $createLink);

			if ($canCreatePlaylist) {
				$filterActions[] = $this->html('mobile.filterAction', 'COM_ES_AUDIO_NEW_PLAYLIST', ESR::audios(array('layout' => 'playlistform')));
			}

			if ($canCreateFilter) {
				$filterActions[] = $this->html('mobile.filterAction', 'COM_ES_NEW_FILTER', 'javascript:void(0);', array('data-audio-create-filter', 'data-type="audios"', 'data-uid="' . $uid . '"', 'data-cluster-type="' . $type . '"'));
			}
			?>
			<?php echo $this->html('mobile.filterActions', $filterActions); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-audio-filters>
		<div class="es-mobile-filter__group <?php echo $activePlaylist || $activeGenre ? '' : 'is-active';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterTab', 'COM_ES_AUDIO_FILTERS_ALL_AUDIOS', $adapter->getAllAudiosLink(), ($filter == '' || $filter == 'all'),
							array('data-filter-item', 'data-type="all"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_ES_AUDIO_FILTERS_FEATURED_AUDIOS', $adapter->getAllAudiosLink('featured'), ($filter == 'featured'),
							array('data-filter-item', 'data-type="featured"')); ?>

						<?php if ($showMyAudios) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_AUDIO_FILTERS_MY_AUDIOS', ESR::audios(array('filter' => 'mine')), ($filter == 'mine'),
								array('data-filter-item', 'data-type="mine"')); ?>
						<?php } ?>

						<?php if ($showPendingAudios) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_AUDIO_FILTERS_PENDING_AUDIOS', $adapter->getAllAudiosLink('pending'), ($filter == 'pending'),
								array('data-filter-item', 'data-type="pending"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($customFilters) { ?>
		<div class="es-mobile-filter__group <?php echo $filter == 'customFilter' && $activeCustomFilter ? 'is-active' : '';?>" data-es-swiper-group data-type="hashtags">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($customFilters as $customFilter) { ?>
						<?php echo $this->html('mobile.filterTab', '#' . $customFilter->title, $customFilter->permalink, ($filter == 'customFilter' && $activeCustomFilter && $activeCustomFilter->id == $customFilter->id),
							array('data-filter-item', 'data-type="hashtag" data-tag-id="' . $customFilter->id . '"'), array('title="' . $customFilter->title . '"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="genres">
			<div class="dl-menu-wrapper t-hidden" data-es-audios-genres>
				<div class="es-list">
				<?php if ($genres) { ?>
					<?php foreach ($genres as $genre) { ?>
					<div class="es-list__item">
						<div class="es-list-item__context">
							<div class="es-list-item__hd">
								<div class="es-list-item__content">
									<div class="es-list-item__title">
										<a href="<?php echo $genre->getPermalink(true, $uid, $type);?>">
											<?php echo JText::_($genre->title);?>
										</a>
										<?php if ($displayCounters) { ?>
										<span class="t-text--muted t-font-weight--normal" data-counter="<?php echo $genre->getTotalAudios($cluster, $uid, $type);?>">
											(<?php echo $genre->getTotalAudios($cluster, $uid, $type);?>)
										</span>
										<?php } ?>
									</div>
									<div class="es-list-item__meta">
										<?php echo $this->html('string.truncate', $genre->description, 80, '', false, false, false, true);?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				<?php } ?>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group <?php echo $activePlaylist ? 'is-active' : '';?>" data-es-sly-group data-es-swiper-group data-type="playlist">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php if ($lists) { ?>
							<?php foreach ($lists as $list) { ?>
								<?php echo $this->html('mobile.filterTab', $list->get('title'), $adapter->getPlaylistLink($list->id), $activePlaylist && $activePlaylist->id == $list->id,
									array('data-filter-item data-type="list"', 'data-id="' . $list->id . '"')); ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
