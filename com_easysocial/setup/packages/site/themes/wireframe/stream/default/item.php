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
<li class="es-stream-item <?php echo $stream->display == SOCIAL_STREAM_DISPLAY_FULL ? ' es-stream-full' : ' es-stream-mini';?>
	es-context-<?php echo $stream->context; ?>
	<?php echo $stream->isModerated() ? ' is-moderated' : '';?>
	<?php echo $stream->sticky ? ' is-sticky' : '';?>
	<?php echo $stream->bookmarked ? ' is-bookmarked' : '';?>"
	data-id="<?php echo $stream->uid;?>"
	data-hidden="0"
	data-context="<?php echo $stream->context; ?>"
	data-actor="<?php echo $stream->actor->id; ?>"
	data-appid="<?php echo $stream->appid; ?>"
	data-stream-item
	data-view="<?php echo $view; ?>"
>
	<div class="es-stream" data-wrapper>
		<?php if ($stream->hasLastAction()) { ?>
			<div class="es-stream-header t-text--muted es-bleed--top">
				<i class="far fa-clock"></i>&nbsp; <?php echo $stream->getLastAction(); ?>
			</div>
		<?php } ?>

		<div class="es-stream-meta">
			<?php if ($stream->canViewActions()) { ?>
			<div class="es-stream-control o-btn-group">
				<a class="btn-control" href="javascript:void(0);" data-es-toggle="dropdown">
					<i class="i-chevron i-chevron--down"></i>
				</a>

				<ul class="dropdown-menu dropdown-menu-right" data-stream-actions>
					<?php if ($stream->canCopyLink()) { ?>
					<li data-copylink data-clipboard-link="<?php echo SOCIAL_JOOMLA_URI . $stream->getPermalink(); ?>">
						<a href="javascript:void(0);"><?php echo JText::_('COM_ES_STREAM_ITEM_COPY_LINK');?></a>
					</li>
					<li class="divider"></li>
					<?php } ?>

					<?php if ($stream->canFavourite()) { ?>
					<li class="add-bookmark" data-bookmark-add>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_BOOKMARK');?></a>
					</li>
					<li class="remove-bookmark" data-bookmark-remove>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_REMOVE_BOOKMARK');?></a>
					</li>
					<?php } ?>

					<?php if ($stream->canSticky()) { ?>
					<li class="add-sticky" data-sticky-add>
						<a href="javascript:void(0);">
						<?php if ($stream->isCluster()) { ?>
							<?php echo JText::_('COM_EASYSOCIAL_STREAM_PIN_ITEM_' . strtoupper($stream->cluster_type));?>
						<?php } else { ?>
							<?php echo JText::_('COM_EASYSOCIAL_STREAM_PIN_ITEM_PROFILE');?>
						<?php } ?>
						</a>
					</li>
					<li class="remove-sticky" data-sticky-remove>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_UNPIN_ITEM');?></a>
					</li>
					<?php } ?>

					<?php if ($stream->editablepoll) { ?>
					<li class="divider"></li>
					<li data-polls-edit>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_EDIT_POLLS');?></a>
					</li>
					<?php } ?>

					<?php if ($stream->editable) { ?>
						<?php if (!$stream->isScheduled()) { ?>
							<li class="divider"></li>
						<?php } ?>

						<li data-edit>
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_EDIT');?></a>
						</li>
					<?php } ?>

					<?php if ($stream->hasObjectEditLink()) { ?>
					<li data-edit>
						<a href="<?php echo $stream->getObjectEditLink(); ?>"><?php echo JText::_('COM_EASYSOCIAL_STREAM_EDIT');?></a>
					</li>
					<?php } ?>

					<?php if ($stream->canHide()) { ?>
						<li data-hide data-type="item">
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_HIDE');?></a>
						</li>

						<?php if( $this->my->id != $stream->actor->id ) { ?>
							<li data-hide data-type="actor" data-multiple="1">
								<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_HIDE_ACTOR');?></a>
							</li>
						<?php } ?>

						<?php if ($stream->context != 'story') { ?>
						<li data-hide data-type="context" data-multiple="1">
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_HIDE_APP');?></a>
						</li>
						<?php } ?>
					<?php } ?>

					<?php if ($stream->canReport()) { ?>
					<li class="divider"></li>
					<li>
						<?php echo ES::reports()->getForm('com_easysocial', SOCIAL_TYPE_STREAM, $stream->uid, JText::sprintf('COM_EASYSOCIAL_STREAM_REPORT_ITEM_TITLE', $stream->actor->getName()), JText::_('COM_EASYSOCIAL_STREAM_REPORT_ITEM'), '', JText::_('COM_EASYSOCIAL_STREAM_REPORT_ITEM_DESC'), ESR::stream([
								'id' => $stream->uid,
								'layout' => 'item',
								'external' => true
							]), false, $stream->actor->id); ?>
					</li>
					<?php } ?>

					<?php if ($stream->canDelete()) { ?>
					<li class="divider"></li>
					<li data-delete>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_DELETE_STORY_POST');?></a>
					</li>
					<?php } ?>

					<?php if ($stream->canPublish()) { ?>
					<li class="divider"></li>
					<li data-publish>
						<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_STREAM_PUBLISH_POST');?></a>
					</li>
					<?php } ?>

					<?php echo $this->render('widgets', 'user', 'stream', 'streamActions', array($this->my, $stream)); ?>
				</ul>
			</div>
			<?php } ?>

			<div class="o-flag">
				<div class="o-flag__image o-flag--top es-stream-avatar-wrap">
					<div class="es-stream-avatar">
						<?php echo $this->html('avatar.' . $stream->getActorAlias()->getType(), $stream->getActorAlias(), 'default', true, true); ?>
					</div>
				</div>

				<div class="o-flag__body">

					<?php if ($this->config->get('stream.pin.enabled')) { ?>
					<div class="es-stream-sticky-label" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_STREAM_YOU_HAVE_PINNED_THIS_STREAM');?>">
						<i class="fa fa-thumbtack"></i>
					</div>
					<?php } ?>

					<?php if ($this->config->get('stream.bookmarks.enabled')) { ?>
					<div class="es-stream-bookmark-label" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_BOOKMARK_YOU_HAVE_BOOKMARKED_THIS_STREAM');?>">
						<i class="fa fa-bookmark"></i>
					</div>
					<?php } ?>

					<div class="es-stream-title">
						<?php echo $stream->title; ?>

						<?php if ($stream->isMini()) { ?>
						<time> &mdash; <?php echo $stream->friendlyDate; ?></time>
						<?php } ?>

						<?php if ($stream->isScheduled()) { ?>
							<span class="o-label o-label--danger">
								<?php echo JText::_('COM_ES_STORY_SCHEDULED');?>
							</span>
						<?php } ?>
					</div>

					<?php if ($stream->display == SOCIAL_STREAM_DISPLAY_FULL) { ?>
					<div class="es-stream-meta-footer">

						<?php if ($stream->isScheduled()) { ?>
							<span class="es-scheduled-text t-xs-mr--md"><?php echo JText::_('COM_ES_STREAM_SCHEDULE_FOR');?></span>
							<time data-stream-schedule-date><?php echo $stream->scheduled;?></time>
						<?php } else { ?>
							<time>
								<a href="<?php echo $stream->getPermalink(); ?>"><?php echo $stream->friendlyDate; ?></a>
							</time>
						<?php } ?>

						<?php if ($stream->isEdited()) { ?>
						<span class="es-edit-text" data-es-provide="tooltip"
							data-original-title="<?php echo JText::sprintf('COM_EASYSOCIAL_STREAM_LAST_EDITED_ON', ES::date($stream->edited)->format(JText::_('DATE_FORMAT_LC2'), true));?>">
							<b>&middot;</b> <?php echo JText::_('COM_EASYSOCIAL_STREAM_EDITED');?>
						</span>
						<?php } ?>

						<span class="es-editing-text t-xs-mr--md"><?php echo $stream->isScheduled() ? JText::_('COM_ES_STREAM_SCHEDULE_EDITING') : JText::_('COM_EASYSOCIAL_STREAM_EDITING');?></span>

						<?php if ($stream->hasPrivacy() && !$stream->isScheduled()) { ?>
						<span data-breadcrumb=".">
							<?php echo $stream->getPrivacyHtml();?>
						</span>
						<?php } ?>


					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php if ($stream->isFull()) { ?>

			<div class="es-stream-content <?php echo ($stream->content || $stream->getMetaHtml()) ? '' : ' t-hidden'; ?> es-story--bg-<?php echo $stream->background_id;?>" data-contents>
			<?php if ($stream->content || $stream->getMetaHtml()) { ?>
				<?php echo $stream->content; ?>
				<?php echo $stream->getMetaHtml(); ?>
			<?php } ?>
			</div>

			<?php if ($stream->showTranslateButton() && $showTranslations) { ?>
			<div class="es-stream-translations">
				<div class="o-loader o-loader--inline o-loader--sm es-stream-translations__loader"></div>
				<div data-translations></div>
				<a href="javascript:void(0);" data-translate><?php echo JText::_('COM_EASYSOCIAL_STREAM_SEE_TRANSLATION');?></a>
			</div>
			<?php } ?>

			<?php if ($stream->isEditable()) { ?>
			<div class="es-stream-editor" data-editor></div>
			<?php } ?>

			<?php if (!empty($stream->location) && ($this->config->get('stream.location.style') === 'inline' || $this->config->get('location.provider') == 'osm')) { ?>
				<div class="es-stream-embed is-maps t-lg-mb--md t-hidden" data-location-preview>
					<?php echo $this->loadTemplate('site/stream/default/location', array('stream' => $stream, 'provider' => $this->config->get('location.provider'), 'isEdit' => false)); ?>
				</div>
			<?php } ?>

			<div data-gif>
				<?php if ($stream->hasGiphy()) { ?>
					<?php echo $this->output('site/giphy/preview/display', array('giphy' => $stream->getGiphy())); ?>
				<?php } ?>
			</div>
			<?php if ($stream->hasPreview()) { ?>
				<div class="es-stream-preview" data-preview>
					<?php echo $stream->preview; ?>
				</div>
			<?php } ?>
		<?php } ?>

		<?php echo $stream->actions; ?>

		<div class="es-moderated-note pull-left">
			<span class="o-label o-label--warning-o">
				<?php echo JText::_('COM_EASYSOCIAL_POST_IS_PENDING_MODERATION');?>
			</span>
		</div>

	</div>

	<div class="es-stream-published es-stream-published-notice">
		<?php if ($stream->isScheduled()) { ?>
			<?php echo JText::_('COM_ES_STREAM_SCHEDULED_ITEM_PUBLISHED'); ?>
		<?php } else { ?>
			<?php echo JText::_('COM_EASYSOCIAL_STREAM_ITEM_PUBLISHED'); ?>
		<?php } ?>
	</div>

	<?php echo $this->render('module', 'es-' . $view . '-between-streams'); ?>
</li>
