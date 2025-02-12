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
<a href="https://wa.me/<?php echo $value;?>" target="_blank">
	<?php if ($params->get('icon', true)) { ?>
		<i class="fab fa-whatsapp"></i>&nbsp;
	<?php } ?>
	https://wa.me/<?php echo $value;?>
</a>
