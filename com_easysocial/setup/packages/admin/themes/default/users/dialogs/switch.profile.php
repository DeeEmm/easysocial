<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>450</width>
	<height>200</height>
	<selectors type="json">
	{
		"{submitButton}": "[data-submit-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-switch-profile-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_USERS_SWITCH_PROFILE_DIALOG_TITLE'); ?></title>
	<content>
		<div class="clearfix">
			<form name="switchProfile" method="post" action="index.php" data-switch-profile-form>
				<p><?php echo JText::_('COM_EASYSOCIAL_USERS_SWITCH_PROFILE_DIALOG_DESC');?></p>

				<div class="form-group">
					<label for="total" class="col-md-3 t-fs--sm"><?php echo JText::_('COM_EASYSOCIAL_USERS_SELECT_PROFILE');?></label>
					<div class="col-md-9">
						<?php echo $this->html('form.profiles', 'profile'); ?>
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-3 col-md-9">
						<div class="checkbox">
							<label for="switch_groups">
								<input type="checkbox" value="1" name="switch_groups" id="switch_groups" />
								<span><?php echo JText::_('COM_EASYSOCIAL_USERS_ALSO_SWITCH_JOOMLA_GROUPS');?></span>
							</label>
						</div>
					</div>
				</div>
				
				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="users" />
				<input type="hidden" name="task" value="switchProfile" />
				<input type="hidden" name="<?php echo ES::token();?>" value="1" />

				<?php foreach ($ids as $id) { ?>
				<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
				<?php } ?>
			</form>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SWITCH_PROFILE_BUTTON'); ?></button>
	</buttons>
</dialog>
