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
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="languages-wrapper" data-languages-wrapper>
		<div class="languages-loader">
			<?php echo JText::_('COM_EASYSOCIAL_INITIALIZING_LANGUAGE_LIST');?><br />
		</div>

		<div class="invalid-api">
			<i class="fa fa-times text-error"></i>
			<div data-languages-error></div>
		</div>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="languages" />
	<input type="hidden" name="controller" value="languages" />
</form>
