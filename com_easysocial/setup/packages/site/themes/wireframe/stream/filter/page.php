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
<div class="es-stream-filter-bar t-lg-mb--lg">
	<?php if ($hashtag) { ?>
		<div class="es-stream-filter-bar__cell">
			<div class="o-media">
				<div class="o-media__image">
					<div>
						<?php echo JText::sprintf('COM_EASYSOCIAL_STREAM_HASHTAG_CURRENTLY_FILTERING', '<a href="' . ESR::pages(array('layout' => 'item', 'id' => $cluster->getAlias(), 'tag' => $hashtagAlias)) . '">#' . $hashtag . '</a>'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($canCreateFilter) { ?>
		<div class="es-stream-filter-bar__cell">
			<a href="javascript:void(0);" data-hashtag-filter-save data-tag="<?php echo $hashtag;?>" class="btn btn-es-default-o btn-sm">
				<?php echo JText::_('COM_ES_CREATE_NEW_FILTER');?>
			</a>
		</div>
		<?php } ?>
	<?php } ?>

	<?php if (!$hashtag) { ?>
	<div class="es-stream-filter-bar__cell">
		 <div class="o-media">
			<div class="o-media__image">
				<?php echo JText::_('COM_ES_FILTER_TIMELINE');?>:
			</div>
			<div class="o-media__body">
				<div class="o-btn-group" data-filter-wrapper>
					<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_ is-loading" data-es-toggle="dropdown" data-active-filter-button>
						<div class="o-loader o-loader--sm"></div>
						<span data-active-filter-text></span> &nbsp;<i class="fa fa-caret-down"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-left es-timeline-filter-dropdown">
						<li>
							<span class="es-timeline-filter-dropdown__title"><?php echo JText::_('COM_ES_NEWSFEEDS');?></span>
						</li>

						<li class="<?php echo $active == 'timeline' || !$active ? ' active' : '';?>" data-filter-item data-type="feeds" data-id="<?php echo $cluster->id;?>">
							<a href="<?php echo ESR::pages(array('layout' => 'item', 'id' => $cluster->getAlias(), 'type' => 'timeline')); ?>">
								<span data-filter-text><?php echo JText::_('COM_EASYSOCIAL_PAGE_TIMELINE');?></span>
							</a>
							<div class="es-timeline-filter-dropdown__bubble" data-counter>0</div>
						</li>

						<?php if (ES::user()->canPostScheduledStory(SOCIAL_TYPE_PAGE) && ($cluster->isAdmin() || $cluster->isOwner())) { ?>
						<li class="<?php echo $active == 'scheduled' ? ' active' : '';?>" data-filter-item data-type="scheduled" data-id="<?php echo $cluster->id;?>">
							<a href="<?php echo ESR::pages(array('layout' => 'item', 'id' => $cluster->getAlias(), 'type' => 'scheduled')); ?>">
								<span data-filter-text><?php echo JText::_('COM_ES_STORY_SCHEDULED'); ?></span>
							</a>
						</li>
						<?php } ?>

						<?php if (($cluster->isAdmin() || $cluster->isOwner() || $this->my->isSiteAdmin())) { ?>
						<li class="divider"></li>
						<li>
							<span class="es-timeline-filter-dropdown__title"><?php echo JText::_('COM_ES_PAGE_SIDEBAR_MODERATE_POSTS'); ?></span>
						</li>
						<li class="<?php echo $active == 'moderation' ? ' active' : '';?> <?php echo $cluster->getTotalPendingPosts() ? 'has-bubble' : '' ?>" data-filter-item data-type="moderation">
							<a href="<?php echo ESR::pages(array('layout' => 'item', 'id' => $cluster->getAlias(), 'type' => 'moderation'));?>">
								<span data-filter-text><?php echo JText::_('COM_EASYSOCIAL_PAGE_SIDEBAR_PENDING_POSTS'); ?></span>
							</a>
							<div class="es-timeline-filter-dropdown__bubble" data-counter><?php echo $cluster->getTotalPendingPosts(); ?></div>
						</li>
						<?php } ?>

						<?php if ($customFilters) { ?>
							<li class="divider"></li>
							<li>
								<span class="es-timeline-filter-dropdown__title"><?php echo JText::_('COM_ES_CUSTOM_FILTERS');?></span>
							</li>

							<?php if ($customFilters) { ?>
								<?php foreach ($customFilters as $customFilter) { ?>
								<li class="<?php echo $customFilter->id == $activeFilterId ? ' active' : '';?>" class="o-tabs__item" data-filter-item data-type="filters" data-id="<?php echo $customFilter->id; ?>">
									<a href="<?php echo $customFilter->permalink;?>">
										<span data-filter-text><?php echo $customFilter->_('title'); ?></span>
									</a>
								</li>
								<?php } ?>
							<?php } ?>
						<?php } ?>

						<?php if ($canCreateFilter) { ?>
						<li class="divider"></li>
						<li>
							<a href="javascript:void(0);" data-filter-create data-type="<?php echo SOCIAL_TYPE_PAGE;?>">
								<?php echo JText::_('COM_ES_CREATE_NEW_FILTER');?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="es-stream-filter-bar__cell">
		<?php if ($appFilters) { ?>
			<div class="o-btn-group">
				<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
					<i class="es-stream-filter-icon"><i></i></i>&nbsp; <?php echo JText::_('COM_ES_POST_TYPES_TITLE');?> &nbsp;<i class="fa fa-caret-down"></i>
				</button>

				<div class="dropdown-menu dropdown-menu-right es-stream-filter-dropdown" data-filter-post-type-wrapper>
					<div>
						<span class="es-stream-filter-dropdown__title"><?php echo JText::_('COM_ES_FILTER_POSTS_DROPDOWN_TITLE');?></span>
						<p class="es-stream-filter-dropdown__desc"><?php echo JText::_('COM_ES_FILTER_POSTS_DROPDOWN_INFO');?></p>
					</div>
					<ul class="es-stream-filter-dropdown__list">
					<?php foreach ($appFilters as $appFilter) { ?>
						<li class="es-stream-filter-dropdown__item">
							<div class="o-checkbox">
								<input id="post-type-<?php echo $appFilter->alias;?>" name="postTypes[]" value="<?php echo $appFilter->alias;?>" autocomplete="off" type="checkbox" data-filter-post-type />
								<label for="post-type-<?php echo $appFilter->alias;?>" data-filter-post-type-label><?php echo $appFilter->title;?></label>
							</div>
						</li>
					<?php } ?>
					</ul>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>
