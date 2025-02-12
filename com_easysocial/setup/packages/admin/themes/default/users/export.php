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
<form name="adminForm" id="adminForm" method="post" data-table-grid target="_blank">
	<div class="row">
		<div class="col-lg-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_EXPORT_USERS_INTO_CSV'); ?>

				<div class="panel-body">
					<div class="form-group">
						<label for="title" class="col-md-4">
							<?php echo JText::_('COM_EASYSOCIAL_EXPORT_USERS_SELECT_PROFILE');?>
							<i class="fa fa-question-circle pull-right"
								<?php echo $this->html('bootstrap.popover', JText::_('COM_EASYSOCIAL_EXPORT_USERS_SELECT_PROFILE'), JText::_('COM_EASYSOCIAL_EXPORT_USERS_SELECT_PROFILE_DESC') , 'bottom'); ?>
							></i>
						</label>
						<div class="col-md-8">
							<div class="row">
								<div class="col-lg-12">
									<select name="profileId" class="o-form-control">
										<?php foreach ($profiles as $profile) { ?>
											<option value="<?php echo $profile->id;?>"><?php echo $profile->get('title');?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-8 col-lg-offset-4">
							<button class="btn btn-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_EXPORT_USERS_BUTTON');?> &raquo;</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<?php echo $this->html('form.token'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="users" />
	<input type="hidden" name="task" value="export" />
</form>
