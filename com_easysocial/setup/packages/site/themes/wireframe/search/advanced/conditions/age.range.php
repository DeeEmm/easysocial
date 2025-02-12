<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<input type="hidden" name="conditions[]" value="<?php echo $this->html('string.escape', $selected);?>" data-condition />
<?php
	$data[0] = '';
	$data[1] = '';

	if ($selected) {
		$tmp = explode('|', $selected);
		$data[0] = $tmp[0];
		if (isset($tmp[1])) {
			$data[1] = $tmp[1];
		}
	}
?>
<input data-start type="number" class="o-form-control" name="frmStart" min="1" max="150" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_ADVANCED_SEARCH_ENTER_FROM' , true );?>" value="<?php echo $this->html('string.escape', $data[0]);?>" />
<input data-end type="number" class="o-form-control" name="frmEnd" min="1" max="150" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_ADVANCED_SEARCH_ENTER_TO' , true );?>" value="<?php echo $this->html('string.escape', $data[1]);?>" />
