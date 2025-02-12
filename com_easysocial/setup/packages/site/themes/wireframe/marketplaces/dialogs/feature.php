<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},
		"{submitButton} click" : function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_MARKETPLACES_DIALOG_' . strtoupper($type) . '_LISTING_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_ES_MARKETPLACES_DIALOG_' . strtoupper($type) . '_LISTING_CONTENT', $listing->getTitle());?></p>

		<form data-form method="post" action="<?php echo JRoute::_('index.php');?>">
			<input type="hidden" name="id" value="<?php echo $listing->id;?>" />
			<input type="hidden" name="controller" value="marketplaces" />
			<input type="hidden" name="task" value="<?php echo $action; ?>" />

			<?php if ($returnUrl) { ?>
			<input type="hidden" name="from" value="<?php echo $returnUrl; ?>" />
			<?php } ?>
			<?php echo $this->html('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_YES_BUTTON'); ?></button>
	</buttons>
</dialog>
