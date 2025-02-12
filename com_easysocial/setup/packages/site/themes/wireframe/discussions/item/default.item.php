<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-app-discussion-reply-item es-island <?php echo $answer && $answer->id == $reply->id ? ' is-answer-item' : '';?>" data-reply-item data-id="<?php echo $reply->id;?>">
	<div class="es-apps-item__hd">
		<div class="o-flag">
			<div class="o-flag__image">
				<?php echo $this->html('avatar.' . $reply->author->getType(), $reply->author, 'sm'); ?>
			</div>
			<div class="o-flag__body">
				<?php echo $this->html('html.' . $reply->author->getType(), $reply->author);?>
				<a id="reply-<?php echo $reply->id;?>"></a>
			</div>
		</div>

		<?php if ($reply->canAcceptAnswer($answer) || $reply->canEditReply() || $reply->canDeleteReply()) { ?>
		<div class="es-apps-item__action">
			<div class="pull-right o-btn-group">
				<a href="javascript:void(0);" class="btn btn-es-default-o btn-xs dropdown-toggle_" data-es-toggle="dropdown">
					<i class="fa fa-ellipsis-h"></i>
				</a>

				<ul class="dropdown-menu dropdown-menu-user messageDropDown">
					<li>
						<a href="javascript:void(0);" data-reply-accept-answer class="<?php echo $reply->canAcceptAnswer($answer) ? '' : 't-hidden'; ?>"><?php echo JText::_('APP_GROUP_DISCUSSIONS_ACCEPT_ANSWER'); ?></a>
					</li>

					<li>
						<a href="javascript:void(0);" data-reply-reject-answer class="<?php echo $reply->canRejectAnswer($answer) ? '' : 't-hidden'; ?>"><?php echo JText::_('APP_DISCUSSIONS_REJECT_ANSWER'); ?></a>
					</li>

					<li class="divider <?php echo ($reply->canEditReply() || $reply->canDeleteReply()) && ($reply->canAcceptAnswer($answer) || $reply->canRejectAnswer($answer)) ? '' : 't-hidden'; ?>"></li>

					<?php if ($reply->canEditReply()) { ?>
					<li>
						<a href="javascript:void(0);" data-reply-edit><?php echo JText::_('APP_GROUP_DISCUSSIONS_EDIT_REPLY'); ?></a>
					</li>
					<?php } ?>
					<?php if ($reply->canDeleteReply()) { ?>
					<li>
						<a href="javascript:void(0);" data-reply-delete><?php echo JText::_('APP_GROUP_DISCUSSIONS_DELETE_REPLY'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="es-apps-item__ft es-bleed--middle">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<i class="far fa-clock"></i> <?php echo ES::date($reply->created)->toLapsed();?>
							</li>
						</ol>
					</div>
				</div>
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
				<div class="es-apps-item__state">
					<span class="o-label o-label--success-o es-app-discussion-reply-answer-state"><?php echo JText::_('COM_EASYSOCIAL_APP_DISCUSSIONS_ACCEPTED_ANSWER');?></span>
				</div>
			</div>
		</div>
	</div>

	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc" data-reply-preview>
			<?php echo $reply->getContent(); ?>
		</div>

		<form class="reply-form reply-form-edit t-hidden" data-reply-form>
			<div class="alert alert-dismissable alert-error alert-empty" style="display:none;">
				<button type="button" class="close" data-es-dismiss="alert">×</button>
				<?php echo JText::_('APP_GROUP_DISCUSSIONS_EMPTY_REPLY_ERROR'); ?>
			</div>

			<?php echo ES::bbcode()->editor('reply_content', $reply->content, array('files' => $files, 'uid' => $cluster->id, 'type' => SOCIAL_TYPE_GROUP), array('data-reply-editor' => '')); ?>

			<div class="o-form-actions">
				<button type="button" class="t-lg-pull-left btn btn-es-default-o" data-reply-edit-cancel><?php echo JText::_('COM_ES_CANCEL'); ?></a>
				<button type="button" class="t-lg-pull-right btn btn-es-primary-o" data-reply-edit-update><?php echo JText::_('COM_ES_UPDATE'); ?></a>
			</div>
		</form>
	</div>

	<div class="es-actions es-bleed--bottom" data-stream-actions>
		<div class="es-actions__item es-actions__item-action">
			<div class="es-actions-wrapper">
				<ul class="es-actions-list">
					<li>
						<?php echo $reply->likes->button('btn btn-es-default-o btn-xs t-lg-mt--lg t-lg-mb--md'); ?>
					</li>
				</ul>
			</div>
		</div>

		<div class="es-actions__item es-actions__item-stats">
			<?php echo $reply->likes->html(); ?>
		</div>
	</div>

</div>
