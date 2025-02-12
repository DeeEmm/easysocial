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

<?php echo $audio->getMiniHeader();?>

<div class="es-container es-audios" data-audio-item data-id="<?php echo $audio->id;?>">
	<div class="es-content">
		<?php echo $this->render('module' , 'es-audios-before-audio'); ?>

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $backLink;?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_ES_AUDIO_BACK_TO_AUDIO'); ?></a>
				</div>

				<?php if (($lists && $audio->isUpload()) || ($audio->canFeature() || $audio->canUnfeature() || $audio->canDelete() || $audio->canEdit())){ ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right" role="group">
						<?php if ($lists && $audio->isUpload()) { ?>
							<div class="o-btn-group" role="group">
								<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown" data-placement="left" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_AUDIO_ADD_TO_PLAYLIST'); ?>">
									 <i class="fa fa-plus"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-right">
									<?php foreach ($lists as $list) { ?>
									<li>
										<a class="addplaylist-item" href="javascript:void(0);" data-playlist-item data-id="<?php echo $list->id;?>">

											<span class="pull-left"><?php echo $list->get('title');?></span>

											<?php if (in_array($audio->id, $list->items)) { ?>
												<i class="fa fa-check pull-right"></i>
											<?php } ?>
										</a>
									</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>

						<?php if ($audio->canFeature() || $audio->canUnfeature() || $audio->canDelete() || $audio->canEdit() || $audio->canDownload()) { ?>
							<div class="o-btn-group" role="group">
								<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
									 <i class="fa fa-ellipsis-h"></i>
									 <span class="t-hidden"><?php echo JText::_('COM_EASYSOCIAL_MANAGE');?></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-right">
									<?php if ($audio->canFeature()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-feature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_AUDIO_FEATURE_AUDIO');?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canUnfeature()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-unfeature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_AUDIO_UNFEATURE_AUDIO');?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canDownload()) { ?>
									<li>
										<a href="<?php echo ESR::audios(array('layout' => 'download', 'id' => $audio->getAlias()));?>" data-audio-download><?php echo JText::_('COM_ES_AUDIO_DOWNLOAD_AUDIO');?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canEdit()) { ?>
									<li>
										<a href="<?php echo $audio->getEditLink();?>"><?php echo JText::_('COM_EASYSOCIAL_EDIT'); ?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canDelete()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-delete><?php echo JText::_('COM_EASYSOCIAL_DELETE');?></a>
									</li>
									<?php } ?>
								</ul>
							</div>

							<span class="es-audio-manage dropdown_ pull-right pl-10 t-hidden">
								<a href="javascript:void(0);" class="dropdown-toggle_" data-es-toggle="dropdown"></a>
								<ul class="dropdown-menu dropdown-arrow-topright">
									<?php if ($audio->canFeature()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-feature><?php echo JText::_('COM_ES_AUDIO_FEATURE_AUDIO');?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canUnfeature()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-unfeature><?php echo JText::_('COM_ES_AUDIO_UNFEATURE_AUDIO');?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canEdit()) { ?>
									<li>
										<a href="<?php echo $audio->getEditLink();?>"><?php echo JText::_('COM_EASYSOCIAL_EDIT'); ?></a>
									</li>
									<?php } ?>

									<?php if ($audio->canDelete()) { ?>
									<li>
										<a href="javascript:void(0);" data-audio-delete><?php echo JText::_('COM_EASYSOCIAL_DELETE');?></a>
									</li>
									<?php } ?>
								</ul>
							</span>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry-section__content">
				<div class="es-audio-single">
					<div class="es-audio-content-body">
						<?php if ($audio->isPendingProcess()) { ?>
						<div class="alert alert-info">
							<?php echo JText::_('COM_ES_AUDIO_ITEM_PENDING_INFO');?>
						</div>
						<?php } ?>

						<?php if ($audio->isUpload()) { ?>
							<?php echo $audio->getUploadEmbedCodes(); ?>
						<?php } else { ?>
							<div class="es-audio-container is-<?php echo strtolower($audio->getLinkProvider()); ?> <?php echo $audio->isSpotifyPodcast() ? 'is-podcast' : ''; ?>">
								<?php echo $audio->getLinkEmbedCodes(); ?>
							</div>

						<?php } ?>
					</div>

					<?php echo $this->render('module' , 'es-audios-after-audio'); ?>

					<div class="t-lg-mt--lg">

						<div class="o-grid">
							<div class="o-grid__cell o-grid__cell--text-overflow">
								<div class="es-audio-context">
									<h1 class="es-audio-title single"><?php echo $audio->getTitle();?></h1>
									<div class="es-audio-meta t-lg-mb--md t-text--muted">
										<span>
											<?php echo JText::sprintf('COM_ES_AUDIO_AUDIO_ARTIST', $audio->getArtist()); ?>
										</span>

										<span>
											<?php echo JText::_('COM_ES_AUDIO_AUDIO_ALBUM'); ?> <?php echo $audio->getAlbum(); ?>
										</span>

										<?php if ($this->config->get('audio.layout.item.hits')) { ?>
										<span>
											<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_HITS', $audio->getHits()); ?>
										</span>
										<?php } ?>

										<span>
											<?php echo JText::_('COM_ES_AUDIO_AUDIO_GENRE'); ?> <a href="<?php echo $audio->getGenre()->getPermalink(true, $uid, $type);?>"><?php echo JText::_($audio->getGenre()->title);?></a>
										</span>

										<?php if ($this->config->get('audio.layout.item.duration')) { ?>
										<span class="es-audio-duration">
											<?php echo JText::_('COM_ES_AUDIO_AUDIO_DURATION');?>
											<?php echo $audio->getDuration();?>
										</span>
										<?php } ?>

										<span>
										<?php echo JText::sprintf('COM_ES_AUDIO_AUDIO_UPLOADED_BY', $this->html('html.' . $creator->getType(), $creator), $audio->getCreatedDate()->format(JText::_('COM_EASYSOCIAL_DATE_DMY'))); ?>
										</span>
									</div>

									<?php echo $this->render('module' , 'es-audios-before-audio-description'); ?>

									<div class="t-lg-mb--md"><?php echo $this->html('string.truncate', $audio->getDescription(), ES::config()->get('stream.content.truncatelength'), '', false, true);?></div>

									<?php echo $this->render('module' , 'es-audios-after-audio-description'); ?>
								</div>
							</div>

							<?php if ($audio->table->isFeatured()) { ?>
							<div class="o-grid__cell o-grid__cell--auto-size">
								<div class="es-label-state es-label-state--featured" data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('COM_ES_AUDIO_FEATURED');?>">
									<i class="es-label-state__icon"></i>
								</div>
							</div>
							<?php } ?>

						</div>
					</div>

					<?php if ($this->config->get('audio.layout.item.usertags') || ($tags && $this->config->get('audio.layout.item.tags'))) { ?>
						<hr class="es-hr">

						<div class="o-grid o-grid--gutters">
							<?php if ($this->config->get('audio.layout.item.usertags')) { ?>
							<div class="o-grid__cell">
								<div class="es-audio-tagging <?php echo !$usersTags ? ' is-empty' : '';?>">
									<b><?php echo JText::_('COM_ES_AUDIO_PEOPLE_IN_THIS_AUDIO');?></b>
									<?php if ($audio->canAddTag()) { ?>
									<span class="t-lg-ml--sm t-text--muted">
										&ndash;
										<a href="javascript:void(0);" data-audio-tag><?php echo JText::_('COM_EASYSOCIAL_TAG_PEOPLE');?></a>
									</span>
									<?php } ?>
									<ul class="g-list-inline t-lg-mt--md <?php echo !$usersTags ? ' t-hidden' : '';?>" data-audio-tag-wrapper>
										<?php echo $this->output('site/audios/item/tags.user'); ?>
									</ul>
									<div class="o-empty o-empty--clean o-empty--bg-no t-lg-mt--md" data-tags-empty>
										<div class="o-empty__content">
											<div class="o-empty__text"><?php echo JText::_('COM_ES_AUDIO_NO_TAGS_YET'); ?></div>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>

							<?php if ($tags && $this->config->get('audio.layout.item.tags')) { ?>
							<div class="o-grid__cell">
								<div class="es-audio-tagging <?php echo !$tags ? ' is-empty' : '';?>">
									<b><?php echo JText::_('COM_EASYSOCIAL_TAGS');?></b>
									<ul class="g-list-inline g-list-inline--space-right <?php echo !$tags ? ' t-hidden' : '';?>">
										<?php echo $this->output('site/audios/item/tags'); ?>
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
					<?php } ?>

					<?php if ($navigation) { ?>
						<?php echo $this->html('media.navigation', $navigation, $uid, $type, 'audio'); ?>
						<hr class="es-hr" />
					<?php } ?>

					<?php echo $this->render('module' , 'es-audios-after-audio-tags'); ?>

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
		</div>

		<div class="es-apps-entry-section">
			<div class="es-apps-entry-section__content">
				<?php echo $this->render('module' , 'es-audios-before-other-audios'); ?>

				<?php if ($this->config->get('audio.layout.item.recent') && $otherAudios) { ?>
				<div class="es-audio-other">
					<?php echo $this->html('html.snackbar', 'COM_ES_AUDIO_OTHER_AUDIO'); ?>

					<div class="es-cards es-cards--2">
						<?php foreach ($otherAudios as $otherAudio) { ?>
							<?php echo $this->loadTemplate('site/audios/default/item', array('audio' => $otherAudio, 'uid' => '', 'utype' => '', 'browseView' => true, 'returnUrl' => $returnUrl, 'from' => 'listing', 'lists' => $lists)); ?>
						<?php } ?>
					</div>
				</div>
				<?php } ?>

				<?php echo $this->render('module' , 'es-audios-after-other-audios'); ?>
			</div>
		</div>
	</div>
</div>


</div>
