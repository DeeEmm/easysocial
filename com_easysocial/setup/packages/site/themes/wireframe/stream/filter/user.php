<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
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
				<div class="o-media__body">
					<?php echo JText::sprintf('COM_EASYSOCIAL_STREAM_HASHTAG_CURRENTLY_FILTERING' , '<a href="' . ESR::dashboard(array('layout' => 'hashtag' , 'tag' => $hashtagAlias)) . '">#' . $hashtag . '</a>'); ?>
				</div>

				<div class="active t-hidden" style="display:none;" data-filter-item data-type="hashtag" data-tag="<?php echo $hashtagAlias; ?>"></div>
			</div>
		</div>

		<?php if ($this->my->id) { ?>
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

						<?php if ($this->config->get('users.dashboard.everyone')) { ?>
						<li class="<?php echo $active == 'everyone' ? ' active' : '';?>" data-filter-item data-type="everyone">
							<a href="<?php echo ESR::dashboard(array('type' => 'everyone'));?>">
								<span data-filter-text><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_NEWSFEEDS_EVERYONE');?></span>
							</a>
							<div class="es-timeline-filter-dropdown__bubble" data-counter>0</div>
						</li>
						<?php } else if ($this->config->get('users.dashboard.start') == 'everyone') { ?>
							<li class="o-tabs__item <?php echo $active == 'everyone' ? ' active' : '';?> t-hidden" data-filter-item data-type="everyone">
								<a href="<?php echo ESR::dashboard(array('type' => 'everyone'));?>"></a>
							</li>
						<?php } ?>

						<li class="<?php echo (empty($active) || $active == 'me') ? 'active' : '';?>" data-filter-item data-type="me">
							<a href="<?php echo ESR::dashboard(array('type' => 'me'));?>">
								<span data-filter-text>
									<?php if ($this->config->get('friends.enabled')) { ?>
										<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_ME_AND_FRIENDS');?>
									<?php } else {  ?>
										<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_MY_UPDATES');?>
									<?php } ?>
								</span>
							</a>
							<div class="es-timeline-filter-dropdown__bubble" data-counter>0</div>
						</li>

						<?php if ($this->config->get('followers.enabled')) { ?>
						<li class="<?php echo $active == 'following' ? ' active' : '';?>" data-filter-item data-type="following">
							<a href="<?php echo ESR::dashboard(array('type' => 'following'));?>">
								<span data-filter-text><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_FEEDS_FOLLOWING');?></span>
							</a>
						</li>
						<?php } ?>

						<?php if ($this->config->get('stream.bookmarks.enabled')) { ?>
						<li class="<?php echo $active == 'bookmarks' ? ' active' : '';?>" data-filter-item data-type="bookmarks">
							<a href="<?php echo ESR::dashboard(array('type' => 'bookmarks'));?>">
								<span data-filter-text><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_FEEDS_BOOKMARKS'); ?></span>
							</a>
						</li>
						<?php } ?>

						<?php if ($this->config->get('stream.pin.enabled')) { ?>
						<li class="<?php echo $active == 'sticky' ? ' active' : '';?>" data-filter-item data-type="sticky">
							<a href="<?php echo ESR::dashboard(array('type' => 'sticky'));?>">
								<span data-filter-text><?php echo JText::_('COM_ES_STREAM_MY_PINNED_ITEMS'); ?></span>
							</a>
						</li>
						<?php } ?>

						<?php if (ES::user()->canPostScheduledStory(SOCIAL_TYPE_USER)) { ?>
						<li class="<?php echo $active == 'scheduled' ? ' active' : '';?>" data-filter-item data-type="scheduled">
							<a href="<?php echo ESR::dashboard(array('type' => 'scheduled'));?>">
								<span data-filter-text><?php echo JText::_('COM_ES_STORY_SCHEDULED'); ?></span>
							</a>
						</li>
						<?php } ?>

						<?php if ($customFilters) { ?>
							<li class="divider"></li>
							<li>
								<span class="es-timeline-filter-dropdown__title"><?php echo JText::_('COM_ES_CUSTOM_FILTERS');?></span>
							</li>

							<?php foreach ($customFilters as $customFilter) { ?>
							<li class="<?php echo $activeFilterId == $customFilter->id ? ' active' : '';?>" data-filter-item data-type="custom" data-id="<?php echo $customFilter->id; ?>">
								<a href="<?php echo ESR::dashboard(array('type' => 'filter', 'filterid' => $customFilter->getAlias()));?>">
									<span data-filter-text><?php echo $customFilter->_('title'); ?></span>
								</a>
							</li>
							<?php } ?>
						<?php } ?>

						<?php if ($canCreateFilter) { ?>
						<li class="divider"></li>
						<li>
							<a href="javascript:void(0);" data-filter-create>
								<?php echo JText::_('COM_ES_CREATE_NEW_FILTER');?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<?php if ($appFilters) { ?>
	<div class="es-stream-filter-bar__cell">
		<div class="dropdown_">
			<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_ " data-es-toggle="dropdown" data-filter-post-type-button>
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
	</div>
	<?php } ?>

	<?php } ?>
</div>
