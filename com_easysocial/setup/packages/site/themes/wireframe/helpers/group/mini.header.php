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
<div class="es-mini-header t-lg-mb--lg"
	data-id="<?php echo $group->id;?>"
	data-name="<?php echo $this->html('string.escape', $group->getName());?>"
	data-avatar="<?php echo $group->getAvatar();?>"
	data-es-group-item
>
	<div class="es-mini-header__hd" style="background-image: url('<?php echo $group->getCover();?>');background-position: <?php echo $group->getCoverPosition();?>;">

		<div class="es-mini-header__cover-content">
			<div class="o-flag">

				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.mini', $group->getName(), $group->getPermalink(), $group->getAvatar(SOCIAL_AVATAR_SQUARE), '', 'es-mini-header__avatar'); ?>
				</div>

				<div class="o-flag__body">
					<div class="o-grid">
						<div class="o-grid__cell">
							<a href="<?php echo $group->getPermalink();?>" class="es-mini-header__title-link"><?php echo $group->getName();?></a>
							<div class="es-mini-header__meta">
								<ol class="g-list-inline g-list-inline--space-right">
									<li>
										<a href="<?php echo $group->getCategory()->getFilterPermalink(); ?>">
											<i class="fa fa-folder"></i>&nbsp; <?php echo $group->getCategory()->get('title'); ?>
										</a>
									</li>
									<li>
										<?php echo $this->html('group.type', $group, 'bottom', true); ?>
									</li>
									<li>
										<a href="<?php echo FRoute::groups( array( 'layout' => 'item', 'type' => 'info', 'id' => $group->getAlias() ) );?>">
											<?php echo JText::_('COM_EASYSOCIAL_GROUPS_MORE_ABOUT_THIS_GROUP'); ?>
										</a>
									</li>
								</ol>
							</div>
						</div>

						<?php if ((!isset($showApps) || (isset($showApps) && $showApps)) && $group->getApps() && ($group->isMember() || $group->isOpen()) && !$group->isDraft()) { ?>
						<div class="o-grid__cell o-grid__cell--auto-size">
							<div class="o-btn-group pull-right" role="group">

								<button type="button" class="btn btn-es-default-o btn-xs dropdown-toggle_" data-es-toggle="dropdown">
									<i class="fa fa-bullseye"></i> <b><?php echo JText::_('COM_EASYSOCIAL_APPS');?> &nbsp;<i class="fa fa-caret-down"></i></b>
								</button>

								<ul class="dropdown-menu dropdown-menu-right">
									<?php foreach ($group->getApps() as $app) { ?>
										<li>
											<a href="<?php echo ESR::groups(array('layout' => 'item', 'id' => $group->getAlias(), 'appId' => $app->getAlias()));?>"><?php echo $app->getAppTitle();?></a>
										</li>
									<?php } ?>
								</ul>
							 </div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mini-header__ft">
		<?php if (! $this->isMobile()) { ?>
		<div class="pull-left">
			<nav class="o-nav o-nav--block es-nav-pills">

				<?php echo $this->render('widgets', 'group', 'groups', 'groupStatsStart', array($group)); ?>

				<span class="o-nav__item">
					<a href="<?php echo $group->getAppPermalink('members');?>" class="o-nav__link">
						<i class="fa fa-users"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_MEMBERS_MINI', $group->getTotalMembers()), '<b>' . $group->getTotalMembers() . '</b>'); ?>
					</a>
				</span>

				<?php if ($group->allowPhotos()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::albums(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));?>" class="o-nav__link">
						<i class="far fa-images"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_ALBUMS', $group->getTotalAlbums()), '<b>' . $group->getTotalAlbums() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($group->allowVideos()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::videos(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));?>" class="o-nav__link">
						<i class="fa fa-film"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_VIDEOS', $group->getTotalVideos()), '<b>' . $group->getTotalVideos() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($group->allowAudios()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::audios(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));?>" class="o-nav__link">
						<i class="fa fa-music"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_CLUSTERS_AUDIO', $group->getTotalAudios()), '<b>' . $group->getTotalAudios() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($group->canViewEvent()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::events(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));?>" class="o-nav__link">
						<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_EVENTS', $group->getTotalEvents()), '<b>' . $group->getTotalEvents() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('groups.layout.hits')) { ?>
				<span class="o-nav__item">
					<span class="o-nav__link">
						<i class="fa fa-eye"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_VIEWS', $group->hits), '<b>' . $group->hits . '</b>'); ?>
					</span>
				</span>
				<?php } ?>

				<?php echo $this->render('widgets', 'group', 'groups', 'groupStatsEnd', array($group)); ?>
			</nav>
		</div>
		<?php } ?>

		<div class="pull-right">
			<?php echo $this->html('group.action', $group); ?>

			<?php if (! $this->isMobile()) { ?>
				<?php echo $this->html('group.bookmark', $group); ?>

				<?php if ($this->my->isSiteAdmin() || $group->isMember() || ES::reports()->canReport()) { ?>
				<div class="o-btn-group" role="group">
					<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_MORE_ACTIONS');?>" data-es-provide="tooltip">
						<i class="fa fa-ellipsis-h"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-right">
						<?php echo $this->html('group.adminActions', $group); ?>

						<?php $reportHtml = $this->html('group.report', $group); ?>

						<?php if ($reportHtml) { ?>
						<li>
							<?php echo $reportHtml; ?>
						</li>
						<?php } ?>
					</ul>
				</div>
				<?php } ?>
			<?php } ?>

		</div>
	</div>
</div>
