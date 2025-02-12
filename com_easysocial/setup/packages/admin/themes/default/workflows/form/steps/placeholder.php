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
<div class="t-d--flex t-align-items--c t-py--md" data-form-actions>
	<div class="t-flex-grow--1">
		<a href="javascript:void(0);" data-field-new data-action="<?php echo $action;?>">
			<span class="btn btn-es-primary-o" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_BROWSE_FIELDS_TITLE');?>">
				<i class="fa fa-plus"></i>&nbsp; <?php echo JText::_('New Custom Field'); ?>
			</span>
		</a>

		<span class="t-ml--lg t-hidden" data-batch-actions>
			<span class="t-lg-mr--lg"><?php echo JText::_('With Selected');?>:</span>

			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-batch-move>
				<?php echo JText::_('Move Selected');?>
			</a>

			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-batch-delete>
				<?php echo JText::_('Delete Selected');?>
			</a>
		</span>
	</div>
</div>

