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
<?php if ($cluster) { ?>
<div class="es-cluster-back-btn-container">
	<a href="<?php echo $cluster->getPermalink();?>" class="btn btn-es-default-o">&larr; <?php echo JText::_('COM_ES_BACK_TO_' . strtoupper($cluster->getType()));?></a>
</div>
<?php } ?>

<?php echo $this->render('module', 'es-event-before-cover'); ?>

<div class="es-profile-header t-lg-mb--lg">
	<div class="es-profile-header__hd with-cover">
		<div class="es-profile-header__cover es-flyout <?php echo $event->hasCover() ? 'has-cover' : 'no-cover'; ?> <?php echo !empty($newCover) ? "editing" : ""; ?> <?php echo $event->isAdmin() ? 'is-owner' : '';?>"
			data-cover <?php echo $cover->photo_id && $showPhotoPopup ? 'data-es-photo="' . $cover->photo_id . '"' : '';?>
			style="background-image: url(<?php echo $cover->getSource();?>);background-position: <?php echo $cover->getPosition();?>;">

			<div class="es-cover-container">
				<div class="es-cover-viewport">

					<div class="es-cover-image" data-cover-image
						<?php if (!empty($newCover)) { ?>
						data-photo-id="<?php echo $newCover->id; ?>"
						style="background-image: url(<?php echo $newCover->getSource('large'); ?>);"
						<?php } ?>
						<?php if ($cover->id) { ?>
						data-photo-id="<?php echo $cover->getPhoto()->id; ?>"
						style="background-image: url(<?php echo $cover->getSource(); ?>);"
						<?php } ?>
					>
					</div>

					<div class="es-cover-hint">
						<span>
							<span class="o-loader o-loader--sm o-loader--inline with-text"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_COVER_LOADING'); ?></span>
							<span class="es-cover-hint-text"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_COVER_DRAG_HINT'); ?></span>
						</span>
					</div>

					<div class="es-cover-loading-overlay"></div>

					<?php if ($event->isAdmin() || $user->isSiteAdmin()) { ?>
					<div class="es-flyout-content">
						<div class="dropdown_  es-cover-menu" data-cover-menu>
							<a href="javascript:void(0);" data-es-toggle="dropdown" class="dropdown-toggle_ es-flyout-button">
								<i class="fa fa-camera"></i>
							</a>
							<ul class="dropdown-menu es-cover-dropdown-menu">
								<li data-cover-upload-button>
									<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_UPLOAD_COVER"); ?></a>
								</li>
								<li data-cover-select-button>
									<a href="javascript:void(0);"><?php echo JText::_( 'COM_EASYSOCIAL_PHOTOS_SELECT_COVER' ); ?></a>
								</li>
								<li data-cover-edit-button>
									<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_REPOSITION_COVER'); ?></a>
								</li>
								<li class="divider for-cover-remove-button"></li>
								<li data-cover-remove-button>
									<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_REMOVE_COVER"); ?></a>
								</li>
							</ul>
						</div>

						<a href="javascript:void(0);"
						   class="es-cover-done-button es-flyout-button"
						   data-cover-done-button><i class="fa fa-check"></i><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_COVER_DONE"); ?></a>

						<a href="javascript:void(0);"
						   class="es-cover-cancel-button es-flyout-button"
						   data-cover-cancel-button><i class="fa fa-times"></i><?php echo JText::_("COM_ES_CANCEL"); ?></a>
					</div>

					<div class="es-cover-desktop-hint">
							<a href="javascript:void(0);" class="es-cover-desktop-hint__cancel" data-cover-cancel-button>
								<i class="fa fa-times"></i>&nbsp; <?php echo JText::_("COM_ES_CANCEL"); ?>
							</a>
							<div class="es-cover-desktop-hint__content">
								<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_COVER_DRAG_HINT'); ?>
							</div>
							<a href="javascript:void(0);" class="es-cover-desktop-hint__save" data-cover-done-button>
								<i class="fa fa-check"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_PHOTOS_COVER_DONE"); ?>
							</a>

					</div>
					<div class="es-cover-desktop-action">
						<div class="es-cover-desktop-action__update">
							<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-cover-edit-button><?php echo JText::_('COM_ES_UPDATE_COVER');?></a>
						</div>
						<div class="es-cover-desktop-action__trigger">
							<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-cover-upload-button>
								<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_UPLOAD_COVER"); ?>
							</a>
							<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-cover-select-button>
								<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SELECT_COVER'); ?>
							</a>


							<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-cover-remove-button>
								<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_REMOVE_COVER"); ?>
							</a>
						</div>
					</div>
					<?php } ?>

				</div>
			</div>
		</div>

		<div class="es-profile-header__avatar-wrap es-flyout" data-avatar>

			<div class="o-avatar-v2 o-avatar-v2--xl<?php echo $this->config->get('layout.avatar.style') == 'rounded' ? ' o-avatar-v2--rounded' : '';?>">
				<div class="o-avatar-v2__mobile"></div>
				<a href="javascript:void(0);" class="o-avatar-v2__content"
					<?php if ($event->getAvatarPhoto() && $showPhotoPopup) { ?>
					data-es-photo="<?php echo $event->getAvatarPhoto()->id;?>"
					<?php } ?>
				>
					<img src="<?php echo $event->getAvatar(SOCIAL_AVATAR_SQUARE);?>" alt="<?php echo $this->html('string.escape' , $event->getName() );?>" data-avatar-image />
				</a>
				<?php if ($event->isAdmin() || $user->isSiteAdmin()) { ?>
				<div class="o-avatar-v2__action">
					<a href="javascript:void(0);" class="dropdown-toggle_" data-es-toggle="dropdown">
						<i class="fas fa-cog"></i>
					</a>
					<ul class="dropdown-menu">
						<li data-avatar-upload-button>
							<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_UPLOAD_AVATAR"); ?></a>
						</li>

						<li data-avatar-select-button>
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SELECT_AVATAR'); ?></a>
						</li>

						<?php if ($this->config->get('users.avatarWebcam') && !$this->isMobile() && ES::isHttps()) { ?>
						<li class="divider"></li>
						<li data-avatar-webcam>
							<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_TAKE_PHOTO"); ?></a>
						</li>
						<?php } ?>

						<?php if ($event->hasAvatar() && ($event->isAdmin() || $user->isSiteAdmin())) { ?>
						<li class="divider"></li>
						<li data-avatar-remove-button>
							<a href="javascript:void(0);">
								<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_REMOVE_AVATAR"); ?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
				<?php } ?>
			</div>

			<?php echo $this->render('module', 'es-profile-avatar'); ?>
		</div>

		<?php echo $this->render('widgets', SOCIAL_TYPE_EVENT, 'item', 'afterAvatar', array($event)); ?>
	</div>
	<div class="es-profile-header__bd">
		<div class="es-profile-header__info-wrap">
			<?php echo $this->render('module', 'es-events-before-name'); ?>

			<?php if ($active == 'entry') { ?>
				<h2 class="es-profile-header__title">
					<?php echo $this->html('html.cluster', $event, false); ?>
				</h2>
			<?php } else { ?>
				<h1 class="es-profile-header__title">
					<?php echo $this->html('html.cluster', $event, false); ?>
				</h1>
			<?php } ?>

			<?php echo $this->render('module', 'es-events-after-name'); ?>

			<ul class="g-list-inline g-list-inline--dashed es-profile-header__meta t-lg-mt--md">
				<li>
					<a href="<?php echo $event->getCategory()->getFilterPermalink();?>" class="">
						<i class="fa fa-folder"></i>&nbsp; <?php echo $event->getCategory()->getTitle(); ?>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" class="">
						<?php echo $this->html('event.type', $event); ?>
					</a>
				</li>

				<li>
					<a href="<?php echo $event->getAppPermalink('guests');?>" class="">
						<i class="fa fa-users"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_TOTAL_GUESTS', $event->getTotalGoing()), $event->getTotalGoing()); ?>
					</a>
				</li>

				<?php if ($this->config->get('events.layout.hits')) { ?>
				<li>
					<a href="javascript:void(0);" class="" data-es-provide="tooltip" data-title="<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_TOTAL_VIEWS', $event->hits), $event->hits); ?>">
						<i class="fa fa-eye"></i>&nbsp; <?php echo $event->hits;?>
					</a>
				</li>
				<?php } ?>

				<li>
					<span class="">
						<i class="far fa-calendar-alt"></i>&nbsp; <?php echo $event->getStartEndDisplay(); ?>
					</span>
				</li>

				<li>
					<?php echo $this->render('widgets', 'event', 'events', 'headerMeta', array($event)); ?>
				</li>
			</ul>
		</div>

		<div class="es-profile-header__action-wrap">
			<?php echo $this->render('module', 'es-events-before-actions'); ?>
			<?php echo $this->render('widgets', SOCIAL_TYPE_EVENT, 'item', 'beforeActions', array($event)); ?>

			<div class="es-profile-header__action-toolbar" role="toolbar">

				<div class="o-btn-group">
					<?php echo $this->html('event.action', $event, 'right', true); ?>
				</div>

				<?php if ($this->config->get('events.ical', true)) { ?>
				<div class="o-btn-group">
					<a href="<?php echo $event->getCalendarLink(); ?>" class="btn btn-es-default-o btn-sm" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_EVENTS_EXPORT_TO_ICAL', true);?>">
						<i class="far fa-calendar-alt"></i>
					</a>
				</div>
				<?php } ?>

				<?php if (($this->config->get('sharing.enabled')) && ($this->config->get('events.sharing.showprivate') || (!$this->config->get('events.sharing.showprivate') && $event->isOpen()))) { ?>
				<div class="o-btn-group">
					<?php echo $this->html('event.bookmark', $event); ?>
				</div>
				<?php } ?>

				<?php if ($event->canAccessActionMenu() || $event->canInvite()) { ?>
				<div class="o-btn-group">

					<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
						<i class="fa fa-ellipsis-h"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-right">
						<?php if ($event->canInvite()) { ?>
							<li>
								<?php $buttonLabel = ($event->isClusterEvent() && $event->isInviteOnly()) ? 'COM_ES_CLUSTERS_INVITE_MEMBERS' : 'COM_EASYSOCIAL_GROUPS_INVITE_FRIENDS'; ?>
								<a href="javascript:void(0);" data-es-events-invite data-id="<?php echo $event->id;?>" data-return="<?php echo $returnUrl;?>" >
									<?php echo JText::_($buttonLabel); ?></a>
							</li>
							<?php if ($event->canInviteNonFriends()) { ?>
								<li>
									<a href="<?php echo ESR::friends(array('layout' => 'invite', 'cluster_id' => $event->id));?>"><?php echo JText::_('COM_ES_INVITE_VIA_EMAIL');?></a>
								</li>
							<?php } ?>

							<?php if ($event->canAccessActionMenu()) { ?>
							<li class="divider"></li>
							<?php } ?>
						<?php } ?>

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
			</div>

			<?php echo $this->render('module', 'es-events-after-actions'); ?>
			<?php echo $this->render('widgets', SOCIAL_TYPE_EVENT, 'item', 'afterActions', array($event)); ?>
		</div>
	</div>

	<?php echo $this->includeTemplate('site/helpers/cover/navigation'); ?>
</div>

<?php echo $this->render('module', 'es-event-after-cover'); ?>
