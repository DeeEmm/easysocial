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
<input type="text" name="<?php echo $field->inputName;?>" id="<?php echo $field->name;?>" placeholder="<?php echo isset( $field->placeholder ) ? $field->placeholder : '';?>"
    <?php if (isset($field->maxupload) && $field->maxupload) { ?>
    data-maxupload-check
    data-maxupload="<?php echo $field->maxupload; ?>"
    data-maxupload-key="<?php echo strtoupper(str_replace('.', '_', $field->name)); ?>"
    <?php } ?>
	class="<?php echo isset($field->class) ? $field->class : '';?> o-form-control"
	value="<?php echo $params->get( $field->name , $field->default );?>" /> <?php echo isset( $field->suffix ) ? $field->suffix : '';?>
<?php if (isset($field->maxupload) && $field->maxupload) { ?>
&nbsp;<span class="t-fs--sm"><?php echo $field->maxuploadDisplay; ?></span>
<?php } ?>
