<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-row">
	<div class="o-col--3">
		<div class="o-input-group guest-limit">
		    <input type="text" class="o-form-control text-center" name="guestlimit" value="<?php echo $value; ?>"/>
		    <span class="o-input-group__addon">
		    	<i class="fa fa-users" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_GUESTS');?>"></i>
		    </span>
		</div>
	</div>
	<div class="o-col--9"></div>
</div>

<?php if ($error) { ?>
<div class="o-row">
	<div class="o-col--12 t-text--danger">
		<?php echo $error;?>
	</div>
</div>
<?php } ?>
