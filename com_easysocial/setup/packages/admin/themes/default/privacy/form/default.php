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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row">

		<div class="col-md-6">
			<div class="panel">
				<div class="panel-head">
					<b><?php echo JText::_( 'COM_EASYSOCIAL_PRIVACY_FORM_GENERAL' );?></b>
				</div>

				<div class="panel-body">
					<div class="form-group">
						<label for="page_title" class="col-md-5">
							<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_FORM_DEFAULT');?>
							<i data-es-provide="popover"
								data-placement="bottom"
								data-title="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_FORM_DEFAULT', true);?>"
								data-content="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_FORM_DEFAULT_DESC', true); ?>"
								class="fa fa-question-circle pull-right" data-original-title></i>
						</label>

						<div class="col-md-7">
							<select class="input-full" value="<?php echo $privacy->value;?>" name="value">
								<?php foreach ($options as $option) {
									// admin should not select custom field as default privacy value.
									if ($option->value == SOCIAL_PRIVACY_FIELD) {
										continue;
									}
								?>
								<option value="<?php echo $option->value; ?>" <?php echo $privacy->value == $option->value ? 'selected="selected"' : ''; ?>><?php echo $option->label; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<?php echo $this->html('form.action', 'privacy'); ?>
	<input type="hidden" name="id" value="<?php echo $privacy->id; ?>" />
</form>
