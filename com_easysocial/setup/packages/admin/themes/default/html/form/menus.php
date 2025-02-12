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
<div class="o-select-group">
	<select id="<?php echo $id;?>" name="<?php echo $name;?>" class="o-form-control" autocomplete="off">
		<?php foreach( $menus as $menutype => $items ){ ?>
			<?php if( $menutype ){ ?>
			<optgroup label="<?php echo $menutype;?>">
			<?php } ?>

			<?php foreach( $items as $item ){ ?>
				<option value="<?php echo $item->value;?>"<?php echo $selected == $item->value ? ' selected="selected"' : '';?>><?php echo $item->text;?></option>
			<?php } ?>

			<?php if( $menutype ){ ?>
			</optgroup>
			<?php } ?>
		<?php } ?>
	</select>
	<label for="<?php echo $id; ?>" class="o-select-group__drop"></label>
</div>
