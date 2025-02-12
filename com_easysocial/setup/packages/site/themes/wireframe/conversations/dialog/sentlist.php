<?php
/**
* @package 		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license 		Proprietary Use License http://stackideas.com/licensing.html
* @author 		Stack Ideas Sdn Bhd
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>400</width>
	<height>120</height>
	<selectors type="json">
	{
		"{cancelButton}"	: "[data-cancel-button]",
		"{viewButton}"		: "[data-view-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function()
		{
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_DELIVERED_DIALOG_TITLE' ); ?></title>
	<content>
		<div class="es-wrapper">
			<div>
				<?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_DELIVERED_TO_LIST' ); ?> <a href="<?php echo $list->getPermalink();?>"><?php echo $list->get( 'title' );?></a>.
			</div>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm">
			<?php echo JText::_( 'COM_EASYSOCIAL_CLOSE_BUTTON' ); ?>
		</button>
		<button data-view-button type="button" class="btn btn-es-primary btn-sm">
			<?php echo JText::_( 'COM_EASYSOCIAL_VIEW_CONVERSATION_BUTTON' ); ?>
		</button>
	</buttons>
</dialog>

