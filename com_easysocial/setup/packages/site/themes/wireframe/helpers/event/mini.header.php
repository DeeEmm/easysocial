<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-mini-header t-lg-mb--lg"
	data-id="<?php echo $event->id;?>"
	data-name="<?php echo $this->html('string.escape' , $event->getName() );?>"
	data-avatar="<?php echo $event->getAvatar();?>"
	data-es-event-item
>
	<div class="es-mini-header__hd" style="background-image: url('<?php echo $event->getCover();?>');background-position: <?php echo $event->getCoverPosition();?>;">

		<div class="es-mini-header__cover-content">
			<div class="o-flag">

				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_SQUARE), '', 'es-mini-header__avatar'); ?>
				</div>
				<div class="o-flag__body">
					<div class="o-grid">
						<div class="o-grid__cell">
							<a href="<?php echo $event->getPermalink();?>" class="es-mini-header__title-link"><?php echo $event->getName();?></a>
							<div class="es-mini-header__meta">
								<ol class="g-list-inline g-list-inline--space-right" >
									<li>
										<a href="<?php echo $event->getCategory()->getFilterPermalink(); ?>">
											<i class="fa fa-folder"></i>&nbsp; <?php echo $event->getCategory()->_('title'); ?>
										</a>
									</li>

									<?php if ($event->isGroupEvent()) { ?>
									<li>
										<?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_GROUP_EVENT_OF_GROUP', '<i class="fa fa-users"></i> ' . $this->html('html.group', $event->getGroup())); ?>
									</li>
									<?php } ?>

									<?php if ($event->isPageEvent()) { ?>
									<li>
										<?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_PAGE_EVENT_OF_PAGE', '<i class="fa fa-users"></i> ' . $this->html('html.page', $event->getPage())); ?>
									</li>
									<?php } ?>

									<li>
										<?php echo $this->html('event.type', $event); ?>
									</li>

									<li>
										<a href="<?php echo ESR::events(array('layout' => 'item', 'type' => 'info', 'id' => $event->getAlias()));?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_MORE_ABOUT_THIS_EVENT'); ?></a>
									</li>
								</ol>
							</div>
						</div>

						<?php if ((!isset($showApps) || (isset($showApps) && $showApps)) && $event->getApps() && ($event->getGuest()->isGuest() || $event->isOpen())) { ?>
						<div class="o-grid__cell o-grid__cell--auto-size">
							<div class="o-btn-group pull-right" role="group">

								<button type="button" class="btn btn-es-default-o btn-xs dropdown-toggle_" data-es-toggle="dropdown">
									<i class="fa fa-bullseye"></i> <b><?php echo JText::_('COM_EASYSOCIAL_APPS');?> &nbsp;<i class="fa fa-caret-down"></i></b>
								</button>

								<ul class="dropdown-menu dropdown-menu-right">
									<?php foreach ($event->getApps() as $app) { ?>
									<li>
										<a href="<?php echo ESR::events(array('layout' => 'item', 'id' => $event->getAlias(), 'appId' => $app->getAlias()));?>">
											<?php echo $app->getAppTitle(); ?>
										</a>
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

				<?php echo $this->render('widgets', 'event', 'events', 'eventStatsStart', array($event)); ?>

				<span class="o-nav__item">
					<a href="<?php echo $event->getAppPermalink('guests');?>" class="o-nav__link">
						<i class="fa fa-users"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_TOTAL_GUESTS', $event->getTotalGuests()), '<b>' . $event->getTotalGuests() . '</b>'); ?>
					</a>
				</span>

				<?php if ($event->getCategory()->getAcl()->get('photos.enabled', true) && $event->getParams()->get('photo.albums', true)) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::albums(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="o-nav__link">
						<i class="far fa-images"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_ALBUMS', $event->getTotalAlbums()), '<b>' . $event->getTotalAlbums() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('video.enabled', true) && $event->getParams()->get('videos', true)) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::videos(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="o-nav__link">
						<i class="fa fa-film"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_VIDEOS', $event->getTotalVideos()), '<b>' . $event->getTotalVideos() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('audio.enabled', true) && $event->getParams()->get('audios', true)) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::audios(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="o-nav__link">
						<i class="fa fa-music"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_CLUSTERS_AUDIO', $event->getTotalAudios()), '<b>' . $event->getTotalAudios() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('events.layout.hits')) { ?>
				<span class="o-nav__item">
					<span class="o-nav__link">
						<i class="fa fa-eye"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_VIEWS', $event->hits), '<b>' . $event->hits . '</b>'); ?>
					</span>
				</span>
				<?php } ?>

				<?php echo $this->render('widgets', 'event', 'events', 'eventStatsEnd', array($event)); ?>
			</nav>
		</div>
		<?php } ?>

		<div class="pull-right">
			<?php echo $this->html('event.action', $event); ?>

			<?php if (! $this->isMobile()) { ?>
				<?php echo $this->html('event.bookmark', $event); ?>

				<?php if ($this->my->isSiteAdmin() || ES::reports()->canReport()) { ?>
				<div class="o-btn-group" role="group">
					<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_MORE_ACTIONS');?>" data-es-provide="tooltip">
						<i class="fa fa-ellipsis-h"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-right">
						<?php $reportHtml = $this->html('event.report', $event); ?>

						<?php if ($reportHtml) { ?>
						<li>
							<?php echo $reportHtml; ?>
						</li>
						<?php } ?>

						<?php echo $this->html('event.adminActions', $event); ?>
					</ul>
				</div>
				<?php } ?>
			<?php } ?>

		</div>
	</div>
</div>
