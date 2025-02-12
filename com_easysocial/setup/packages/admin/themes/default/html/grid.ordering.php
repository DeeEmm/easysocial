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
<div class="arrow-wrapper o-btn-group<?php echo $showOrdering ? ' pull-left' : ' center';?>" data-grid-column>
	<?php if ($current != 1 && $showOrdering) { ?>
	<a class="btn btn-xs btn-default" href="javascript:void(0);" title="<?php echo $this->html('string.escape', JText::_('COM_EASYSOCIAL_GRID_MOVE_UP'));?>"
		data-es-provide="tooltip"
		data-grid-order-up>
		<i class="fa fa-caret-up"></i>
	</a>
	<?php } ?>

	<?php if( $current != $total && $showOrdering ){ ?>
	<a class="btn btn-xs btn-default" href="javascript:void(0);" data-original-title="<?php echo $this->html('string.escape', JText::_('COM_EASYSOCIAL_GRID_MOVE_DOWN'));?>"
		data-es-provide="tooltip"
		data-grid-order-down
	>
		<i class="fa fa-caret-down"></i>
	</a>
	<?php } ?>
</div>
<input type="text" name="order[]" size="3" value="<?php echo $ordering;?>" class="o-form-control input-sm text-center input-short<?php echo $showOrdering ? ' pull-right' : '';?>">
