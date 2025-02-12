<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-comments-form" data-comments-form>
	<div class="es-form">
		<div class="o-alert o-alert--dismissible o-alert--warning t-hidden" data-comment-error>
			<button type="button" class="o-alert__close" data-comment-error-dismiss>×</button>
			<span data-comment-error-message></span>
		</div>
		<div data-comments-editor>
			<div data-uploader-form>
				<div class="mentions">
					<div data-mentions-overlay data-default=""><?php echo $isEdit ? $overlay : '';?></div>
					<?php if (!$isEdit) { ?>
						<textarea class="o-form-control" row="1" name="message"
							data-mentions-textarea
							data-default=""
							data-initial="0"
							data-comments-form-input
							placeholder="<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_FORM_PLACEHOLDER' , true);?>"></textarea>
					<?php } else { ?>
						<textarea class="es-story-textfield o-form-control" name="content"
							data-comment-input
							data-story-textField
							data-mentions-textarea
							data-initial="0"><?php echo $comment; ?></textarea>
					<?php } ?>


					<?php if (ES::giphy()->isEnabledForComments() || $this->config->get('comments.attachments.enabled') || $this->config->get('comments.smileys')) { ?>
						<b class="es-form-attach">
							<?php if (ES::giphy()->isEnabledForComments()) { ?>
								<label class="es-input-gif" for="input-gif"
									data-giphy-button
									data-popbox="module://easysocial/giphy/popbox"
									data-popbox-toggle="click"
									data-popbox-position="bottom-right"
								>
									GIF
								</label>
							<?php } ?>

							<?php if ($this->config->get('comments.attachments.enabled')) { ?>
								<label class="es-input-photo" for="input-file" data-uploader-browse><i class="fa fa-camera"></i></label>
							<?php } ?>

							<?php if ($this->config->get('comments.smileys')) { ?>
								<?php echo ES::smileys()->html();?>
							<?php } ?>
						</b>
					<?php } ?>
				</div>

				<?php if (ES::giphy()->isEnabledForComments()) { ?>
					<?php echo $this->output('site/giphy/preview/form', array('giphy' => $hasGiphy)); ?>
				<?php } ?>

				<div class="attachments clearfix" data-comment-attachment-queue></div>
			</div>
		</div>
	</div>

	<?php if (!$isEdit) { ?>
		<div class="es-comments-form__footer">
			<a href="javascript:void(0);" class="btn btn-es-danger-o btn-sm t-hidden" data-replying-id data-comment-reply-cancel><?php echo JText::_('COM_ES_COMMENTS_CANCEL_REPLY_BUTTON'); ?>
			</a>

			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm"  data-comments-form-submit><?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_SUBMIT'); ?>
			</a>
		</div>
	<?php } else { ?>
		<div class="t-lg-mt--md">
			<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm t-lg-pull-right" data-save>
				<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_SAVE');?>
			</a>

			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm t-lg-pull-right t-lg-mr--sm" data-cancel>
				<?php echo JText::_('COM_ES_CANCEL');?>
			</a>
		</div>
	<?php } ?>
</div>

<div class="t-hidden" data-comment-attachment-template>
	<div class="figure" data-comment-attachment-item>
		<div class="attachment" data-comment-attachment-background>
			<div class="upload-progress">
				<div class="progress progress-striped active">
					<div class="upload-progress-bar progress-bar progress-bar-info" style="width: 0%;" data-comment-attachment-progress-bar></div>
				</div>
			</div>
			<a href="javascript:void(0);" class="attachment-cancel fa fa-times" data-comment-attachment-remove></a>
		</div>
	</div>
</div>

