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
<form name="adminForm" id="adminForm" method="post" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.profiles', 'profile' , $profile); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.usergroups', 'group' , $group); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'published', $published); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
		<?php } ?>
	</div>

	<div id="usersTable" class="panel-table" data-users>
		<table class="app-table table">
			<thead>
				<tr>
					<?php if( $multiple ){ ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th>
						<?php echo $this->html('grid.sort', 'name', 'COM_EASYSOCIAL_USERS_NAME', $ordering , $direction ); ?>
					</th>

					<th width="20%">
						<?php echo $this->html('grid.sort', 'username', 'COM_EASYSOCIAL_USERS_USERNAME', $ordering , $direction ); ?>
					</th>

					<?php if ($this->tmpl != 'component') { ?>
					<th width="15%" class="center">
						<?php echo $this->html('grid.sort', 'lastVisitDate', 'COM_ES_TABLE_COLUMN_LAST_VISIT_DATE', $ordering , $direction ); ?>
					</th>

					<th width="15%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_PROFILE_TYPE'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_TYPE'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'block', 'COM_EASYSOCIAL_TABLE_COLUMN_ENABLED', $ordering , $direction ); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort' , 'block' , 'COM_EASYSOCIAL_TABLE_COLUMN_ACTIVATED', $ordering , $direction ); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'verified', 'COM_ES_TABLE_COLUMN_VERIFIED', $ordering , $direction ); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'points', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_POINTS'), $ordering, $direction); ?>
					</th>
					<?php } ?>

					<th width="<?php echo $this->tmpl == 'component' ? ' 10%' : '5%';?>" class="center">
						<?php echo $this->html('grid.sort', 'id', 'COM_EASYSOCIAL_USERS_ID', $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($users) { ?>
				<?php $i = 0; ?>
				<?php
				foreach ($users as $user) {
					$userObj = ES::user($user->id);
					$hasOtherOauthClientAssociated = false;
					$userTypes = array('joomla');

					if ($userObj->type != 'joomla') {
						$oauthModel = ES::model('Oauth');
						$hasOtherOauthClientAssociated = $oauthModel->getOauthClientAssociatedList($userObj->id);

						if ($hasOtherOauthClientAssociated) {

							// reset Joomla user type if this user has associated with their social account
							$userTypes = array();

							foreach ($hasOtherOauthClientAssociated as $oauthData) {
								$userTypes[] = $oauthData->client;
							}
						}
					}
				?>
				<tr data-user-item
					data-name="<?php echo $this->html('string.escape', $userObj->getName());?>"
					data-title="<?php echo $this->html('string.escape', $userObj->name);?>"
					data-alias="<?php echo $userObj->getAlias(true, true);?>"
					data-avatar="<?php echo $userObj->getAvatar(SOCIAL_AVATAR_MEDIUM);?>"
					data-email="<?php echo $userObj->email;?>"
					data-id="<?php echo $userObj->id;?>">
					<?php if ($multiple ){ ?>
					<td>
						<?php echo $this->html('grid.id', $i, $users[$i]->id); ?>
					</td>
					<?php } ?>

					<td style="text-align:left;">
						<a href="<?php echo FRoute::_( 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id );?>"
							data-user-insert
							data-id="<?php echo $user->id;?>"
							data-alias="<?php echo $userObj->getAlias(true, true);?>"
							data-title="<?php echo $this->html('string.escape', $userObj->name);?>"
							data-avatar="<?php echo $this->html('string.escape', $userObj->getAvatar(SOCIAL_AVATAR_MEDIUM));?>"
						>
							<?php echo $userObj->name;?>
						</a>

						<?php if ($userObj->require_reset) { ?>
							<span class="t-fs--sm label label-warning">Reset password required</span>
						<?php } ?>
					</td>

					<td>
						<?php echo $userObj->username;?>
					</td>

					<?php if ($this->tmpl != 'component') { ?>
					<td class="center">
						<?php
							$lastVisitDate = $userObj->getLastVisitDate(null, true);

							if ($lastVisitDate === '0000-00-00 00:00:00') {
								echo JText::_('JNEVER');
							} else {
								echo $lastVisitDate;
							}
						?>
					</td>

					<td class="center">
						<?php $title = $userObj->getProfile()->title; ?>

						<?php if( $title ){ ?>
							<a href="<?php echo FRoute::_( 'index.php?option=com_easysocial&view=profiles&layout=form&id=' . $userObj->getProfile()->id );?>"><?php echo JText::_($title); ?></a>
						<?php } else { ?>
							<?php echo JText::_( 'COM_EASYSOCIAL_NOT_AVAILABLE' ); ?>
						<?php } ?>
					</td>

					<td class="center">
						<div class="es-social-icons-wrapper">
							<?php foreach ($userTypes as $userType) { ?>
								<div class="">
									<i class="fab fa-<?php echo $userType;?>" data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf('COM_EASYSOCIAL_USERS_USER_ACCOUNT_TYPE', $userType);?>"></i>
								</div>
							<?php } ?>
						</div>
					</td>

					<td class="center">
						<?php if( $userObj->state == SOCIAL_USER_STATE_PENDING ){ ?>
							<a class="es-state-locked" href="javascript:void(0);"
							data-original-title="<?php echo JText::_('COM_EASYSOCIAL_USERS_USER_IS_PENDING_MODERATION');?>"
							data-es-provide="tooltip"
							></a>
						<?php } else { ?>
							<?php echo $this->html('grid.userPublished', $this->my->id != $userObj->id, $userObj, 'users'); ?>
						<?php }?>
					</td>

					<td class="center">
						<?php if ($userObj->activation && !$userObj->getUserActivationParams()) { ?>
							<a href="javascript:void(0);" class="es-state-unactivated" data-activate-user data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_USERS_USER_IS_PENDING_ACTIVATION');?>"></a>
						<?php } else { ?>
							<a href="javascript:void(0);" class="es-state-publish" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_USERS_USER_IS_ACTIVATED');?>"></a>
						<?php } ?>
					</td>

					<td class="center">
						<?php echo $this->html('grid.published', $userObj, 'verifications', 'verified', ['enable', 'disable']); ?>
					</td>

					<td class="center">
						<?php echo $userObj->points; ?>
					</td>

					<?php } ?>

					<td class="center">
						<?php echo $userObj->id;?>
					</td>
				</tr>
				<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
			<tr>
				<td class="empty center" colspan="11">
					<div><?php echo JText::_( 'COM_EASYSOCIAL_USERS_NO_USERS_FOUND_BASED_ON_SEARCH_RESULT' ); ?></div>
				</td>
			</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="11">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />

	<input type="hidden" name="excludeClusterMembers" value="<?php echo $excludeClusterMembers; ?>" />
	<input type="hidden" name="clusterId" value="<?php echo $clusterId; ?>" />

	<?php echo $this->html('form.action', 'users', '', 'users'); ?>

	<?php
	// Used by verifications
	echo $this->html('form.hidden', 'verification_type', SOCIAL_TYPE_USER);
	?>
</form>

<?php if ($this->tmpl != 'component') { ?>
<div id="toolbar-actions" class="btn-wrapper t-hidden" data-toolbar-actions="others">
	<div class="dropdown">
		<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
			<span class="icon-cog"></span> <?php echo JText::_('Other Actions');?> &nbsp;<span class="caret"></span>
		</button>

		<ul class="dropdown-menu">
			<li>
				<a href="javascript:void(0);" data-action="setVerified">
					<?php echo JText::_('COM_ES_SET_VERIFIED'); ?>
				</a>
			</li>
			<li>
				<a href="javascript:void(0);" data-action="removeVerified">
					<?php echo JText::_('COM_ES_REMOVE_VERIFIED'); ?>
				</a>
			</li>
			<li class="divider">
			<li>
				<a href="javascript:void(0);" data-action="resendActivate">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_BUTTON_RESEND_ACTIVATION'); ?>
				</a>
			</li>
			<li class="divider">
			</li>
			<li>
				<a href="javascript:void(0);" data-action="assignBadge">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_ASSIGN_BADGE'); ?>
				</a>
			</li>
			<li class="divider">
			</li>
			<li>
				<a href="javascript:void(0);" data-action="assignPoints">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_ASSIGN_POINTS'); ?>
				</a>
			</li>
			<li>
				<a href="javascript:void(0);" data-action="resetPoints">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_RESET_POINTS'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
<?php } ?>
