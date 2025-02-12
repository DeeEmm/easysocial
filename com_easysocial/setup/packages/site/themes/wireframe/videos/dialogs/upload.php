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
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{form}": "[data-avatar-form]",
		"{file}": "[data-avatar-file]",
		"{filename}": "[data-avatar-filename]",
		"{uploadButton}": "[data-upload-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{closeButton}": "div.es-dialog-close-button"
	}
	</selectors>
	<bindings type="javascript">
	{

		"{uploadButton} click": function(button) {

			var button = $(button);
			var self = this.parent;
			var form = this.form();
			var file = this.file();
			var filename = this.filename();
			var path = file.val().replace(/.*(\/|\\)/, '');

			// If no file was chosen, stop.
			if (path == "") {
				return;
			}

			// Prevent user from clicking upload button twice
			button.disabled(true);

			// Add loading class to upload form
			// This will hide file input because in Chrome
			// cloned file inputs does not carry over filenames
			// and will show "No file chosen".
			form.addClass("loading");
			button.find('[data-button-loader]').addClass('is-active');

			// Show a filename instead
			filename.text(path);

			var task = EasySocial.ajax("site/controllers/videos/createThumbnailFromFile", {
						"id": "<?php echo $id;?>",
						"files": file
					}, {
						type: 'iframe'
					}).done(function(thumbnailPath) {
						self.close();

					}).fail(function(response) {
					
						self.setMessage(response);
					})
					.always(function(){

						// Enable upload button
						button.disabled(false);

						// Remove loading class
						form.removeClass("loading");
						button.find('[data-button-loader]').removeClass('is-active');


						// Reset filename
						filename.html("");
					});

			this.element.trigger("upload", task);
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_VIDEOS_UPLOAD_AVATAR'); ?></title>
	<content>
		<div class="es-avatar-form" data-avatar-form>
			<p><?php echo JText::_('COM_ES_VIDEOS_UPLOAD_AVATAR_INSTRUCTIONS' . $isMobile); ?></p>
			<input class="es-avatar-file" name="files" type="file" data-avatar-file />
			<span class="es-avatar-filename"><i class="es-loading-icon"></i> <span data-avatar-filename></span></span>
		</div>
	</content>
	<loading><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_UPLOADING'); ?></loading>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-upload-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_BUTTON'); ?>
			<div data-button-loader class="o-loader o-loader--sm o-loader--inline"></div>
		</button>
	</buttons>
</dialog>
