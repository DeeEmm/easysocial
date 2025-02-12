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
<dialog>
	<width>600</width>
	<height>250</height>
	<selectors type="json">
	{
		"{submitButton}": "[data-submit-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-switch-category-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click": function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_SWITCH_CATEGORY_DIALOG_TITLE'); ?></title>
	<content>
		<div class="clearfix">
			<form name="switchCategory" method="post" action="index.php" data-switch-category-form>
				<p class="t-mb--lg">
					<?php echo JText::_('COM_ES_SWITCH_CATEGORY_DIALOG_DESC');?>
				</p>

				<div class="form-group">
					<label for="total" class="col-md-3 t-fs--sm"><?php echo JText::_('COM_ES_SELECT_CATEGORY');?></label>
					<div class="col-md-9">
						<?php echo $categories; ?>
					</div>
				</div>

				<p class="t-mt--lg">
					<?php echo JText::_('COM_ES_SWITCH_CATEGORY_DIALOG_FOOTNOTE');?>
				</p>

				<?php echo $this->html('form.action', $type, 'switchCategory'); ?>
				<?php if ($ids) { ?>
					<?php foreach ($ids as $id) { ?>
						<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
					<?php } ?>
				<?php } ?>
			</form>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SWITCH_CATEGORY_BUTTON'); ?></button>
	</buttons>
</dialog>
