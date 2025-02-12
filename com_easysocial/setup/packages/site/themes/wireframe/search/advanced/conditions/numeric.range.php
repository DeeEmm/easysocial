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
<?php
$data = array();
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
<div class="o-grid o-grid--2of4">
	<div class="o-grid__cell t-lg-pr--md t-xs-pr--no t-xs-mb--lg">
		<input type="number" class="o-form-control" min="1" placeholder="<?php echo JText::_('COM_ES_ADVANCED_SEARCH_FROM', true);?>"
			data-start value="<?php echo $this->html('string.escape', $data[0]);?>"
		/>
	</div>

	<div class="o-grid__cell">
		<input type="number" class="o-form-control" name="frmEnd" min="1" placeholder="<?php echo JText::_('COM_ES_ADVANCED_SEARCH_TO', true);?>"
			data-end value="<?php echo $this->html('string.escape', $data[1]);?>"
		/>
	</div>

	<input data-condition type="hidden" name="conditions[]" value="<?php echo $this->html('string.escape', $selected);?>" />
</div>
