<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<label class="o-control-label">
	<?php echo $label; ?>

	<?php if ($help) { ?>
	<i class="fa fa-question-circle pull-right" <?php echo $this->html('bootstrap.popover', $label, $desc, 'bottom' , '' , true);?>></i>
	<?php } ?>
</label>
