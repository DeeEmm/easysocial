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
<div class="profile-field-config" data-fields-config>
	<h3><?php echo $title;?></h3>
	
	<div class="profile-field-close close" data-fields-config-close>×</div>

	<div data-fields-config-params>
		<?php foreach ($params as $name => $field) { ?>
			<div class="o-form-group" data-es-provide="tooltip" data-original-title="<?php echo $field->tooltip ? $field->tooltip : ''; ?>">
				<div>
					<label><?php echo $field->label; ?></label>
				</div>

				<div>
					<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name, 'field' => $field, 'value' => $values->get($name))); ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
