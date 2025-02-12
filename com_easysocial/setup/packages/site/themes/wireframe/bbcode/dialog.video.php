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
	<width>600</width>
	<height>250</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]",

		"{form}" : "[data-es-video-form]",
		"{videoUrl}": "[data-es-video-url]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click": function() {
			var url = this.videoUrl().val();

			// Insert the video
			window.insertVideoCode(url, "<?php echo $caretPosition;?>", "<?php echo $element;?>");

			// Close the dialog
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_BBCODE_INSERT_VIDEO'); ?></title>
	<content>
		<p class="t-lg-mb--xl">
			<?php echo JText::_('COM_EASYSOCIAL_BBCODE_INSERT_VIDEO_DESC');?>
		</p>

		<ul class="es-video-providers">
			<li class="video-youtube"><?php echo JText::_('COM_EASYSOCIAL_YOUTUBE');?></li>
			<li class="video-vimeo"><?php echo JText::_('COM_EASYSOCIAL_VIMEO');?></li>
			<li class="video-dailymotion"><?php echo JText::_('COM_EASYSOCIAL_DAILYMOTION');?></li>
			<li class="video-google"><?php echo JText::_('COM_EASYSOCIAL_GOOGLE');?></li>
			<li class="video-metacafe"><?php echo JText::_('COM_EASYSOCIAL_METACAFE');?></li>
			<li class="video-nicovideo"><?php echo JText::_('COM_EASYSOCIAL_NICOVIDEO');?></li>
			<li class="video-smule"><?php echo JText::_('COM_EASYSOCIAL_SMULE');?></li>
		</ul>
		<form data-es-video-form>
			<label for="videoURL">
				<strong><?php echo JText::_('COM_EASYSOCIAL_VIDEO_URL');?>:</strong>
			</label>
			<input type="text" id="videoURL" value="" class="o-form-control" data-es-video-url />
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_INSERT_BUTTON'); ?></button>
	</buttons>
</dialog>
