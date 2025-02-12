<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data" data-badges-discover>
	<div class="row">
		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_BADGES_INSTALL_SCAN'); ?>

				<div class="panel-body">
					<table class="table table-striped table-noborder">
						<tr>
							<td>
								<?php echo JPATH_ROOT;?>/administrator/components/
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JPATH_ROOT;?>/media/com_easysocial/apps/users/
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JPATH_ROOT;?>/media/com_easysocial/apps/fields/
							</td>
						</tr>
					</table>

					<div class="mt-20 small">
						<span class="label label-danger small"><?php echo JText::_( 'COM_EASYSOCIAL_FOOTPRINT_NOTE' );?>:</span> <?php echo JText::_( 'COM_EASYSOCIAL_DISCOVERY_FOOTPRINT' );?>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_BADGES_DISCOVERY_RESULT'); ?>

				<div class="panel-body">
					<div class="es-progress-wrap t-hidden" data-progress>
						<div class="progress">
							<div style="width: 0%;" class="progress-bar progress-bar-info"></div>
						</div>
						<div class="progress-result"></div>
					</div>

					<div class="discovery-log">
						<table class="table table-striped table-noborder" data-discovery-result>
							<tr>
								<td>
									<?php echo JText::_('COM_EASYSOCIAL_NO_ITEMS_DISCOVERED_YET'); ?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'badges'); ?>
</form>
