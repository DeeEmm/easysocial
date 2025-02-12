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
<div class="es-cards__item" data-audio-item data-id="<?php echo $audio->id;?>">
	<div class="es-card <?php echo ($audio->table->isFeatured()) ? 'is-featured' : ''; ?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($lists && $audio->isUpload()) { ?>
				<div class="es-card__addplaylist-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-es-toggle="dropdown" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_AUDIO_ADD_TO_PLAYLIST'); ?>">
							<i class="fa fa-plus"></i>
						</a>
						<ul class="dropdown-menu">
							<?php foreach ($lists as $list) { ?>
							<li>
								<a href="javascript:void(0);" class="es-card__addplaylist-item"
									data-playlist-item
									data-id="<?php echo $list->id;?>"
									>
									<span class="es-card__addplaylist-title pull-left"><?php echo $list->get('title');?></span>

									<?php if (in_array($audio->id, $list->items)) { ?>
										<i class="fa fa-check pull-right"></i>
									<?php } ?>
								</a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
				<?php if ($audio->canFeature() || $audio->canUnfeature() || $audio->canDelete() || $audio->canEdit() || $audio->canDownload()) { ?>
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
						<ul class="dropdown-menu">
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
								<a href="<?php echo $audio->getEditLink();?>"><?php echo JText::_('COM_ES_AUDIO_EDIT_AUDIO'); ?></a>
							</li>
							<?php } ?>

							<?php if ($audio->canDelete()) { ?>
							<li class="divider"></li>

							<li>
								<a href="javascript:void(0);" data-audio-delete data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_AUDIO_DELETE_AUDIO');?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>

			<?php if ($audio->isUpload()) { ?>
				<?php echo $audio->getUploadEmbedCodes(); ?>
			<?php } else { ?>
				<div class="es-audio-container is-<?php echo strtolower($audio->getLinkProvider()); ?> <?php echo $audio->isSpotifyPodcast() ? 'is-podcast' : ''; ?>">
					<?php echo $audio->getLinkEmbedCodes(); ?>
				</div>

			<?php } ?>
		</div>

		<div class="es-card__bd es-card--border">
			<div class="es-label-state es-label-state--featured es-card__state"><i class="es-label-state__icon"></i></div>
			<div class="es-card__title">
				<a class="" href="<?php echo $audio->getPermalink(true, $uid, $utype, $from);?>"><?php echo $audio->getTitle();?></a>
			</div>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list--horizontal">
					<li class="g-list__item">
						<a href="<?php echo $audio->getGenre()->getPermalink(true, $uid, $utype);?>">
							<?php echo JText::_($audio->getGenre()->title);?>
						</a>
					</li>

					<?php if ($browseView) { ?>
					<li class="t-lg-p--sm">&bull;</li>
					<li class="g-list__item">
						<?php echo $this->html('html.user', $audio->creator, true);?>
					</li>
					<?php } ?>
				</ol>
			</div>
		</div>


		<div class="es-card__ft es-card--border">
			<div class="t-lg-pull-left">
				<ul class="g-list-inline g-list-inline--space-right">
					<?php if ($this->config->get('audio.layout.item.hits')) { ?>
					<li>
						<i class="fa fa-headphones"></i> <?php echo $audio->getHits();?>
					</li>
					<?php } ?>

					<li>
						<i class="fa fa-heart"></i> <?php echo $audio->getLikesCount();?>
					</li>
					<li>
						<i class="fa fa-comment"></i> <?php echo $audio->getCommentsCount();?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
