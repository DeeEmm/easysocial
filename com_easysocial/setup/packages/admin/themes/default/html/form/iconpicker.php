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
<?php echo $this->html('panel.label', $title); ?>

<div class="col-md-7">
	<div class="o-btn-group">
		<button type="button" class="btn btn-es-default-o iconpicker-component">
			<i class="<?php echo $value !== '' ? $value : $defaultIcon; ?>"></i>
		</button>
		<button type="button" data-icon-selection class="icp icp-dd btn btn-es-default-o dropdown-toggle"
			data-selected="<?php echo $value; ?>" data-toggle="dropdown">
			<i class="fa fa-caret-down"></i>
		</button>

		<div class="dropdown-menu"></div>
	</div>
	<input type="hidden" id="<?php echo $name;?>" name="<?php echo $name;?>" data-icon-input value="<?php echo $value;?>" />
</div>
