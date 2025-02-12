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
<?php
		$geo = array();

		if ($selected) {
			$geo = explode( '|', $selected );
		}
?>

	<input data-distance type="number" class="o-form-control input-sm"
		   name="frmDistance" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_ADVANCED_SEARCH_ENTER_DISTANCE' , true );?>"
		   value="<?php echo isset($geo[0]) ? $geo[0] : '';?>" />
    <input class="o-form-control input-sm" type="hidden" value="<?php echo isset($geo[1]) ? $geo[1] : '';?>" name="frmLatitude" data-latitude />
    <input class="o-form-control input-sm" type="hidden" value="<?php echo isset($geo[2]) ? $geo[2] : '';?>" name="frmLongitude" data-longitude />
    <input class="o-form-control input-sm" type="hidden" value="<?php echo isset($geo[3]) ? $geo[3] : '';?>" name="frmAddress" data-address />

	<input data-condition type="hidden" class="o-form-control input-sm" name="conditions[]" value="<?php echo $this->html('string.escape', $selected);?>" />
    <span class="t-hidden" data-error-map-permission><?php echo JText::_('COM_EASYSOCIAL_LOCATION_PERMISSION_ERROR', true);?></span>
    <span class="t-hidden" data-error-map-timeout><?php echo JText::_('COM_EASYSOCIAL_LOCATION_TIMEOUT_ERROR', true);?></span>
    <span class="t-hidden" data-error-map-unavailable><?php echo JText::_('COM_EASYSOCIAL_LOCATION_UNAVAILABLE_ERROR', true);?></span>
