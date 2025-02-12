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
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module', 'es-users-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($this->lib->my->isSiteAdmin() || $this->access->allowed('search.create.filter')) { ?>
		<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl">
			<?php echo JText::_('COM_ES_CREATE_NEW_FILTER');?>
		</a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_USERS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">

					<li class="o-tabs__item <?php echo !$filter || $filter == 'all' ? 'active' : '';?>" data-filter-item data-type="users" data-id="all">
						<a href="<?php echo ESR::users();?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_USERS', true);?>">
							<?php echo JText::_('COM_EASYSOCIAL_USERS_FILTER_USERS_ALL_USERS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ($this->my->id && $this->config->get('friends.enabled')) { ?>
					<li class="o-tabs__item <?php echo $filter == 'friends' ? 'active' : '';?>" data-filter-item data-type="users" data-id="friends">
						<a href="<?php echo ESR::users(array('filter' => 'friends'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_ES_MY_FRIENDS', true);?>">
							<?php echo JText::_('COM_ES_MY_FRIENDS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($this->my->id && $this->config->get('followers.enabled')) { ?>
					<li class="o-tabs__item <?php echo $filter == 'followers' ? 'active' : '';?>" data-filter-item data-type="users" data-id="followers">
						<a href="<?php echo ESR::users(array('filter' => 'followers'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_ES_MY_FOLLOWERS', true);?>">
							<?php echo JText::_('COM_ES_MY_FOLLOWERS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<li class="o-tabs__item <?php echo $filter == 'photos' ? 'active' : '';?>" data-filter-item data-type="users" data-id="photos">
						<a href="<?php echo ESR::users(array('filter' => 'photos'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_USERS_WITH_PHOTOS', true);?>">
							<?php echo JText::_('COM_EASYSOCIAL_USERS_FILTER_USERS_WITH_PHOTOS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item <?php echo $filter == 'online' ? 'active' : '';?>" data-filter-item data-type="users" data-id="online">
						<a href="<?php echo ESR::users(array('filter' => 'online'));?>" class="o-tabs__link" title="<?php echo JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_USERS_ONLINE_USERS' , true );?>">
							<?php echo JText::_('COM_EASYSOCIAL_USERS_FILTER_ONLINE_USERS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ($this->lib->config->get('users.verification.enabled')) { ?>
					<li class="o-tabs__item <?php echo $filter == 'verified' ? 'active' : '';?>" data-filter-item data-type="users" data-id="verified">
						<a href="<?php echo ESR::users(array('filter' => 'verified'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_ES_PAGE_TITLE_USERS_VERIFIED_USERS', true);?>">
							<?php echo JText::_('COM_ES_USERS_FILTER_VERIFIED_USERS');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if (ES::config()->get('users.blocking.enabled') && $this->lib->my->id) { ?>
					<li class="o-tabs__item <?php echo $filter == 'blocked' ? 'active' : '';?>" data-filter-item data-type="users" data-id="blocked">
						<a href="<?php echo ESR::users(array('filter' => 'blocked'));?>" class="o-tabs__link" title="<?php echo JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_USERS_BLOCKED' , true );?>">
							<?php echo JText::_('COM_ES_USERS_FILTER_BLOCKED');?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($this->lib->my->isSiteAdmin() || $searchFilters) { ?>
						<?php if ($searchFilters) { ?>
							<?php foreach ($searchFilters as $searchFilter) { ?>
							<li class="o-tabs__item filter-item<?php echo $filter == 'search' && $fid == $searchFilter->id ? ' active' : '';?>" data-filter-item data-type="search" data-id="<?php echo $searchFilter->id;?>">
								<a href="<?php echo ESR::users(array('filter' => 'search', 'id' => $searchFilter->getAlias()));?>" class="o-tabs__link" title="<?php echo $searchFilter->get('title'); ?>">
									<?php echo $this->lib->html('string.escape', $searchFilter->get('title')); ?>
								</a>
								<div class="o-loader o-loader--sm"></div>
							</li>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($profiles) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_USERS_BROWSE_BY_PROFILES'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php foreach ($profiles as $profile) { ?>
					<li class="o-tabs__item has-notice filter-item<?php echo $filter == 'profiletype' && $activeProfile->id == $profile->id ? ' active' : '';?>" data-filter-item data-type="profiles" data-id="<?php echo $profile->id;?>">
						<a href="<?php echo ESR::users(array('filter' => 'profiletype', 'id' => $profile->getAlias()));?>" class="o-tabs__link" title="<?php echo $profile->get('title'); ?>">
							<?php echo $profile->get('title'); ?>
							<?php if ($this->lib->config->get('users.listings.profilescount', 0)) { ?>
							<div class="o-tabs__bubble"><?php echo $profile->totalUsers;?></div>
							<?php } ?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-users-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>

	</div>
</div>

<script>
EasySocial.require()
.script('site/users/default')
.done(function($) {
	$('[data-es-users]').implement(EasySocial.Controller.Users);
});
</script>
