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
	<width>450</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}"  	: "[data-close-button]",
		"{submitButton}"	: "[data-submit-button]",
		"{form}"			: "[data-group-invite-respond]",
		"{responseValue}"	: "[data-respond-value]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function()
		{
			this.parent.close();
		},

		"{submitButton} click" : function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_GROUPS_DIALOG_CONFIRM_PUBLISH_TITLE'); ?></title>
	<content>
		<p class="t-lg-mt--md">
			<img src="<?php echo $group->getAvatar();?>" class="t-lg-ml--xl" align="right" />

			<?php echo JText::sprintf('COM_EASYSOCIAL_GROUPS_DIALOG_PUBLISH_CONTENT', $group->getName());?>
		</p>

		<form data-group-invite-respond method="post" action="<?php echo JRoute::_( 'index.php' );?>">
			<?php echo $this->html( 'form.token' ); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="groups" />
			<input type="hidden" name="task" value="publish" />
			<input type="hidden" name="id" value="<?php echo $group->id;?>" />
		</form>
	</content>
	<buttons>
		<button type="button" class="btn btn-es-default btn-sm" data-close-button><?php echo JText::_( 'COM_ES_CANCEL' );?></button>
		<button type="button" class="btn btn-es-danger btn-sm" data-submit-button><?php echo JText::_( 'COM_EASYSOCIAL_PUBLISH_GROUP_BUTTON' );?></button>
	</buttons>
</dialog>
