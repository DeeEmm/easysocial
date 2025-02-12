<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>500</width>
	<height>450</height>
	<selectors type="json">
	{
		"{sendButton}": "[data-send-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{repostContent}": "[data-repost-form-content]",
		"{textbox}": "[data-repost-textbox]",
		"{header}": "[data-repost-header]",
		"{form}": "[data-repost-form]",
		"{closeButton}": ".es-dialog-close-button",

		"{radioUser}": "[data-repost-user]",
		"{radioCluster}": "[data-repost-cluster]"

	}
	</selectors>
	<bindings type="javascript">
	{
		"{repostContent} focus": function() {
			this.validateContent();
		},

		"validateContent" : function() {

			var content = $('[data-repost-form-content]').val();

			if (content == '<?php echo JText::_('COM_EASYSOCIAL_REPOST_FORM_DIALOG_MSG'); ?>') {
				$('[data-repost-form-content]').val('');
			}
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_REPOST_FORM_DIALOG_TITLE'); ?></title>
	<content>
		<div class="es-repost-form" data-repost-wrapper>
			<form method="post" class="t-lg-mb--lg" data-repost-form>

				<?php if ($cluster) { ?>
				<div>
					<div class="o-radio">
						<input type="radio" id="item-radio-0" name="repost_on" value="0" checked="checked" data-repost-user>
						<label for="item-radio-0"><?php echo JText::_('COM_ES_REPOST_FORM_INTO_USER_FEEDS'); ?></label>
					</div>

					<?php if ($canRepostIntoCluster) { ?>
						<div class="o-radio">
							<input type="radio" id="item-radio-1" name="repost_on" value="<?php echo $cluster->id; ?>" data-repost-cluster>
							<label for="item-radio-1"><?php echo JText::sprintf('COM_ES_REPOST_FORM_INTO_CLUSTER_FEEDS', $cluster->getTitle()); ?></label>
						</div>
					<?php } ?>
				</div>
				<?php } ?>

				<div class="es-form" data-repost-header>
					<div class="es-story-textbox mentions-textfield" data-repost-textbox>
						<div class="mentions">
							<div data-mentions-overlay data-default=""></div>
							<textarea class="o-form-control" name="content" autocomplete="off" data-story-textField data-mentions-textarea data-repost-form-content data-default="" data-initial="0"
								placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_REPOST_FORM_DIALOG_MSG' ); ?>"></textarea>
						</div>
					</div>
				</div>

				<?php echo $this->html('suggest.hashtags'); ?>
				<?php echo $this->html('suggest.friends'); ?>
			</form>

			<?php if ($preview) { ?>
				<?php echo $preview; ?>
			<?php } ?>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-send-button type="button" data-share-as="<?php echo $shareAs; ?>" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_REPOST_SUBMIT_BUTTON'); ?></button>
	</buttons>
</dialog>
