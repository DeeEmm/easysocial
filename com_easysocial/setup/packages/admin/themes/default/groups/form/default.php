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
<form name="adminForm" id="adminForm" class="groupsForm" method="post" enctype="multipart/form-data" data-groups-form data-table-grid>
	<div class="es-user-form">
		<div class="wrapper accordion">
			<div class="tab-box tab-box-alt">
				<div class="tabbable">
					<?php if (!$isNew) { ?>
					<ul id="userForm" class="nav nav-tabs nav-tabs-icons nav-tabs-side">
						<li class="tabItem <?php if($activeTab == 'profile') { ?>active<?php } ?>" data-tabnav data-for="profile">
							<a href="#profile" data-es-toggle="tab">
								<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FORM_GROUP_DETAILS');?>
							</a>
						</li>
						<li class="tabItem <?php if($activeTab == 'members') { ?>active<?php } ?>" data-tabnav data-for="members">
							<a href="#members" data-es-toggle="tab">
								<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FORM_GROUP_MEMBERS');?>
							</a>
						</li>
					</ul>

					<div class="tab-content">
						<div id="profile" class="tab-pane <?php echo $activeTab == 'profile' ? 'active' : '';?>" data-tabcontent data-for="profile">
							<?php echo $this->includeTemplate('admin/groups/form/fields'); ?>
						</div>

						<div id="members" class="tab-pane <?php echo $activeTab == 'members' ? 'active' : '';?>" data-tabcontent data-for="members">
							<?php echo $this->includeTemplate('admin/groups/form/members'); ?>
						</div>
					</div>
					<?php } else { ?>
					<div class="tab-content">
						<div class="tab-pane active">
							<?php echo $this->includeTemplate('admin/groups/form/fields'); ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="groups" />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="id" value="<?php echo $group->id ? $group->id : ''; ?>" />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="activeTab" data-active-tab value="<?php echo $activeTab; ?>" />
	<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check>

	<?php echo JHTML::_('form.token');?>
</form>

<form data-form-add-members>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="groups" />
	<input type="hidden" name="task" value="addMembers" />
	<input type="hidden" name="id" value="<?php echo $group->id; ?>" />
	<input type="hidden" name="members" data-ids />
</form>
