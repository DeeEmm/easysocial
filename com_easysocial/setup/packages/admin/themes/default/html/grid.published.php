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
<a class="es-state-<?php echo $class;?> <?php echo !$allowed ? 'disabled' : '';?>" href="javascript:void(0);"
	<?php echo $allowed ? ' data-table-grid-publishing' : '';?>
	data-task="<?php echo $task;?>"
	data-es-provide="tooltip"
	data-original-title="<?php echo $this->html( 'string.escape' , $tooltip );?>"
	data-controller="<?php echo isset($controllerName) ? $controllerName : '';?>"
	<?php echo !$allowed ? ' disabled="disabled"' : '';?>
></a>

