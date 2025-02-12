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
<div class="<?php echo $static ? '' : 'o-form-group';?> o-form-group--float<?php echo $static ? ' is-focused' : '';?><?php echo $hasInfo ? ' has-trailing-icon' : ''; ?>">
	<?php if ($hasInfo) { ?>
	<span class="o-form-group__icon"
		data-placement="bottom"
		data-title="<?php echo $label; ?>"
		data-content="<?php echo $info; ?>"
		data-es-provide="popover"
	><i class="fas fa-question-circle"></i></span>
	<?php } ?>

	<?php echo $this->html('form.' . $type, $name, $id, $value, array('class' => 'o-form-control ' . $inputClass, 'attr' => 'autocomplete="off" ' . $inputAttributes)); ?>
	<label class="o-control-label" for="<?php echo $id;?>"><?php echo $label;?></label>
</div>
