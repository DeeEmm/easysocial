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
$attributes = isset($attributes) ? $attributes : '';
?>
<div class="form-group">
	<label for="<?php echo $name;?>" class="col-md-3">
		<?php echo $title;?>
		<i data-placement="bottom" data-title="<?php echo $title;?>" data-content="<?php echo $desc;?>" data-es-provide="popover" class="fa fa-question-circle pull-right"></i>
	</label>
	<div class="col-md-9">
		<?php echo $this->html('form.toggler', $name, $value, '', $attributes); ?>
	</div>
</div>
