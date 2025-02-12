<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>400</width>
	<height>200</height>
	<selectors type="json">
	{
		"{closeButton}"  : "[data-close-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function()
		{
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ERROR_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ERRORS_IN_FORM'); ?></p>

		<?php if ($stepData) { ?>
			<?php foreach ($stepData as $step) { ?>
				<div class="t-lg-mb--md">

					<strong><?php echo JText::_($step->title); ?></strong>

					<?php if ($stepData) { ?>
						<?php foreach ($fieldData as $field) { ?>

							<?php if ($step->stepId == $field->stepId) { ?>
								<div class="t-fs--sm t-text--danger">
									<span>&mdash;</span>&nbsp;<?php echo JText::_($field->title); ?>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</div>

			<?php } ?>
		<?php } ?>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
