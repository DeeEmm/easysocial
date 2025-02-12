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
<input type="hidden" name="task" value="<?php echo $task;?>" data-table-grid-task />
<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />

<input type="hidden" name="option" value="com_easysocial" />
<input type="hidden" name="controller" value="<?php echo $controller;?>" data-table-grid-controller />

<?php if ($view) { ?>
<input type="hidden" name="view" value="<?php echo $controller;?>" />
<?php } ?>

<?php echo $this->html('form.token'); ?>
