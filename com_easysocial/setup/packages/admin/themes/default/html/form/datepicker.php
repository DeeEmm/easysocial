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
<div <?php echo $id ? 'id="' . $id . '"' : '';?> class="o-input-group" data-es-datepicker data-value="<?php echo ($value && $value !== '0000-00-00 00:00:00') ? ES::date($value, false)->toFormat('Y-m-d H:i:s') : '';?>">
	<input type="text" class="o-form-control" placeholder="<?php echo JText::_($placeholder); ?>" data-es-datepicker-picker />
	<input type="hidden" name="<?php echo $name;?>" data-es-datepicker-input />
	<span class="o-input-group__addon" data-es-datepicker-toggle>
		<i class="far fa-calendar-alt"></i>
	</span>
</div>
