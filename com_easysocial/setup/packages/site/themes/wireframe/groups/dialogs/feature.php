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
		"{closeButton}" : "[data-close-button]",
		"{setFeatured}"	: "[data-set-button]",
		"{form}"		: "[data-group-featured-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function()
		{
		},
		"{closeButton} click": function()
		{
			this.parent.close();
		},
		"{setFeatured} click" : function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_GROUPS_DIALOG_SET_GROUP_FEATURED_TITLE'); ?></title>
	<content>
		<form data-group-featured-form method="post" action="<?php echo JRoute::_( 'index.php' );?>">
			<p class="t-lg-mt--md">
				<img src="<?php echo $group->getAvatar();?>" class="t-lg-ml--xl" align="right" />

				<?php echo JText::sprintf('COM_EASYSOCIAL_GROUPS_DIALOG_SET_GROUP_FEATURED_CONTENT', $group->getName());?>
			</p>

			<?php echo $this->html('form.token'); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="groups" />
			<input type="hidden" name="task" value="setFeatured" />
			<input type="hidden" name="id" value="<?php echo $group->id;?>" />

			<?php if ($returnUrl) { ?>
			<input type="hidden" name="return" value="<?php echo $returnUrl;?>" />
			<?php } ?>
		</form>

	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-set-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SET_FEATURED_BUTTON'); ?></button>
	</buttons>
</dialog>
