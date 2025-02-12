<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" class="profileForm" method="post" enctype="multipart/form-data" data-user-form>

<div class="o-checkbox t-lg-mb--xl">
	<input id="autoapproval" type="checkbox" name="autoapproval" value="1">
	<label for="autoapproval">
		<?php echo JText::_('COM_EASYSOCIAL_USER_AUTOMATICALLY_APPROVE_USER');?>
	</label>

	<input id="sendWelcomeMail" type="checkbox" name="sendWelcomeMail" value="1">
	<label for="sendWelcomeMail">
		<?php echo JText::_('COM_ES_USER_SEND_WELCOME_NOTIFICATION');?>
	</label>	
</div>

<div data-user-new-content>
	<?php echo $this->includeTemplate('admin/users/form/content'); ?>
</div>

<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check>
<input type="hidden" name="option" value="com_easysocial" />
<input type="hidden" name="controller" value="users" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="profileId" value="<?php echo $profile->id;?>" />
<?php echo JHTML::_( 'form.token' );?>

</form>
