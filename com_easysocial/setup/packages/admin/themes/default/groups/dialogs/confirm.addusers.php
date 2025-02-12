<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
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
	<width>500</width>
	<height>300</height>
	<selectors type="json">
	{
		"{submitButton}"	: "[data-submit-button]",
		"{cancelButton}"	: "[data-cancel-button]",
		"{addMembersForm}"	: "[data-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_GROUPS_CONFIRM_ADD_MEMBERS_DIALOG_TITLE'); ?></title>
	<content>
		<form name="addMembers" method="post" action="index.php" data-form>
			<p><?php echo JText::_('COM_EASYSOCIAL_GROUPS_CONFIRM_ADD_MEMBERS_DIALOG_MESSAGE'); ?></p>

			<ul class="g-list-unstyled es-avatar-list es-avatar-adduser">
				<?php foreach ($members as $member) { ?>
				<li>
					<div class="media">
						<div class="media-object pull-left">
							<span class="es-avatar-rounded es-avatar ml-15 mr-10  mt-5 pull-left">
								<img src="<?php echo $member['avatar']; ?>" width="16" align="left" />
							</span>
						</div>
						<div class="media-body pt-10">
							<span><?php echo $member['name']; ?></span>
						</div>
					</div>



				</li>
				<?php } ?>
			</ul>


		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON'); ?></button>
	</buttons>
</dialog>
