<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{removeButton}"	: "[data-remove-button]",
		"{form}"		: "[data-page-unfeature-form]"
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
		"{removeButton} click" : function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PAGES_DIALOG_UNFEATURE_PAGE_TITLE'); ?></title>
	<content>
		<form data-page-unfeature-form method="post" action="<?php echo JRoute::_('index.php');?>">
			<p class="t-lg-mt--md">
				<img src="<?php echo $page->getAvatar();?>" class="t-lg-ml--xl" align="right" />

				<?php echo JText::sprintf('COM_EASYSOCIAL_PAGES_DIALOG_UNFEATURE_PAGE_CONTENT', $page->getName());?>
			</p>

			<?php echo $this->html('form.token'); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="pages" />
			<input type="hidden" name="task" value="removeFeatured" />
			<input type="hidden" name="id" value="<?php echo $page->id;?>" />

			<?php if ($returnUrl) { ?>
			<input type="hidden" name="return" value="<?php echo $returnUrl;?>" />
			<?php } ?>
		</form>

	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-remove-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_REMOVE_FEATURED_BUTTON'); ?></button>
	</buttons>
</dialog>
