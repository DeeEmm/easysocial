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
<dialog>
	<width>500</width>
	<height>250</height>
	<selectors type="json">
	{
		"{saveButton}" : "[data-save-button]",
		"{cancelButton}" : "[data-cancel-button]",
		"{title}": "[data-feeds-form-title]",
		"{url}": "[data-feeds-form-url]",
		"{notice}": "[data-form-notice]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		resetNotice: function() {
			this.notice().addClass('t-hidden');
			this.notice().removeClass('alert alert-error');
		},

		showError: function(message) {
			this.notice().text(message);
			this.notice().addClass('alert alert-error');
			this.notice().removeClass('t-hidden');

			return;
		},

		checkEmpty: function(value) {

			if (value.trim().length == 0) {
				return false;
			}

			return true;
		},

		"{saveButton} click": function(button, event) {
			var title = this.title().val();
			var url = this.url().val();
			var notice = $('[data-feeds-form-notice]');

			// Reset notice
			this.resetNotice();
			button.addClass('is-loading');

			if (!this.checkEmpty(title)) {
				button.removeClass('is-loading');
				this.showError('<?php echo JText::_('Please enter a title for your feed', true);?>');
				return;
			}

			if (!this.checkEmpty(url)) {
				button.removeClass('is-loading');
				this.showError('<?php echo JText::_('Please enter URL', true);?>');
				return;
			}

			var self = this;

			EasySocial.ajax('site/controllers/feeds/save', {
				"title": title,
				"url": url,
				"appId": self.caller.options.appId,
				"uid": this.caller.options.id
			}).done(function(output) {
				button.removeClass('is-loading');

				// Whenever a new feed item is created, it should never be empty.
				self.caller.browser().removeClass('is-empty');

				// Append output to the list
				self.caller.sources().prepend(output);

				EasySocial.dialog().close();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('APP_FEEDS_DIALOG_CREATE_TITLE'); ?></title>
	<content>
		<p class="mb-20"><?php echo JText::_('APP_GROUP_FEEDS_DIALOG_CREATE_DESC'); ?></p>

		<div data-form-notice class="t-hidden"></div>

		<label for="feed-title">
			<?php echo JText::_('APP_FEEDS_DIALOG_FORM_CREATE_TITLE'); ?>:
		</label>
		<input type="text" name="title" value="" id="feed-title" class="o-form-control" placeholder="<?php echo JText::_('APP_FEEDS_DIALOG_FORM_CREATE_TITLE');?>" data-feeds-form-title />

		<div class="t-lg-mt--xl">
			<label for="feed-url">
				<?php echo JText::_('APP_FEEDS_DIALOG_FORM_CREATE_URL'); ?>:
			</label>
			<input type="text" name="title" value="" id="feed-url" class="o-form-control" placeholder="<?php echo JText::_('APP_FEEDS_DIALOG_FORM_CREATE_URL_PLACEHOLDER');?>" data-feeds-form-url />
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-save-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('APP_FEEDS_CREATE_BUTTON'); ?></button>
	</buttons>
</dialog>
