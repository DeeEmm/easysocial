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
<div>
	<a href="tel://<?php echo $value;?>" target="_blank">
		<?php if ($params->get('icons', true)) { ?>
			<i class="fa fa-phone"></i>&nbsp;
		<?php } ?>
		<?php echo $value;?>
	</a>
</div>
<?php if ($params->get('sms', true)) { ?>
<div class="t-lg-mt--md">
	<a href="sms://<?php echo $value;?>" target="_blank">
		<?php if ($params->get('icons', true)) { ?>
			<i class="fa fa-comments"></i>&nbsp;
		<?php } ?>
		<?php echo $value;?>
	</a>
</div>
<?php } ?>
