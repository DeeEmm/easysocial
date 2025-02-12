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
<form name="adminForm" id="adminForm" method="post" data-table-grid enctype="multipart/form-data">
	<div class="row">
		<div class="col-lg-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_IMPORT_USERS_FROM_CSV', JText::sprintf('COM_ES_IMPORT_USERS_FROM_CSV_DESC', '<a href="https://stackideas.com/docs/easysocial/administrators/users/importing-users-with-csv" target="_blank">', '</a>')); ?>

				<div class="panel-body">
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_SELECT_CSV_FILE', true, '', 4); ?>

						<div class="col-md-8">
							<div style="clear:both;" class="t-lg-mb--xl">
								<input type="file" name="user_import_csv" id="user_import_csv" class="input" style="width:265px;" accept=".csv" data-uniform />
							</div>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_IMPORT_USERS_SELECT_PROFILE', true, '', 4); ?>

						<div class="col-md-8">
							<div class="row">
								<div class="col-lg-12">
									<div class="o-select-group o-select-group--xinline">
										<select name="profileId" class="o-form-control">
											<?php foreach ($profiles as $profile) { ?>
												<option value="<?php echo $profile->id;?>"><?php echo $profile->get('title');?></option>
											<?php } ?>
										</select>
										<label for="" class="o-select-group__drop"></label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-8 col-lg-offset-4">
							<button class="btn btn-primary btn-sm" type="submit" data-user-import-csv><?php echo JText::_('COM_ES_CONFIRM_AND_UPLOAD');?> &raquo;</button>

							<div class="help-block small">
								<?php echo JText::_('COM_ES_IMPORT_USERS_FROM_CSV_INFO');?>
							</div>
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
</form>
