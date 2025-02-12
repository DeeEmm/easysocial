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
	data-id="<?php echo $page->id;?>"
	data-name="<?php echo $this->html('string.escape', $page->getName());?>"
	data-avatar="<?php echo $page->getAvatar();?>"
	data-es-page-item
>
	<div class="es-mini-header__hd" style="background-image: url('<?php echo $page->getCover();?>');background-position: <?php echo $page->getCoverPosition();?>;">

		<div class="es-mini-header__cover-content">
			<div class="o-flag">

				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.mini', $page->getName(), $page->getPermalink(), $page->getAvatar(SOCIAL_AVATAR_SQUARE), '', 'es-mini-header__avatar'); ?>
				</div>

				<div class="o-flag__body">
					<div class="o-grid">
						<div class="o-grid__cell">
							<a href="<?php echo $page->getPermalink();?>" class="es-mini-header__title-link"><?php echo $page->getName();?></a>
							<div class="es-mini-header__meta">
								<ol class="g-list-inline g-list-inline--space-right">
									<li>
										<a href="<?php echo $page->getCategory()->getFilterPermalink(); ?>">
											<i class="fa fa-folder"></i>&nbsp; <?php echo $page->getCategory()->get('title'); ?>
										</a>
									</li>
									<li>
										<?php echo $this->html('page.type', $page); ?>
									</li>
									<li>
										<a href="<?php echo ESR::pages(array('layout' => 'item', 'type' => 'info', 'id' => $page->getAlias()));?>">
											<?php echo JText::_('COM_EASYSOCIAL_PAGES_MORE_ABOUT_THIS_PAGE'); ?>
										</a>
									</li>
								</ol>
							</div>
						</div>

						<?php if ((!isset($showApps) || (isset($showApps) && $showApps)) && $page->getApps() && ($page->isMember() || $page->isOpen())) { ?>
						<div class="o-grid__cell o-grid__cell--auto-size">
							<div class="o-btn-group pull-right" role="group">

								<button type="button" class="btn btn-es-default-o btn-xs dropdown-toggle_" data-es-toggle="dropdown">
									<i class="fa fa-bullseye"></i> <b><?php echo JText::_('COM_EASYSOCIAL_APPS');?> &nbsp;<i class="fa fa-caret-down"></i></b>
								</button>

								<ul class="dropdown-menu dropdown-menu-right">
									<?php foreach ($page->getApps() as $app) { ?>
										<li>
											<a href="<?php echo ESR::pages(array('layout' => 'item', 'id' => $page->getAlias(), 'appId' => $app->getAlias()));?>"><?php echo $app->getAppTitle();?></a>
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

				<?php echo $this->render('widgets', 'page', 'pages', 'pageStatsStart', array($page)); ?>

				<span class="o-nav__item">
					<a href="<?php echo $page->getAppPermalink('followers');?>" class="o-nav__link">
						<i class="far fa-thumbs-up"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_LIKERS', $page->getTotalMembers()), '<b>' . $page->getTotalMembers() . '</b>'); ?>
					</a>
				</span>

				<?php if ($page->allowPhotos()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::albums(array('uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));?>" class="o-nav__link">
						<i class="far fa-images"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_ALBUMS', $page->getTotalAlbums()), '<b>' . $page->getTotalAlbums() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($page->allowVideos()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::videos(array('uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));?>" class="o-nav__link">
						<i class="fa fa-film"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_VIDEOS', $page->getTotalVideos()), '<b>' . $page->getTotalVideos() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($page->allowAudios()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::audios(array('uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));?>" class="o-nav__link">
						<i class="fa fa-music"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_CLUSTERS_AUDIO', $page->getTotalAudios()), '<b>' . $page->getTotalAudios() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($page->canViewEvent()) { ?>
				<span class="o-nav__item">
					<a href="<?php echo ESR::events(array('uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));?>" class="o-nav__link">
						<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_EVENTS', $page->getTotalEvents()), '<b>' . $page->getTotalEvents() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('pages.hits.display')) { ?>
				<span class="o-nav__item">
					<span class="o-nav__link">
						<i class="fa fa-eye"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_VIEWS', $page->hits), '<b>' . $page->hits . '</b>'); ?>
					</span>
				</span>
				<?php } ?>

				<?php echo $this->render('widgets', 'page', 'pages', 'pageStatsEnd', array($page)); ?>
			</nav>
		</div>
		<?php } ?>

		<div class="pull-right">
			<?php echo $this->html('page.action', $page); ?>

			<?php if (! $this->isMobile()) { ?>
				<?php echo $this->html('page.bookmark', $page); ?>

				<?php if ($this->my->isSiteAdmin() || $page->isMember() || ES::reports()->canReport()) { ?>
				<div class="o-btn-group" role="group">
					<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_MORE_ACTIONS');?>" data-es-provide="tooltip">
						<i class="fa fa-ellipsis-h"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-right">
						<?php echo $this->html('page.adminActions', $page); ?>

						<?php $reportHtml = $this->html('page.report', $page); ?>

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
