<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if (!empty($field->output)) { ?>
	<div class="<?php echo $field->isConditional() ? 't-hidden' : ''; ?>"
		data-isconditional="<?php echo $field->isConditional(); ?>"
		data-conditions="<?php echo ES::string()->escape($field->getConditions(false)); ?>"
		data-conditions-logic="<?php echo $field->getConditionsLogic(); ?>"
		data-field-item="<?php echo $field->element; ?>"
		data-id="<?php echo $field->id; ?>"
		data-required="<?php echo $field->required; ?>"
		data-name="<?php echo SOCIAL_FIELDS_PREFIX . $field->id; ?>"
	>
		<?php echo $field->output; ?>
		<input type="hidden" name="cid[]" value="<?php echo $field->id;?>"/>
	</div>
<?php } ?>