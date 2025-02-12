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
<div class="es-mini-header t-lg-mb--lg">

	<div class="es-mini-header__hd" style="background-image: url('<?php echo $user->getCover();?>');background-position: <?php echo $user->getCoverPosition();?>;">
		<div class="es-mini-header__cover-content">

			<div class="o-flag">
				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.user', $user, 'default', false, true);?>
				</div>

				<div class="o-flag__body">
					<div class="o-grid">
						<div class="o-grid__cell">
							<?php echo $this->html('html.user', $user, false, 'sss', false, 'es-mini-header__title-link'); ?>

							<div class="es-mini-header__meta">

								<?php if ($this->config->get('users.layout.lastonline')) { ?>
								<ol class="g-list-inline g-list-inline--space-right">
									<li class="current">
										<span>
											<?php echo JText::_('COM_EASYSOCIAL_PROFILE_LAST_SEEN');?>, <strong><?php echo $user->getLastVisitDate('lapsed'); ?></strong>
										</span>
									</li>
								</ol>
								<?php } ?>

								<ul class="g-list-inline g-list-inline--space-right">
									<li>
										<a href="<?php echo $user->getPermalink();?>"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_MORE_INFO');?></a>
									</li>
									<?php if ($this->config->get('users.layout.profiletitle')) { ?>
									<li>
										(<a href="<?php echo $user->getProfile()->getPermalink();?>" class="profile-type"><?php echo $user->getProfile()->get('title');?></a>)
									</li>
									<?php } ?>
								</ul>
							</div>

							<?php if ($user->badgesViewable($this->my->id)) { ?>
							<div class="es-mini-header__badges">
								<?php foreach ($user->getBadges() as $badge) { ?>
									<a href="<?php echo $badge->getPermalink(); ?>" data-original-title="<?php echo $badge->getTitle(); ?>" data-placement="top" data-es-provide="tooltip" class="badge-link">
										<img src="<?php echo $badge->getAvatar(); ?>" alt="<?php echo $badge->getTitle(); ?>">
									</a>
								<?php } ?>
							</div>
							<?php } ?>
						</div>

						<?php if ($user->hasCommunityAccess() && $user->getApps('profile')) { ?>
						<div class="o-grid__cell o-grid__cell--auto-size">
							<div class="o-btn-group pull-right" role="group">
								<button type="button" class="btn btn-es-default-o btn-xs dropdown-toggle_" data-es-toggle="dropdown">
									<i class="fa fa-bullseye"></i> <b><?php echo JText::_('COM_EASYSOCIAL_APPS');?> &nbsp;<i class="fa fa-caret-down"></i></b>
								</button>

								<ul class="dropdown-menu dropdown-menu-right">
									<?php foreach ($user->getApps("profile") as $app) { ?>
									<li>
										<a href="<?php echo $app->getUserPermalink($user->getAlias());?>" class="o-nav__link"><?php echo $app->getAppTitle();?></a>
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

		<?php if ($user->hasCommunityAccess()) { ?>
		<div class="pull-left">
			<nav class="o-nav o-nav--block es-nav-pills">

				<?php if ($user->canCreateAlbums()) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::albums(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>">
						<i class="far fa-images"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_USER_ALBUMS' , $user->getTotalAlbums()), '<b>' . $user->getTotalAlbums() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($user->canCreateVideos()) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::videos(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>">
						<i class="fa fa-film"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_VIDEOS' , $user->getTotalVideos()), '<b>' . $user->getTotalVideos() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('friends.enabled', true)) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::friends( array( 'userid' => $user->getAlias() ) );?>">
						<i class="fa fa-users"></i>&nbsp; <?php echo JText::sprintf( ES::string()->computeNoun( 'COM_EASYSOCIAL_GENERIC_FRIENDS' , $user->getTotalFriends() ) , '<b>' . $user->getTotalFriends() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('groups.enabled')) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::groups(array('userid' => $user->getAlias()));?>">
						<i class="fa fa-users"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_COUNT', $user->getTotalGroups()), '<b>' . $user->getTotalGroups() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>


				<?php if ($this->config->get('events.enabled')) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::events(array('userid' => $user->getAlias()));?>">
						<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS', $user->getTotalCreatedJoinedEvents()), '<b>' . $user->getTotalCreatedJoinedEvents() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

				<?php if ($this->config->get('pages.enabled')) { ?>
				<span class="o-nav__item">
					<a class="o-nav__link" href="<?php echo ESR::pages(array('userid' => $user->getAlias()));?>">
						<i class="fa fa-cube"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES', $user->getTotalPages()), '<b>' . $user->getTotalPages() . '</b>'); ?>
					</a>
				</span>
				<?php } ?>

			</nav>
		</div>
		<?php } ?>

		<div class="pull-right">
			<?php if (!$user->isViewer()) { ?>
			<div class="o-btn-group">
				<?php echo $this->html('user.friends', $user); ?>

				<?php echo $this->html('user.subscribe', $user); ?>

				<?php echo $this->html('user.conversation', $user); ?>
			</div>
			<?php } ?>

			<?php if ($showDropdown) { ?>
				<div class="o-btn-group" role="group">
					<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
						<i class="fa fa-ellipsis-h"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-right">
						<?php if ($canBlockUser) { ?>
						<li>
							<?php echo ES::blocks()->getForm($user->id); ?>
						</li>
						<?php } ?>

						<?php if ($canReportUser) { ?>
						<li>
							<?php echo ES::reports()->form(SOCIAL_TYPE_USER, $user->id, array('dialogTitle' => 'COM_EASYSOCIAL_PROFILE_REPORT_USER',
										'dialogContent' => 'COM_EASYSOCIAL_PROFILE_REPORT_USER_DESC',
										'title' => $user->getName(),
										'permalink' => $user->getPermalink(true, true),
										'type' => 'link',
										'showIcon' => false,
										'text' => 'COM_EASYSOCIAL_PROFILE_REPORT_USER',
										'actorid' => $user->id
								)); ?>
						</li>
						<?php } ?>

						<?php if ($canBanUser) { ?>
							<li class="divider"></li>

							<?php if (!$user->isBlock()) { ?>
							<li data-admintool-banuser>
								<a href="javascript:void(0);" data-admintool-ban data-id="<?php echo $user->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_BAN_THIS_USER');?></a>
							</li>
							<?php } else { ?>
							<li>
								<a href="javascript:void(0);" data-id="<?php echo $user->id;?>" data-admintool-unban><?php echo JText::_('COM_EASYSOCIAL_PROFILE_UNBAN_USER');?></a>
							</li>
							<?php } ?>
						<?php } ?>

						<?php if ($canDeleteUser) { ?>
						<li class="divider"></li>
						<li data-admintool-deleteuser>
							<a href="javascript:void(0);" data-admintool-delete data-id="<?php echo $user->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_DELETE_THIS_USER');?></a>
						</li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>

		</div>
	</div>
</div>
