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
<div class="o-form-group <?php echo !empty($error) ? 'error' : '';?>" data-field data-field-<?php echo $field->id; ?> data-sample-field data-sample-field-<?php echo $field->id;?>>
	<?php if (!isset($options['title']) || $options['title'] !== false) { ?>
		<?php echo $this->includeTemplate('admin/fields/sample.title'); ?>
	<?php } ?>

	<div class="o-control-input">
		<?php echo $this->includeTemplate($subNamespace); ?>

        <?php if (!isset($options['description']) || $options['description'] !== false) { ?>
            <?php echo $this->includeTemplate('admin/fields/sample.description'); ?>
        <?php } ?>
	</div>

	
</div>
