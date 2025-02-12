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
<div id="es" class="es-backend-module-wrap" data-es-info data-service-url="<?php echo SOCIAL_SERVICE_VERSION;?>">
	<div>
		<div class="row">
			<div class="col-lg-6">
				<div class="panel">
					<div class="panel-head t-my--no t-pb--no t-pl--no">
						<div class="t-d--flex">
							<div class="t-flex-grow--1">
								<b class="panel-head-title">Information</b>
							</div>
						</div>
					</div>

					<?php echo $lib->output('admin/easysocial/widgets/version', [
						'installedVersion' => $version
					]); ?>
				</div>

				<?php if ($showCounterHeader) { ?>
				<div class="panel">
					<div class="panel-head t-my--no t-pb--no t-pl--no">
						<b class="panel-head-title">Quick Stats</b>
					</div>
					<div class="panel-body t-bg--100 t-px--no t-my--no">
						<div class="l-stack">
							<?php if (isset($totalUsers)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=users'),
									'icon' => 'fa fa-user-friends',
									'label' => 'COM_EASYSOCIAL_USERS',
									'count' => $totalUsers
								]); ?>
							<?php } ?>

							<?php if (isset($totalPendingUsers)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=users&layout=pending'),
									'icon' => 'fa fa-user-friends',
									'label' => 'MOD_ES_INFO_PENDING_APPROVALS',
									'count' => $totalPendingUsers
								]); ?>
							<?php } ?>


							<?php if (isset($totalGroups)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=groups'),
									'icon' => 'fas fa-users',
									'label' => 'COM_EASYSOCIAL_GROUPS',
									'count' => $totalGroups
								]); ?>
							<?php } ?>

							<?php if (isset($totalPages)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=pages'),
									'icon' => 'fas fa-users',
									'label' => 'COM_EASYSOCIAL_PAGES',
									'count' => $totalPages
								]); ?>
							<?php } ?>

							<?php if (isset($totalEvents)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=events'),
									'icon' => 'fa fa-calendar-check',
									'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_EVENTS',
									'count' => $totalEvents
								]); ?>
							<?php } ?>

							<?php if (isset($totalOnline)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => 'javascript:void(0);',
									'icon' => 'fa fa-signal',
									'label' => 'COM_EASYSOCIAL_ONLINE',
									'count' => $totalOnline
								]); ?>
							<?php } ?>

							<?php if (isset($totalAlbums)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=albums'),
									'icon' => 'fa fa-images',
									'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_ALBUMS',
									'count' => $totalAlbums
								]); ?>
							<?php } ?>

							<?php if (isset($totalAudios)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=audios'),
									'icon' => 'fa fa-music',
									'label' => 'COM_ES_WIDGETS_STATS_TOTAL_AUDIO',
									'count' => $totalAudios
								]); ?>
							<?php } ?>

							<?php if (isset($totalVideos)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=videos'),
									'icon' => 'fa fa-play-circle',
									'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_VIDEOS',
									'count' => $totalVideos
								]); ?>
							<?php } ?>

							<?php if (isset($totalReports)) { ?>
								<?php echo $lib->output('admin/easysocial/widgets/stats.item', [
									'link' => JRoute::_('index.php?option=com_easysocial&view=videos'),
									'icon' => 'fa fa-flag',
									'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_REPORTS',
									'count' => $totalReports
								]); ?>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php } ?>

				<?php if (isset($pendingUsers) && $pendingUsers) { ?>
				<div class="panel">
					<div class="panel-head t-my--no t-pb--no t-pl--no">
						<div class="t-d--flex">
							<div class="t-flex-grow--1">
								<div class="t-d--flex t-align-items--c">
									<b class="panel-head-title"><?php echo JText::_('MOD_ES_INFO_PENDING_USERS');?></b> <span class="es-backend-mod-bubble"><?php echo $totalPending;?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-body t-bg--100 t-px--no t-my--no">
						<div class="l-stack">
							<?php foreach ($pendingUsers as $user) { ?>
							<div class="db-post-item">
								<div class="t-flex-grow--1 t-min-width--0 t-pr--lg">
									<div class="o-media o-media--top">
										<div class="o-media__image">
											<?php echo ES::template()->html('avatar.mini', $user->getName(), 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id, $user->getAvatar(), 'sm'); ?>
										</div>
										<div class="o-media__body">
											<div class="t-text--truncate">
												<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $user->id;?>" class="t-d--block t-mb--xs"><?php echo $user->getName();?> (<?php echo $user->getProfile()->getTitle();?>) </a>
											</div>
											<div class="o-meta l-cluster">
												<div>
													<div>
														<?php echo JText::sprintf('MOD_ES_INFO_REGISTERED_ON', $user->getRegistrationDate()->format(JText::_('DATE_FORMAT_LC3')));?>
													</div>
													<div>
														<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-approve data-id="<?php echo $user->id;?>"><?php echo JText::_('MOD_ES_INFO_APPROVE');?></a>
													</div>
													<div>
														<a href="javascript:void(0);" class="btn btn-es-danger-o btn-sm" data-reject data-id="<?php echo $user->id;?>"><?php echo JText::_('MOD_ES_INFO_REJECT');?></a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php } ?>

				<?php if (isset($recentUsers) && $recentUsers) { ?>
				<div class="panel">
					<div class="panel-head t-my--no t-pb--no t-pl--no">
						<div class="t-d--flex">
							<div class="t-flex-grow--1">
								<div class="t-d--flex t-align-items--c">
									<b class="panel-head-title"><?php echo JText::_('MOD_ES_INFO_RECENT_USERS');?></b>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-body t-bg--100 t-px--no t-my--no">
						<div class="l-stack">
							<?php foreach ($recentUsers as $user) { ?>
							<div class="db-post-item">
								<div class="t-flex-grow--1 t-min-width--0 t-pr--lg">
									<div class="o-media o-media--top">
										<div class="o-media__image">
											<?php echo ES::template()->html('avatar.mini', $user->getName(), 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id, $user->getAvatar(), 'sm'); ?>
										</div>
										<div class="o-media__body">
											<div class="t-text--truncate">
												<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $user->id;?>" class="t-d--block t-mb--xs"><?php echo $user->getName();?> (<?php echo $user->getProfile()->getTitle();?>) </a>
											</div>
											<div class="o-meta l-cluster">
												<div>
													<div>
														<?php echo JText::sprintf('MOD_ES_INFO_REGISTERED_ON', $user->getRegistrationDate()->format(JText::_('DATE_FORMAT_LC3')));?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php } ?>

			</div>

			<div class="col-lg-6">
				<div class="panel">
					<div class="panel-head t-my--no t-pb--no t-pl--no">
						<div class="t-d--flex">
							<div class="t-flex-grow--1">
								<b class="panel-head-title">StackIdeas Blog</b>
								<div class="panel-info">Recent updates and news from the team</div>
							</div>
						</div>
					</div>
					<div class="panel-body t-bg--100 t-px--no t-my--no">
						<div class="l-stack" data-news-result>
						</div>

						<div class="t-text--center t-lg-mt--lg">
							<a href="https://stackideas.com/blog" target="_blank" class="btn btn-es-default-o btn-block"><?php echo JText::_('MOD_ES_INFO_VIEW_ALL_POSTS');?> &rarr;</a>
						</div>
					</div>
				</div>

				<div data-news-template>
					<div class="db-post-item t-hidden">
						<div class="t-min-width--0">
							<div class="t-text--truncate t-mb--sm t-font-size--03">
								<b><a href="javascript:void(0);" data-permalink target="_blank" class="si-link" data-title></a></b>
							</div>

							<div class="t-text--500" data-date></div>

							<div class="t-flex-grow--1 t-min-width--0 t-pr--lg">
								<div class="o-media o-media--top o-media--rev">
									<div class="o-media__image">
										<a class="t-rounded--lg t-d--block t-overflow--hidden" href="javascript:void(0);" target="_blank" data-permalink>
											<img data-image width="120" align="right" />
										</a>
									</div>
									<div class="o-media__body">
										<div class="l-stack l-spaces--xs">
											<div class="t-text--700" data-content></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
