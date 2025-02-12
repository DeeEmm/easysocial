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
<div class="dropdown_ dropdown--popdown" data-popdown<?php echo ' ' . $attributes; ?>>
	<button class="<?php echo $btnSize;?> btn-popdown dropdown-toggle_  btn-popdown--inline" type="button" data-es-toggle="dropdown" data-popdown-button>
		<i class="fa fa-caret-down btn-popdown__caret"></i>
		<div data-popdown-active>
			<?php echo $selectedHtml;?>
		</div>
		<input type="hidden" value="<?php echo $selected;?>" name="<?php echo $name;?>" />
	</button>

	<ul class="dropdown-menu dropdown-menu-<?php echo $direction; ?> dropdown-menu--popdown">
		<?php foreach ($options as $option) { ?>
		<li class="<?php echo $selected == $option->value ? 'active' : '';?>" data-popdown-option="<?php echo $option->value;?>">
			<?php echo $option->html;?>
		</li>
		<?php } ?>
	</ul>
</div>
