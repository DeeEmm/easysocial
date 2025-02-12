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
<dialog>
	<width>450</width>
	<height>250</height>
	<selectors type="json">
	{
		"{saveButton}" : "[data-save-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-filter-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{saveButton} click" : function() {

			var hasError = false;
			var test = ['title'];
			$.each(test, function(idx, type) {
				var wrapper = $('[data-' + type + ']');
				var value = wrapper.find('input[name="' + type + '"]');

				if (value.val() == '') {
					wrapper.addClass('has-error');
					wrapper.find('[data-notice]').removeClass('help-block text-note').addClass('es-fields-error-note');

					hasError = true;
				} else {
					wrapper.removeClass('has-error');
					wrapper.find('[data-notice]').addClass('help-block text-note').removeClass('es-fields-error-note');
				}
			});

			if (hasError) {
				return;
			}

			var sitewide = $('[data-filter-sitewide]').is(':checked') ? 1 : 0;
			$('[data-filter-sitewide]').val(sitewide);

			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_FILTER_ADD_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_FILTER_ADD_CONFIRMATION'); ?></p>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-filter-form>
			<div class="alert" filter-form-notice style="display:none;"><?php echo JText::_( 'COM_EASYSOCIAL_ADVANCED_SEARCH_FILTER_WARNING_TITLE_EMPTY_SHORT' ); ?></div>

			<div class="o-form-group" data-title>
				<label for="filterName"><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_FILTER_TITLE'); ?></label>
				<input type="text" name="title" id="filterName" value="<?php echo $filter->id ? $filter->title : ''; ?>" class="o-form-control" data-filter-name />

				<div class="help-block text-note" data-notice><?php echo JText::_('COM_EASYSOCIAL_STREAM_FILTER_TITLE_DESC'); ?></div>
			</div>

			<?php if ($this->my->isSiteAdmin()) { ?>
			<div class="o-checkbox">
				<input type="checkbox" name="sitewideFilter" id="sitewideFilter" value="<?php echo $filter->id ? $filter->sitewide : '0'; ?>" data-filter-sitewide <?php echo $filter->sitewide === '1' ? 'checked' : '';?> />
				<label for="sitewideFilter"><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SAVE_AS_SITEWIDE_FILTER'); ?></label>
			</div>
			<?php } ?>

			<?php echo $this->html('form.action', 'search', 'addFilter'); ?>
			<input type="hidden" name="type" value="<?php echo $type;?>" />
			<input type="hidden" name="id" value="<?php echo $filter->id;?>" />

			<textarea name="data" class="t-hidden"><?php echo $data;?></textarea>
		</form>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-save-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?></button>
	</buttons>
</dialog>
