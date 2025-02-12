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
<ul class="g-list-unstyled fields-config-param-choices" data-fields-config-param data-fields-config-param-choices 
	data-fields-config-param-field-<?php echo $name; ?> 
	data-name="<?php echo $name; ?>" 
	data-unique="<?php echo isset( $field->unique ) ? $field->unique : 1; ?>"
>
	<?php if (!empty($value)) { ?>
		<?php foreach ($value as $key => $v) { ?>
			<?php echo $this->loadTemplate('admin/fields/config/choices.item', array('id' => $v->id, 'title' => $v->label, 'value' => $v->value, 'default' => isset($v->default) ? $v->default : 0, 'hasDefault' => isset($field->hasDefault) ? $field->hasDefault : true )); ?>
		<?php } ?>
	<?php } ?>

	<?php if (empty($value) && !isset($field->hasDefaultItems)) { ?>
		<?php echo $this->loadTemplate('admin/fields/config/choices.item', array('id' => 0, 'title' => '', 'value' => '', 'default' => 0, 'hasDefault' => isset($field->hasDefault) ? $field->hasDefault : true)); ?>
	<?php } ?>

	<?php if (empty($value) && isset($field->hasDefaultItems) && $field->hasDefaultItems && isset($field->defaultItems) && $field->defaultItems) { ?>
		<?php
			$defaultItems = ES::makeArray($field->defaultItems);
		?>
		<?php foreach ($defaultItems as $defaultItem) { ?>
			<?php echo $this->loadTemplate('admin/fields/config/choices.item', array('id' => 0, 'title' => $defaultItem->label, 'value' => $defaultItem->value, 'default' => 0, 'hasDefault' => isset($field->hasDefault) ? $field->hasDefault : true)); ?>
		<?php } ?>
	<?php } ?>
</ul>