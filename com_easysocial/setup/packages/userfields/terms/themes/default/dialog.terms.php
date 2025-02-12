<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$termText = $params->get('message', 'PLG_FIELDS_TERMS_CONFIG_CONDITION_MESSAGE_TEXT');
?>
<dialog>
	<width>620</width>
	<height>420</height>
	<selectors type="json">
	{
		"{closeButton}"	: "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},
	}
	</bindings>
	<title><?php echo JText::_('PLG_FIELDS_TERMS_DIALOG_TITLE'); ?></title>
	<content>
		<div data-field-terms-textbox>
			<?php if ($useArticle && $article) { ?>
				<?php echo $article->introtext . $article->fulltext;?>
			<?php } else { ?>
				<?php echo nl2br(JText::_($termText)); ?>
			<?php } ?>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-sm btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
