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
	<width>600</width>
	<height>310</height>
	<escape_key>0</escape_key>
	<selectors type="json">
	{
		"{viewport}" : "[data-avatar-viewport]",
		"{image}"    : "[data-avatar-image]",
		"{createButton}"  : "[data-create-button]",
		"{cancelButton}"  : "[data-cancel-button]",
		"{wrapper}": "[data-photos-avatar]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {

			var dialog = this;

			EasySocial.require()
				.script('site/photos/avatar')
				.done(function(){

					dialog.element.addController("EasySocial.Controller.Photos.Avatar", {
						uid: "<?php echo $uid;?>",
						type: "<?php echo $type;?>",
						redirect: <?php echo ($redirect) ? 1 : 0; ?>,
						redirectUrl: "<?php echo $redirectUrl;?>"
					});
				});
		},

		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{self} avatarCreate": function(el, event, task) {

			var dialog = this;

			task.done(function(){
				EasySocial.dialog().close();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_AVATAR_CROP'); ?></title>
	<content>
		<div class="es-photos-avatar" data-photos-avatar>

			<p><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_AVATAR_CROP_HINT'); ?></p>

			<div class="es-photo-image" style="background-image: url(<?php echo $photo->getSource('large'); ?>)" data-photo-image>
				 <div data-photo-avatar-viewport class="es-photo-avatar-viewport"></div>
			</div>
			<input type="hidden" data-photo-id value="<?php echo $photo->id; ?>" />
		</div>
	</content>
	<loading><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_AVATAR_CROPPING'); ?></loading>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-create-button type="button" class="btn btn-es-primary btn-sm" disabled><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_CREATE_AVATAR'); ?>
			<div data-create-button-loader class="o-loader o-loader--sm o-loader--inline"></div>
		</button>
	</buttons>
</dialog>
