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
<div id="es" class="mod-es mod-es-sidebar-friends <?php echo $this->lib->getSuffix();?>" data-es-friends-filters>
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module', 'es-friends-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_FRIENDS_SIDEBAR_TITLE'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo !$activeList && (!$filter || $filter == 'all' ) ? ' active' : '';?>" data-filter-item data-type="all">
						<a href="<?php echo $filters->all->link;?>" class="o-tabs__link" title="<?php echo $filters->all->page_title;?>">
							<?php echo $filters->all->label;?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $counter->friends;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>


					<?php if (!$user->isViewer() && $this->my->id) { ?>
					<li class="o-tabs__item has-notice <?php echo !$activeList && $filter == 'mutual' ? ' active' : '';?>" data-filter-item data-type="mutual">
						<a href="<?php echo $filters->mutual->link;?>" class="o-tabs__link" title="<?php echo $filters->mutual->page_title;?>">
							<?php echo $filters->mutual->label;?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $counter->mutual;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($user->isViewer()) { ?>
						<li class="o-tabs__item has-notice <?php echo !$activeList && $filter == 'suggest' ? ' active' : '';?>" data-filter-item data-type="suggest">
							<a href="<?php echo $filters->suggestion->link;?>" class="o-tabs__link" title="<?php echo $filters->suggestion->page_title;?>">
								<?php echo $filters->suggestion->label;?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counter->suggestions;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo !$activeList && $filter == 'pending' ? ' active' : '';?>" data-filter-item data-type="pending">
							<a href="<?php echo $filters->pending->link;?>" class="o-tabs__link" title="<?php echo $filters->pending->page_title;?>">
								<?php echo $filters->pending->label;?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counter->pending;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo !$activeList && $filter == 'request' ? ' active' : '';?>" data-filter-item data-type="request">
							<a href="<?php echo $filters->sent->link;?>" class="o-tabs__link" title="<?php echo $filters->sent->page_title;?>">
								<?php echo $filters->sent->label;?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counter->sent;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<?php if ($this->lib->config->get('friends.invites.enabled')) { ?>
						<li class="o-tabs__item has-notice <?php echo !$activeList && $filter == 'invites' ? ' active' : '';?>" data-filter-item data-type="invites">
							<a href="<?php echo $filters->invites->link;?>" class="o-tabs__link" title="<?php echo $filters->invites->page_title;?>">
								<?php echo $filters->invites->label;?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counter->invites;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php }?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($user->isViewer() && $this->lib->access->allowed('friends.list')) { ?>
			<hr class="es-hr" />

			<?php if ($user->isViewer() && ES::lists()->canCreateList()) { ?>
			<a href="<?php echo ESR::friends(array('layout' => 'listForm'));?>" class="btn btn-es-primary btn-block t-lg-mb--xl">
				<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_NEW_LIST'); ?>
			</a>
			<?php } ?>

			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_FRIENDS_YOUR_LIST'); ?>

				<div class="es-side-widget__bd" data-friends-list>
					<?php if ($lists) { ?>
					<ul class="o-tabs o-tabs--stacked" data-friends-listItems>
						<?php foreach ($lists as $list) { ?>
							<li class="o-tabs__item has-notice item-<?php echo $list->id;?><?php echo $activeList && $activeList->id == $list->id ? ' active' : '';?>" data-filter-item data-type="list" data-id="<?php echo $list->id;?>">
								<a href="<?php echo ESR::friends(array('listId' => $list->id));?>" class="o-tabs__link" title="<?php echo $this->lib->html('string.escape' , $list->get('title'));?>">
									<?php echo $this->lib->html('string.escape', $list->get('title')); ?>
								</a>
								<span class="o-tabs__bubble" data-counter><?php echo $list->getCount();?></span>
								<div class="o-loader o-loader--sm"></div>
							</li>
						<?php } ?>
					</ul>
					<?php } else { ?>
					<div class="t-text--muted">
						<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_NO_LIST_CREATED_YET'); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-friends-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
<script>
EasySocial
.require()
.script('site/friends/filter')
.done(function($){
	$('body').addController(EasySocial.Controller.Friends.Filter);
});
</script>
