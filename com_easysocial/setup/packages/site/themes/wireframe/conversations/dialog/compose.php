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
<dialog>
	<width>500</width>
	<height>250</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{sendButton}": "[data-send-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_COMPOSE_DIALOG_TITLE'); ?></title>
	<content>
		<div class="es-wrapper">
			<div class="es-user-name t-d--inline-flex t-align-items--c t-lh--1">
				<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_COMPOSE_TO'); ?>:

				<?php if ($type == 'user') { ?>
				<span class="t-d--inline-flex t-align-items--c t-ml--2xs">
					<a href="<?php echo $recipient->getPermalink();?>">
						<?php echo $this->html('avatar.user', $recipient, 'sm', false, false, '', false); ?>
					</a>
					<span class="t-ml--2xs">
						<?php echo $this->html('html.user', $recipient); ?>
					</span>

				</span>
				<?php } ?>

				<?php if ($type == 'list') { ?>
				<span>
					<a href="<?php echo $list->getPermalink();?>">
						<i class="icon-es-clipboard t-lg-mr--sm"></i>&nbsp <strong><?php echo $list->get('title');?></strong>
					</a>
				</span>
				<?php } ?>
			</div>

			<div class="composer-textarea input-wrap t-lg-mt--lg">
				<textarea class="o-form-control" name="message" style="height: 160px;" data-composer-editor  data-composer-message><?php echo $message;?></textarea>
			</div>

			<?php foreach ($recipients as $recipient) { ?>
			<input type="hidden" name="recipient[]" value="<?php echo $recipient->id;?>" data-composer-recipient />
			<?php } ?>
			<input type="hidden" name="ctype" value="<?php echo $ctype;?>" data-composer-type />
			<input type="hidden" name="title" value="<?php echo $title;?>" data-composer-title />

		</div>

		<br />
		<div class="o-alert o-alert--warning o-alert--icon t-hidden" role="alert" data-composer-notice></div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default">
			<?php echo JText::_('COM_ES_CANCEL'); ?>
		</button>
		<button data-send-button type="button" class="btn btn-es-primary-o">
			<?php echo JText::_('COM_EASYSOCIAL_SEND_BUTTON'); ?>
		</button>
	</buttons>
</dialog>

