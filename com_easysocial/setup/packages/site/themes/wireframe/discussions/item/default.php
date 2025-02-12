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
<div class="es-container" data-discussion-item data-uid="<?php echo $cluster->id;?>"
		data-type="<?php echo $cluster->getType();?>" data-id="<?php echo $discussion->id;?>" data-sorting="<?php echo strtolower($sorting);?>">
	<div class="es-content">

		<div class="es-apps-entry es-app-discussions <?php echo $answer ? ' is-resolved' : '';?> <?php echo !$replies ? ' is-unanswered' : '';?> <?php echo $discussion->lock ? ' is-locked' : '';?>" data-discussion-item-wrapper>

			<div class="es-entry-actionbar es-island">
				<div class="o-grid-sm">
					<div class="o-grid-sm__cell">
						<a href="<?php echo $cluster->getAppPermalink('discussions');?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_ES_BACK'); ?></a>
					</div>

					<?php if ($discussion->canAccessDropdownAction()) { ?>
					<div class="o-grid-sm__cell">
						<div class="o-btn-group pull-right">
							<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
								<i class="fa fa-ellipsis-h"></i>
							</button>

							<ul class="dropdown-menu dropdown-menu-right">
								<?php if ($discussion->canLock()) { ?>
										<li class="discussion-unlock-action">
											<a href="javascript:void(0);" data-unlock><?php echo JText::_('APP_GROUP_DISCUSSIONS_UNLOCK'); ?></a>
										</li>
										<li class="discussion-lock-action">
											<a href="javascript:void(0);" data-lock><?php echo JText::_('APP_GROUP_DISCUSSIONS_LOCK'); ?></a>
										</li>
								<?php } ?>

								<?php if ($discussion->canEdit()) { ?>
								<li>
									<a href="<?php echo $discussion->getEditPermalink();?>"><?php echo JText::_('APP_GROUP_DISCUSSIONS_EDIT'); ?></a>
								</li>
								<?php } ?>

								<?php if ($discussion->canDelete()) { ?>
								<li class="divider"></li>
								<li>
									<a href="javascript:void(0);" data-delete><?php echo JText::_('APP_GROUP_DISCUSSIONS_DELETE'); ?></a>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<?php } ?>

				</div>
			</div>


			<div class="es-apps-entry-section es-island">
				<div class="es-apps-entry">
					<div class="es-apps-entry__hd">
						<a href="<?php echo $discussion->getPermalink();?>" class="es-apps-item__title"><?php echo $discussion->_('title');?></a>
					</div>

					<div class="es-apps-entry__ft es-bleed--middle">
						<div class="o-grid">
							<div class="o-grid__cell">
								<div class="es-apps-entry__meta">
									<div class="es-apps-entry__meta-item">
										<ol class="g-list-inline g-list-inline--dashed">
											<li>
												<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.' . $author->getType(), $author->id); ?>
											</li>
											<li>
												<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($discussion->created)->format(JText::_('DATE_FORMAT_LC1'));?>
											</li>

											<li class="g-list__item">
												<?php echo JText::sprintf(ES::string()->computeNoun('APP_GROUP_DISCUSSIONS_HITS', $discussion->hits), $discussion->hits); ?>
											</li>
											<li class="g-list__item">
												<?php echo JText::sprintf(ES::string()->computeNoun('APP_GROUP_DISCUSSIONS_PARTICIPANTS', count($participants)), count($participants)); ?>
											</li>
											<li class="g-list__item">
												<?php echo JText::sprintf(ES::string()->computeNoun('APP_GROUP_DISCUSSIONS_TOTAL_REPLIES', $discussion->total_replies), '<span data-reply-count>' . $discussion->total_replies . '</span>'); ?>
											</li>

											<?php if ($answer) { ?>
											<li class="g-list__item">
												<a href="#reply-<?php echo $answer->id ?>"><?php echo JText::_('APP_GROUP_DISCUSSIONS_ANSWERED'); ?></a>
												<?php echo JText::sprintf('APP_GROUP_DISCUSSIONS_BY', '<a href="' . $answer->author->getPermalink() . '">' . $answer->author->getName() . '</a>'); ?>
											</li>
											<?php } ?>
										</ol>
									</div>
								</div>
							</div>
							<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
								<div class="es-apps-entry__state">
									<span class="o-label o-label--success-o label-resolved"><?php echo JText::_('APP_GROUP_DISCUSSIONS_RESOLVED'); ?></span>
									<span class="o-label o-label--warning-o label-locked"><i class="fa fa-lock locked-icon"></i> <?php echo JText::_('APP_GROUP_DISCUSSIONS_LOCKED'); ?></span>
									<span class="o-label o-label--danger-o label-unanswered"><?php echo JText::_('APP_GROUP_DISCUSSIONS_UNANSWERED'); ?></span>
								</div>
							</div>
						</div>
					</div>

					<div class="es-apps-entry__bd">
						<div class="es-apps-entry__desc">
							<?php echo $discussion->getContent();?>
						</div>
					</div>

					<div class="es-actions es-bleed--bottom" data-stream-actions>
						<div class="es-actions__item es-actions__item-action">
							<div class="es-actions-wrapper">
								<ul class="es-actions-list">
									<li>
										<?php echo $discussion->likes->button(true); ?>
									</li>
								</ul>
							</div>
						</div>
						<div class="es-actions__item es-actions__item-stats">
							<?php echo $discussion->likes->html(); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="es-apps-entry-section">
				<div class="es-apps-entry-section__title">
					<?php echo $this->html('html.snackbar', 'APP_GROUP_DISCUSSIONS_PARTICIPANTS'); ?>
				</div>
				<div class="es-apps-entry-section__content es-island">
					<ul class="g-list-inline">
						<?php foreach ($participants as $participant) { ?>
						<li class="t-lg-mb--md t-lg-mr--md">
							<?php echo $this->html('avatar.' . $participant->getType(), $participant); ?>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>

			<?php if ($discussion->canReply() && strtolower($sorting) == 'desc') { ?>
			<div class="es-apps-entry-section">
				<div class="es-apps-entry-section__title">
					<?php echo $this->html('html.snackbar', 'APP_GROUP_DISCUSSIONS_YOUR_RESPONSE'); ?>
				</div>

				<div class="es-apps-entry-section__content es-island">
					<div class="o-alert o-alert--warning locked-notice">
						<i class="fa fa-lock"></i>
						<?php echo JText::_('APP_GROUP_DISCUSSIONS_IS_LOCKED'); ?>
					</div>

					<form class="es-app-discussion-reply-form" data-reply-form data-new-reply>
						<div class="alert alert-dismissable alert-error alert-empty">
							<button type="button" class="close" data-es-dismiss="alert">×</button>
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_EMPTY_ERROR'); ?>
						</div>

						<?php echo ES::bbcode()->editor('reply_content', '' , array('files' => $files , 'uid' => $cluster->id , 'type' => $cluster->getType(), 'controllerName' => $cluster->getTypePlural()) , array('data-reply-editor' => '', 'data-reply-editor-new' => '')); ?>

						<div class="o-form-actions es-bleed--bottom">
							<button type="button" class="pull-right btn btn-es-primary-o" data-reply-submit><?php echo JText::_('APP_GROUP_DISCUSSIONS_SUBMIT_REPLY'); ?></a>
						</div>
					</form>
				</div>
			</div>
			<?php } ?>

			<div class="es-apps-entry-section <?php echo !$replies ? ' is-empty' : '';?>" data-replies-wrapper>
				<div class="es-apps-entry-section__title">
					<?php echo $this->html('html.snackbar', JText::_('APP_GROUP_DISCUSSIONS_REPLIES') . '(<span data-reply-count>' . $discussion->total_replies . '</span>)'); ?>
				</div>

				<div class="es-apps-entry-section__content es-app-discussion-reply-list" data-reply-list>
					<?php echo $this->html('html.emptyBlock', 'APP_GROUP_DISCUSSIONS_REPLIES_EMPTY', 'fa-database', false, true); ?>

					<?php foreach ($replies as $reply) { ?>
						<?php echo $this->loadTemplate('site/discussions/item/default.item' , array('reply' => $reply , 'answer' => $answer, 'cluster' => $cluster, 'question' => $discussion, 'files' => $files)); ?>
					<?php } ?>
				</div>
			</div>

			<?php if ($pagination) { ?>
				<div class="es-apps-pagination">
				<?php echo $pagination->getListFooter('site');?>
				</div>
			<?php } ?>

			<?php if ($discussion->canReply() && strtolower($sorting) == 'asc') { ?>
			<div class="es-apps-entry-section">
				<div class="es-apps-entry-section__title">
					<?php echo $this->html('html.snackbar', 'APP_GROUP_DISCUSSIONS_YOUR_RESPONSE'); ?>
				</div>

				<div class="es-apps-entry-section__content es-island">
					<div class="o-alert o-alert--warning locked-notice">
						<i class="fa fa-lock"></i>
						<?php echo JText::_('APP_GROUP_DISCUSSIONS_IS_LOCKED'); ?>
					</div>

					<form class="es-app-discussion-reply-form" data-reply-form data-new-reply>
						<div class="alert alert-dismissable alert-error alert-empty">
							<button type="button" class="close" data-es-dismiss="alert">×</button>
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_EMPTY_ERROR'); ?>
						</div>

						<?php echo ES::bbcode()->editor('reply_content', '' , array('files' => $files , 'uid' => $cluster->id , 'type' => $cluster->getType(), 'controllerName' => $cluster->getTypePlural()) , array('data-reply-editor' => '', 'data-reply-editor-new' => '')); ?>

						<div class="o-form-actions es-bleed--bottom">
							<button type="button" class="pull-right btn btn-es-primary-o" data-reply-submit><?php echo JText::_('APP_GROUP_DISCUSSIONS_SUBMIT_REPLY'); ?></a>
						</div>
					</form>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
