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
<div class="wrapper-for-full-height">
<?php echo $video->getMiniHeader();?>

<div class="es-container es-videos" data-video-item data-id="<?php echo $video->id;?>">
	<div class="es-content">
		<?php echo $this->render('module' , 'es-videos-before-video'); ?>

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $backLink;?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_EASYSOCIAL_VIDEOS_BACK_TO_VIDEOS'); ?></a>
				</div>

				<?php if ($video->canFeature() || $video->canUnfeature() || $video->canDelete() || $video->canEdit()) { ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right" role="group">
						<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
							 <i class="fa fa-ellipsis-h"></i>
							 <span class="t-hidden"><?php echo JText::_('COM_EASYSOCIAL_MANAGE');?></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-right">
							<?php if ($video->canFeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-video-feature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FEATURE_VIDEO');?></a>
							</li>
							<?php } ?>

							<?php if ($video->canUnfeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-video-unfeature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_UNFEATURE_VIDEO');?></a>
							</li>
							<?php } ?>

							<?php if ($video->canEdit()) { ?>
							<li>
								<a href="<?php echo $video->getEditLink();?>"><?php echo JText::_('COM_EASYSOCIAL_EDIT'); ?></a>
							</li>
								<?php if ($video->isUpload()) { ?>
								<li>
									<a href="javascript:void(0);" data-video-thumbnail-edit><?php echo JText::_('COM_ES_VIDEOS_EDIT_THUMBNAIL'); ?></a>
								</li>
								<?php } ?>
							<?php } ?>

							<?php if ($video->canDelete()) { ?>
							<li>
								<a href="javascript:void(0);" data-video-delete><?php echo JText::_('COM_EASYSOCIAL_DELETE');?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry-section__content">
				<div class="es-video-content-body">
					<?php if ($video->isPendingProcess()) { ?>
					<div class="alert alert-info">
						<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_ITEM_PENDING_INFO');?>
					</div>
					<?php } ?>

					<?php if ($video->renderEmbedExternalVideo()) { ?>
						<?php echo $video->getEmbedCodes(); ?>
					<?php } ?>

					<?php if (!$video->renderEmbedExternalVideo()) { ?>
						<div class="video-container<?php echo $video->isFacebookEmbed() ? ' ' . $video->getRatioString() : ''; ?>">
							<?php echo $video->getEmbedCodes(); ?>
						</div>
					<?php } ?>
				</div>

				<div class="t-hidden" data-video-thumbnail-wraper>
					<div class="es-video-cover-action t-lg-pl--no t-lg-pr--no t-lg-pb--no" style="background: none;">
						<div class="es-video-cover-action__cell t-lg-mr--md">
							<div class="o-alert t-hidden" style="margin-bottom: 0px;" data-video-thumbnail-msg>
							</div>
						</div>

						<div class="es-video-cover-action__cell es-video-cover-action__cell--last">
							<a href="javascript:void(0);" class="btn btn-es-default-o" data-video-thumbnail-done><?php echo JTEXT::_('COM_ES_VIDEOS_THUMB_DONE'); ?></a>
						</div>
					</div>

					<div class="es-video-cover-action t-lg-mt--lg t-lg-mb--lg">

						<div class="es-video-cover-action__cell">
							<div class="es-video-cover-action__media">
								<div class="es-embed-container">
									<div class="es-embed-container__action<?php echo $video->isThumbnailCustom() ? '' : ' t-hidden'; ?>" data-video-thumbnail-revert>
										<a href="javascript:void(0);" class="es-embed-container__remove" title="<?php echo JTEXT::_('COM_ES_VIDEOS_THUMB_REVERT_TO_ORIGINAL', true); ?>">x</a>
									</div>
									<div class="embed-responsive embed-responsive-16by9">
										<img src="<?php echo $video->getThumbnail(); ?>" alt="" class="embed-responsive-item" data-video-thumbnail-img>
									</div>
								</div>

							</div>

							<div class="t-xs-mt--lg">
								<div>
									<div class="t-lg-mb--lg"><?php echo JText::_('COM_ES_VIDEOS_THUMB_INSTRUCTION'); ?></div>
									<a href="javascript:void(0);" class="btn btn-es-default-o" data-video-thumbnail-play>
										<i class="fas fa-play"></i>&nbsp; <?php echo JTEXT::_('COM_ES_VIDEOS_THUMB_PLAY'); ?>
									</a>

									<a href="javascript:void(0);" class="btn btn-es-default-o t-hidden" data-video-thumbnail-capture>
										<i class="fa fa-camera"></i>&nbsp; <?php echo JTEXT::_('COM_ES_VIDEOS_THUMB_CAPTURE'); ?>
									</a>
									<a href="javascript:void(0);" class="btn btn-es-default-o t-lg-ml--md" data-video-thumbnail-upload>
										<i class="fas fa-upload"></i>
										<?php echo JTEXT::_('COM_ES_VIDEOS_THUMB_UPLOAD'); ?>
									</a>
								</div>
							</div>

						</div>
					</div>
				</div>


				<?php echo $this->render('module' , 'es-videos-after-video'); ?>

				<div class="t-lg-mt--lg">

					<div class="o-grid">
						<div class="o-grid__cell o-grid__cell--text-overflow">
							<div class="es-video-context">
								<h1 class="es-video-title single"><?php echo $video->getTitle();?></h1>
								<div class="es-video-meta t-lg-mb--md t-text--muted">
									<span>
										<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_UPLOADED_BY', $this->html('html.' . $creator->getType(), $creator),
													'<a href="' . $video->getCategory()->getPermalink(true, $uid, $type) . '">' . JText::_($video->getCategory()->title) . '</a>'
												);?>
									</span>

									<?php if ($this->config->get('video.layout.item.hits')) { ?>
									<span>
										<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_HITS', $video->getHits()); ?>
									</span>
									<?php } ?>

									<span>
										<?php echo $video->getCreatedDate()->format(JText::_('COM_EASYSOCIAL_DATE_DMY'));?>
									</span>

									<?php if ($this->config->get('video.layout.item.duration')) { ?>
									<span class="es-video-duration">
										<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_DURATION');?>
										<?php echo $video->getDuration();?>
									</span>
									<?php } ?>

									<?php if ($video->hasLocation()) { ?>
									<span class="es-video-location">
										<?php echo $this->html('html.location', $video->getLocation());?>
									</span>
									<?php } ?>
								</div>

								<?php if ($video->hasLocation()) { ?>
									<?php echo $this->html('html.map', $video->getLocation());?>
								<?php } ?>

								<?php echo $this->render('module' , 'es-videos-before-video-description'); ?>

								<div class="es-video-desc t-lg-mb--md"><?php echo $video->getDescription(); ?></div>

								<?php echo $this->render('module' , 'es-videos-after-video-description'); ?>
							</div>

						</div>
						<div class="o-grid__cell o-grid__cell--auto-size">
							<?php if ($video->table->isFeatured()) { ?>
								<div class="es-label-state es-label-state--featured"
									data-es-provide="tooltip"
									data-placement="top"
									data-original-title="<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FEATURED');?>"
								>
									<i class="es-label-state__icon"></i>
								</div>

							<?php } ?>
						</div>
					</div>
				</div>

				<?php if ($this->config->get('video.layout.item.usertags') || ($tags && $this->config->get('video.layout.item.tags'))) { ?>
				<hr class="es-hr" />
				<div class="o-grid o-grid--gutters">

					<?php if ($this->config->get('video.layout.item.usertags')) { ?>
					<div class="o-grid__cell">
						<div class="es-video-tagging <?php echo !$usersTags ? ' is-empty' : '';?>">
							<b><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_PEOPLE_IN_THIS_VIDEO');?></b>
							<?php if ($video->canAddTag()) { ?>
							<span class="t-lg-ml--sm t-text--muted">
								&ndash;
								<a href="javascript:void(0);" data-video-tag><?php echo JText::_('COM_EASYSOCIAL_TAG_PEOPLE');?></a>
							</span>
							<?php } ?>

							<ul class="g-list-inline t-lg-mt--md <?php echo !$usersTags ? ' t-hidden' : '';?>" data-video-tag-wrapper>
								<?php echo $this->output('site/videos/item/tags.user'); ?>
							</ul>

							<div class="o-empty o-empty--clean o-empty--bg-no t-lg-mt--md" data-tags-empty>
								<div class="o-empty__content">
									<div class="o-empty__text"><?php echo JText::_('COM_ES_VIDEOS_NO_TAGS_YET'); ?></div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>

					<?php if ($tags && $this->config->get('video.layout.item.tags')) { ?>
					<div class="o-grid__cell">
						<div class="es-video-tagging <?php echo !$tags ? ' is-empty' : '';?>">
							<b><?php echo JText::_('COM_EASYSOCIAL_TAGS');?></b>
							<ul class="g-list-inline g-list-inline--space-right <?php echo !$tags ? ' t-hidden' : '';?>">
								<?php echo $this->output('site/videos/item/tags'); ?>
							</ul>
							<div class="o-empty o-empty--clean o-empty--bg-no t-lg-mt--md">
								<div class="o-empty__content">
									<div class="o-empty__text"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_NO_TAGS_AVAILABLE'); ?></div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<hr class="es-hr" />
				<?php } ?>

				<?php if ($navigation) { ?>
					<?php echo $this->html('media.navigation', $navigation, $uid, $type, 'video'); ?>
					<hr class="es-hr" />
				<?php } ?>

				<?php echo $this->render('module' , 'es-videos-after-video-tags'); ?>

				<div class="es-actions es-bleed--bottom" data-stream-actions>
					<div class="es-actions__item es-actions__item-action">
						<div class="es-actions-wrapper">
							<ul class="es-actions-list">

								<?php if ($this->my->id) { ?>
									<?php if ($likes->hasReactions()) { ?>
									<li><?php echo $likes->button(); ?></li>
									<?php } ?>

									<?php if ($this->config->get('repost.enabled')) { ?>
										<li><?php echo $repost->button(); ?></li>
									<?php } ?>
								<?php } ?>

								<?php if ($reports->canReport()) { ?>
									<li><?php echo $reports->html(); ?></li>
								<?php } ?>

								<li><?php echo $sharing->html(false); ?></li>

								<?php if ($this->config->get('video.layout.item.embed')) { ?>
									<li>
										<a href="javascript:void(0);" data-video-embed><?php echo JText::_('COM_ES_EMBED'); ?></a>
									</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<div class="es-actions__item es-actions__item-stats">
						<?php echo $likes->html(); ?>
					</div>
					<div class="es-actions__item es-actions__item-comment">
						<?php echo $comments->getHTML();?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->render('module' , 'es-videos-before-other-videos'); ?>

		<div class="es-apps-entry-section">
			<div class="es-apps-entry-section__content">
				<?php if ($this->config->get('video.layout.item.recent') && $otherVideos) { ?>
				<div class="es-video-other">
					<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_VIDEOS_OTHER_VIDEOS'); ?>

					<div class="es-cards es-cards--3">
						<?php foreach ($otherVideos as $otherVideo) { ?>
							<?php echo $this->loadTemplate('site/videos/default/item', array('video' => $otherVideo, 'uid' => '', 'utype' => '', 'browseView' => true, 'returnUrl' => $returnUrl, 'from' => 'listing')); ?>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<?php echo $this->render('module' , 'es-videos-after-other-videos'); ?>
	</div>
</div>

</div>
