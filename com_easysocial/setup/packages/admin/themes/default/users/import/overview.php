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
<form name="adminForm" id="adminForm" method="post" data-table-grid enctype="multipart/form-data">
	<div class="row">

		<div class="col-md-6" data-import-overview>
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_FROM_CSV_SETTINGS'); ?>

				<div class="panel-body">

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_TO_PROFILE', true, '', 5); ?>

						<div class="col-md-7">
							<div class="t-lg-mt--sm">
								<p><?php echo $profile->getTitle(); ?></p>
							</div>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_AUTO_GENERATE_PASSWORD', true, '', 5); ?>

						<div class="col-md-7">
							<div class="t-lg-mt--sm">
								<p><?php echo $importOptions['autopassword'] ? JText::_('COM_EASYSOCIAL_GRID_YES') : JText::_('COM_EASYSOCIAL_GRID_NO'); ?></p>
							</div>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_AUTO_APPROVE_USER', true, '', 5); ?>

						<div class="col-md-7">
							<div class="t-lg-mt--sm">
								<p><?php echo $importOptions['autoapprove'] ? JText::_('COM_EASYSOCIAL_GRID_YES') : JText::_('COM_EASYSOCIAL_GRID_NO'); ?></p>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="panel" data-import-overview>
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_FROM_CSV_OVERVIEW'); ?>

				<div class="panel-body t-lg-p--no">
					<div class="app-table-wrapper">
						<table class="app-table table">
							<thead>
								<tr>
									<?php foreach ($fields as $field) { ?>
									<th colspan="2">
										<?php echo $field->title; ?>
									</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php $count = 1; ?>
								<?php foreach ($data as $items) { ?>
								<?php
									$count++;
									$total--;
								?>
								<tr>
									<?php $i = 0; ?>
									<?php foreach ($items as $item) { ?>
										<?php if (isset($fieldIds[$i]) && $fieldIds[$i]) { ?>
										<td colspan="2"><?php echo $item; ?></td>
										<?php } ?>
										<?php $i++; ?>
									<?php } ?>
								</tr>
									<?php if ($count > 10) { ?>
										<tr>
											<td colspan="<?php echo $totalColumn * 2; ?>" style="text-align:center;"><?php echo JText::sprintf('COM_ES_AND_MORE_ITEMS', $total); ?></td>
										</tr>
										<?php break; ?>
									<?php } ?>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="form-group">

					</div>

					<div class="o-form-actions t-lg-m--no">
						<button class="btn btn-default btn-sm" type="submit">&laquo; <?php echo JText::_('COM_ES_CANCEL_AND_BACK');?></button>
						<button class="btn btn-primary btn-sm pull-right" type="button" data-user-import-begin><?php echo JText::_('COM_ES_BEGIN_IMPORT_BUTTON');?> &raquo;</button>
					</div>
				</div>
			</div>

			<div class="panel t-hidden" data-import-processing>
				<?php echo $this->html('panel.heading', 'COM_ES_USER_IMPORT_CSV'); ?>

				<div class="panel-body t-lg-p--no is-empty">
					<div class="o-empty o-empty--bg-no">
						<div class="o-empty__content" data-import-progress>
							<div class="t-lg-mb--lg">
								<?php echo JText::_('COM_ES_USER_IMPORT_PROGRESS'); ?> <span data-progress-text>0%</span>
							</div>
							<div class="es-progress-wrap" style="width: 50vw">
								<div class="progress">
									<div style="width: 0%" class="progress-bar progress-bar-success" data-progress-bar></div>
								</div>
							</div>
						</div>
						<div class="o-empty__content t-hidden" data-import-success>
							<div class="o-empty__text t-lg-mb--lg"><?php echo JText::sprintf('COM_ES_USERS_IMPORT_PROCESS_SUCCESS', count($data)); ?></div>
							<i class="o-empty__icon fa fa-check-circle t-text--success"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="panel t-hidden" data-import-summary>

				<div class="panel-body t-lg-p--no">
					<ul class="o-tabs o-tabs--horizontal">
						<li class="o-tabs__item active" data-table-filter data-type="success">
							<a href="javascript:void(0);" class="o-tabs__link t-lg-p--md"><?php echo JText::_('COM_ES_USER_IMPORT_SUCCESS'); ?> (<span class="t-text--success" data-import-counter-success>0</span>)</a>
						</li>
						<li class="o-tabs__item" data-table-filter data-type="failed">
							<a href="javascript:void(0);" class="o-tabs__link t-lg-p--md"><?php echo JText::_('COM_ES_USER_IMPORT_FAILED'); ?> (<span class="t-text--danger" data-import-counter-failed>0</span>)</a>
						</li>
					</ul>
					<div class="" data-table-import data-type="success">
						<div class="app-table-wrapper">
							<table class="app-table table">
								<thead>
									<tr>
										<?php foreach ($fields as $field) { ?>
										<th colspan="2">
											<?php echo JText::_($field->title); ?>
										</th>
										<?php } ?>
									</tr>
								</thead>
								<tbody data-table-content>
								</tbody>
							</table>
						</div>
					</div>
					<div class="t-hidden" data-table-import data-type="failed">
						<div class="app-table-wrapper">
							<table class="app-table table">
								<thead>
									<tr>
										<?php foreach ($fields as $field) { ?>
										<th colspan="2">
											<?php echo JText::_($field->title); ?>
										</th>
										<?php } ?>
										<th colspan="2">
											<?php echo JText::_('COM_ES_ERROR_MESSAGE'); ?>
										</th>
									</tr>
								</thead>
								<tbody data-table-content>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<?php echo $this->html('form.token'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="users" />
	<input type="hidden" name="task" value="import" />
	<input type="hidden" name="profileId" value="<?php echo $profile->id; ?>" />
	<input type="hidden" name="fields" value="<?php echo $profile->id; ?>" />
	<input type="hidden" name="previousData" value="<?php echo $this->html('string.escape', json_encode($previousData)); ?>" />
</form>
