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

<input data-condition type="hidden" class="o-form-control input-sm" name="conditions[]" value="<?php echo $this->html('string.escape', $selected);?>" />
<?php
	$data[0] = '';
	$data[1] = '';

	if ($selected) {
		$tmp = explode( '|', $selected );
		$data[0] = $tmp[0];
		$data[1] = isset($tmp[1]) && $tmp[1] ? $tmp[1] : $tmp[0];
	}
?>
<?php echo $this->html( 'form.calendar', 'frmStart', $this->html( 'string.escape', $data[0] ), '', array('data-start'), false, 'DD-MM-YYYY', false, false); ?>
<?php echo $this->html( 'form.calendar', 'frmEnd', $this->html( 'string.escape', $data[1] ), '', array('data-end'), false, 'DD-MM-YYYY', false, false); ?>
