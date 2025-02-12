<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
		"{closeButton}": "[data-close-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},
		"{submitButton} click": function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_MARKETPLACES_DIALOG_' . strtoupper($type) . '_LISTING_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_ES_MARKETPLACES_DIALOG_' . strtoupper($type) . '_LISTING_CONTENT', $listing->getTitle());?></p>
		<form data-form method="post" action="<?php echo JRoute::_('index.php'); ?>">
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="marketplaces" />
			<input type="hidden" name="task" value="<?php echo $task; ?>" />
			<input type="hidden" name="id" value="<?php echo $listing->id; ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_YES_BUTTON'); ?></button>
	</buttons>
</dialog>

