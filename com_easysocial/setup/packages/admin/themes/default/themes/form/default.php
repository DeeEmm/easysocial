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
?>
<form name="themeForm" method="post" action="index.php" id="adminForm">
	<?php echo $theme->form;?>
	
	<input type="hidden" name="activeTab" data-tab-active />
	<input type="hidden" name="<?php echo ES::token();?>" value="1" />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="themes" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="element" value="<?php echo $theme->element;?>" />
</form>
