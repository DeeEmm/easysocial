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
<div class="panel-head">
	<div class="t-d--flex">
		<div class="t-flex-grow--1">
			<b class="panel-head-title"><?php echo JText::_($header);?></b>
			<div class="panel-info"><?php echo JText::_($desc);?></div>
		</div>

		<?php if ($helpLink) { ?>
		<div>
			<a href="<?php echo $helpLink;?>" class="btn btn-es-default btn-sm" target="_blank">
				<i class="far fa-life-ring"></i>&nbsp; <?php echo JText::_('JHELP'); ?>
			</a>
		</div>
		<?php } ?>
	</div>
</div>
