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

$editors = ES::getEditors(true);
?>
<div class="row">
	<div class="col-sm-6">
		<select name="<?php echo $field->inputName;?>" class="o-form-control <?php echo isset( $field->class ) ? $field->class : '';?>">
			<?php if( isset( $editors ) ){ ?>
				<?php foreach( $editors as $editor ){ ?>
				<option value="<?php echo $editor->value;?>"<?php echo $params->get( $field->name , $field->default ) == $editor->value ? ' selected="selected"' : '';?>><?php echo JText::_($editor->text);?></option>
				<?php } ?>
			<?php } ?>
		</select>
	</div>
</div>
