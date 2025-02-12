<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<span class="<?php echo !$keys ? 't-hidden' : '';?>">
<?php if ($keys) { ?>
    <select class="o-form-control" name="datakeys[]" data-key>
    	<?php foreach( $keys as $key => $label ){ ?>
    		<option value="<?php echo $key; ?>"<?php echo $key == $selected ? ' selected="selected"' : '';?>><?php echo $label; ?></option>
    	<?php } ?>
    </select>
<?php } else { ?>
    <input type="hidden" value="" name="datakeys[]" />
<?php } ?>
</span>
