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
	<width>860</width>
	<height>640</height>
	<selectors type="json">
	{
		"{close}": "[data-close-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{close} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_($mailer->title);?></title>
	<content type="text"><?php echo rtrim( JURI::root() , '/' );?>/administrator/index.php?option=com_easysocial&view=mailer&layout=preview&id=<?php echo $mailer->id;?>&tmpl=component</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_( 'COM_EASYSOCIAL_CLOSE_BUTTON' ); ?></button>
	</buttons>
</dialog>
