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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

$param = $params->get($field->name,$field->default);
if (! is_object($param)) {
	$oriVal = $param;
	$param = new stdClass();
	$param->value = $oriVal;
	$param->interval = 0;
}
?>
<div class="t-d--flex t-align-items--c" data-limit-form>
	<div>
		<?php echo $this->html( 'grid.boolean' , uniqid() , $param->value == 0 ? true : false , '' , array( 'data-' . str_ireplace( array( '[' , '.' , ']' ) , '' , $field->inputName ) ) , array() ,
								array( 'on' => JText::_( 'Unlimited' ) , 'off' => JText::_( 'Limited' )  )
				); ?>
	</div>
	<div>
		<span class="<?php echo $param->value == 0 ? 't-hidden' : '';?>" data-limit-limited>
			<input data-limit-input type="text" name="<?php echo $field->inputName;?>[value]" id="<?php echo $field->name;?>" placeholder="<?php echo isset( $field->placeholder ) ? $field->placeholder : '';?>"
				class="o-form-control input-sm t-ml--sm input-short text-center <?php echo isset( $field->class ) ? $field->class : '';?>"
				value="<?php echo $param->value;?>" /> <?php echo isset( $field->suffix ) ? $field->suffix : '';?>
		</span>
	</div>

	<div>
		<span class="<?php echo $param->value == 0 ? 't-hidden' : '';?>" data-limit-interval>
			<select data-interval-input name="<?php echo $field->inputName;?>[interval]" class="o-form-control t-ml--sm input-sm <?php echo isset( $field->class ) ? $field->class : '';?>">
				<option value="<?php echo SOCIAL_ACCESS_LIMIT_INTERVAL_NO; ?>" <?php echo ($param->interval == SOCIAL_ACCESS_LIMIT_INTERVAL_NO) ? 'selected="true"' : '' ; ?>>
					<?php echo JText::_('COM_EASYSOCIAL_ACCESS_LIMIT_INTEVAL_NO'); ?></option>
				<option value="<?php echo SOCIAL_ACCESS_LIMIT_INTERVAL_DAILY; ?>" <?php echo ($param->interval == SOCIAL_ACCESS_LIMIT_INTERVAL_DAILY) ? 'selected="true"' : '' ; ?>>
					<?php echo JText::_('COM_EASYSOCIAL_ACCESS_LIMIT_INTEVAL_DAILY'); ?></option>
				<option value="<?php echo SOCIAL_ACCESS_LIMIT_INTERVAL_WEEKLY; ?>" <?php echo ($param->interval == SOCIAL_ACCESS_LIMIT_INTERVAL_WEEKLY) ? 'selected="true"' : '' ; ?>>
					<?php echo JText::_('COM_EASYSOCIAL_ACCESS_LIMIT_INTEVAL_WEEKLY'); ?></option>
				<option value="<?php echo SOCIAL_ACCESS_LIMIT_INTERVAL_MONTHLY; ?>" <?php echo ($param->interval == SOCIAL_ACCESS_LIMIT_INTERVAL_MONTHLY) ? 'selected="true"' : '' ; ?>>
					<?php echo JText::_('COM_EASYSOCIAL_ACCESS_LIMIT_INTEVAL_MONTHLY'); ?></option>
				<option value="<?php echo SOCIAL_ACCESS_LIMIT_INTERVAL_YEARLY; ?>" <?php echo ($param->interval == SOCIAL_ACCESS_LIMIT_INTERVAL_YEARLY) ? 'selected="true"' : '' ; ?>>
					<?php echo JText::_('COM_EASYSOCIAL_ACCESS_LIMIT_INTEVAL_YEARLY'); ?></option>
			</select>
		</span>
	</div>

</div>
