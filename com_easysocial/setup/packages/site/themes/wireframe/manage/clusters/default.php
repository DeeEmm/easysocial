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
<div class="es-container" data-es-cluster-wrapper data-es-container>

	<div class="es-content">
		<div class="es-stream-filter-bar t-lg-mb--lg">
			<div class="es-stream-filter-bar__cell">
				 <div class="o-media">
					<div class="o-media__image">
						<?php echo JText::_('COM_ES_FILTER');?>:
					</div>
					<div class="o-media__body">
						<div class="o-btn-group" data-filter-wrapper>
							<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_ is-loading" data-es-toggle="dropdown" data-active-filter-button>
								<div class="o-loader o-loader--sm"></div>
								<span data-active-filter-text></span> &nbsp;<i class="fa fa-caret-down"></i>
							</button>

							<ul class="dropdown-menu dropdown-menu-left es-timeline-filter-dropdown">
								<li>
									<span class="es-timeline-filter-dropdown__title"><?php echo JText::_('COM_ES_MANAGE_CLUSTER_SIDEBAR_TITLE');?></span>
								</li>

								<?php if ($this->config->get('events.enabled')) { ?>
								<li class="has-bubble <?php echo $filter == 'event' ? ' active' : '';?>" data-filter-item data-type="event">
									<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));?>" title="<?php echo JText::_('COM_ES_PAGE_TITLE_EVENTS_MODERATION', true);?>">
										<span data-filter-item-text><?php echo JText::_('COM_EASYSOCIAL_EVENTS');?></span>
									</a>
									<div class="es-timeline-filter-dropdown__bubble" data-counter><?php echo $pendingCounters['event'];?></div>
								</li>
								<?php } ?>

								<?php if ($this->config->get('groups.enabled')) { ?>
								<li class="has-bubble <?php echo $filter == 'group' ? ' active' : '';?>" data-filter-item data-type="group">
									<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'group'));?>" title="<?php echo JText::_('COM_ES_PAGE_TITLE_GROUPS_MODERATION', true);?>">
										<span data-filter-item-text><?php echo JText::_('COM_EASYSOCIAL_GROUPS');?></span>
									</a>
									<div class="es-timeline-filter-dropdown__bubble" data-counter><?php echo $pendingCounters['group'];?></div>
								</li>
								<?php } ?>

								<?php if ($this->config->get('pages.enabled')) { ?>
								<li class="has-bubble <?php echo $filter == 'page' ? ' active' : '';?>" data-filter-item data-type="page">
									<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'page'));?>" title="<?php echo JText::_('COM_ES_PAGE_TITLE_PAGES_MODERATION', true);?>">
										<span data-filter-item-text><?php echo JText::_('COM_EASYSOCIAL_PAGES');?></span>
									</a>
									<div class="es-timeline-filter-dropdown__bubble" data-counter><?php echo $pendingCounters['page'];?></div>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div data-wrapper>
			<?php echo $this->html('listing.loader', 'listing', 8, 1); ?>

			<div data-contents>
				<?php echo $this->includeTemplate('site/manage/clusters/items', array('clusters' => $clusters, 'pagination' => $pagination)); ?>
			</div>
		</div>
	</div>
</div>
