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
<div class="tab-box<?php echo $sidebarTabs ? ' tab-box-sidenav' : ' tab-box-alt';?>">
	<div class="tabbable">

		<ul class="nav nav-tabs nav-tabs-non-head <?php echo $sidebarTabs ? ' nav-tabs-side' : '';?>">
			<?php foreach ($forms as $form) { ?>
			<li class="tab-item <?php echo $form->active ? ' active' : '';?>" data-form-tabs-<?php echo $uid;?> data-item="<?php echo $form->id;?>">
				<a href="#<?php echo $form->id;?>-tabs" data-es-toggle="tab"><?php echo $form->title; ?></a>
			</li>
			<?php } ?>
		</ul>

		<div class="tab-content<?php echo $sidebarTabs ? ' tab-content-side' : '';?>">

			<?php foreach ($forms as $form) { ?>
				<div class="tab-pane <?php echo $form->active ? 'active in' : '';?>" id="<?php echo $form->id;?>-tabs">
					<div class="panel">
						<div class="panel-head">
							<div class="t-d--flex">
								<div class="t-flex-grow--1">
									<?php if ($form->title) { ?>
									<b class="panel-head-title"><?php echo $form->title;?></b>
									<?php } ?>
									<?php if ($form->desc) { ?>
									<div class="panel-info"><?php echo $form->desc;?></div>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php if ($form->fields) { ?>
						<div class="panel-body">
							<div class="o-form-horizontal">

								<?php foreach ($form->fields as $field) { ?>
								<div class="form-group">

									<?php if ($field->label) { ?>
										<?php echo $this->html('panel.label', $field->label, isset($field->tooltip) ? true : false, isset($field->tooltip) ? $field->tooltip : ''); ?>
									<?php } ?>

									<div class="col-md-7">
										<?php if ($field->output) { ?>
											<?php echo $field->output;?>
										<?php } else { ?>
											<?php echo $this->loadTemplate('admin/forms/types/' . $field->type, ['params' => $params, 'field' => $field]); ?>
										<?php } ?>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>

				</div>
			<?php } ?>
		</div>
	</div>
</div>
