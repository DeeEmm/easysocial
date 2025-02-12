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
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.pendingUsers', 'filter', $filter); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.profiles', 'profile', $profile); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $limit); ?>
			</div>
		</div>
		<?php } ?>
	</div>

	<div id="pendingUsersTable" class="panel-table">
		<table class="app-table table" data-pending-users>
			<thead>
				<tr>
					<th width="5">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<th style="text-align: left;">
						<?php echo $this->html('grid.sort', 'name', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_NAME'), $ordering , $direction ); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_PROFILE_TYPE'); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE'); ?>
					</th>
					<th width="20%" class="center">
						<?php echo $this->html('grid.sort', 'block', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_REGISTRATION_DATE'), $ordering, $direction); ?>
					</th>
					<th width="15%" class="center">
						<?php echo $this->html('grid.sort', 'email', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_EMAIL'), $ordering, $direction); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_TYPE'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_USERS_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if( $users ){ ?>
				<?php $i = 0; ?>
				<?php foreach ($users as $user) {

					$hasOtherOauthClientAssociated = false;
					$userTypes = array('joomla');

					if ($user->type != 'joomla') {
						$oauthModel = ES::model('Oauth');
						$hasOtherOauthClientAssociated = $oauthModel->getOauthClientAssociatedList($user->id);

						if ($hasOtherOauthClientAssociated) {

							// reset Joomla user type if this user has associated with their social account
							$userTypes = array();

							foreach ($hasOtherOauthClientAssociated as $oauthData) {
								$userTypes[] = $oauthData->client;
							}
						}
					}
				?>

				<tr data-pending-item
					data-name="<?php echo $user->getName();?>"
					data-id="<?php echo $user->id;?>"
					data-avatar="<?php echo $user->getAvatar();?>"
					data-email="<?php echo $user->email;?>">
					<td>
						<?php echo $this->html('grid.id', $i++ , $user->id); ?>
					</td>
					<td align="left">
						<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $user->id;?>" data-user-item-insertLink>
							<?php echo $user->name;?>
						</a>
						<div class="t-lg-mt--md">
							<a href="javascript:void(0);" class="btn btn-xs btn-es-primary-o" data-pending-approve><?php echo JText::_('COM_EASYSOCIAL_USER_APPROVE_BUTTON'); ?></a>
							<a href="javascript:void(0);" class="btn btn-xs btn-es-danger-o" data-pending-reject><?php echo JText::_('COM_EASYSOCIAL_USER_REJECT_BUTTON'); ?></a>
						</div>
					</td>
					<td class="center">
						<a href="index.php?option=com_easysocial&view=profiles&layout=form&id=<?php echo $user->getProfile()->id;?>">
							<?php echo $user->getProfile()->get('title'); ?>
						</a>
					</td>
					<td class="center">
						<?php if ($user->state == SOCIAL_REGISTER_APPROVALS) { ?>
							<?php echo JText::_('COM_ES_PENDING_APPROVAL'); ?>
						<?php } elseif ($user->state == SOCIAL_REGISTER_CONFIRMATION_APPROVAL) { ?>
							<?php echo JText::_('COM_ES_PENDING_CONFIRMATION_EMAIL_ACCOUNT'); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_ES_PENDING_USER_ACTIVATION'); ?>
						<?php } ?>
					</td>
					<td class="center">
						<?php echo ES::date($user->registerDate)->toSql(true); ?>
					</td>
					<td class="center">
						<a href="mailto:<?php echo $user->email;?>" target="_blank"><?php echo $user->email;?></a>
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
						<?php echo $user->id;?>
					</td>
				</tr>
				<?php } ?>

			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="8" class="center empty">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_USERS_NO_PENDING_USERS'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8">
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
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="users" />
	<input type="hidden" name="layout" value="pending" />
	<input type="hidden" name="controller" value="users" />
</form>
