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
<dialog>
	<width>600</width>
	<height>400</height>
	<selectors type="json">
	{
		"{removeItem}": "[data-remove-item]",
		"{cancelButton}": "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		
		"{removeItem} click" : function(button, event) {
			var item = $(button).parents('[data-reporter-item]');
			var id = item.data('id');

			EasySocial.ajax('admin/controllers/reports/removeItem', {
				"id": id
			}).done(function(result) {
				item.remove();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_REPORTS_VIEW_REPORTS_DIALOG_TITLE'); ?></title>
	<content>
		<ul class="g-list-unstyled es-reports" data-reporters>
			<?php if ($reporters) { ?>
				<?php foreach( $reporters as $report ){ ?>
				<li class="es-report" data-reporter-item data-id="<?php echo $report->id;?>">

						<div class="es-report-msg">
						<?php if (!$report->message) { ?>
							<i><?php echo JText::_('COM_EASYSOCIAL_REPORTS_USER_DID_NOT_PROVIDE_ANY_MESSAGE'); ?></i>
						<?php } else { ?>
							<?php echo $report->get( 'message' ); ?>
						<?php } ?>
					</div>

					<div class="pull-left es-report-reporter">
						<?php echo $this->html('avatar.mini', $report->getUser()->getName(), '', $report->getUser()->getAvatar(), 'md', 'pull-left', '', false); ?>

						<span class="es-report-username t-lg-ml--md">
							<a href="<?php echo $report->getUser()->getPermalink(true, true);?>" target="_blank"><?php echo $report->getUser()->getName();?></a>
						</span>

						<span class="es-report-ip">
							<?php echo $report->ip;?>
						</span>

					</div>

					<div class=" pull-right es-report-action">
						<a class="btn btn-es-danger btn-sm btn-remove" data-remove-item>
							<i class="fa fa-times"></i>
							<?php echo JText::_('COM_EASYSOCIAL_REMOVE_REPORT_BUTTON'); ?>
						</a>
					</div>
				</li>
				<?php } ?>
			<?php } ?>
		</ul>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>