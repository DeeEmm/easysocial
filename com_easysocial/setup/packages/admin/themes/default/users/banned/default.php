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
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div id="usersTable" class="panel-table" data-users>
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'name', 'COM_EASYSOCIAL_USERS_NAME', $ordering , $direction ); ?>
					</th>

					<th width="15%">
						<?php echo $this->html('grid.sort', 'username', 'COM_EASYSOCIAL_USERS_USERNAME', $ordering , $direction ); ?>
					</th>

					<th width="15%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_PERIOD'); ?>
					</th>

					<th width="15%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_TIME_REMAINING'); ?>
					</th>

					<th width="5%" class="center">
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
				<tr>
					<td>
						<?php echo $this->html('grid.id', $i, $user->id); ?>
					</td>

					<td style="text-align:left;">

						<div class="es-social-icons-wrapper">
							<?php foreach ($userTypes as $userType) { ?>
								<div class="">
									<i class="fab fa-<?php echo $userType;?>" data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf('COM_EASYSOCIAL_USERS_USER_ACCOUNT_TYPE', $userType);?>"></i>
								</div>
							<?php } ?>
						</div>

						<a href="<?php echo FRoute::_( 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id );?>"
							data-user-insert
							data-id="<?php echo $user->id;?>"
							data-alias="<?php echo $userObj->getAlias(true, true);?>"
							data-title="<?php echo $this->html( 'string.escape' , $userObj->name );?>"
							data-avatar="<?php echo $this->html( 'string.escape' , $userObj->getAvatar( SOCIAL_AVATAR_MEDIUM ) );?>"
						>
							<?php echo $userObj->name;?>
						</a>
					</td>

					<td>
						<?php echo $userObj->username;?>
					</td>

					<td class="center">
						<?php if ($user->period == 0) { ?>
							<?php echo JText::_('COM_ES_PERMANENT'); ?>
						<?php } else { ?>
							<?php echo JText::sprintf('COM_ES_BAN_PERIOD', $user->period); ?>
						<?php } ?>
					</td>

					<td class="center">
						<?php if ($user->period == 0) { ?>
							&mdash;
						<?php } else { ?>
							<?php
							$now = new DateTime();
							$future = new DateTime($user->block_date);
							$interval = $future->diff($now);
							$lapsed = $user->period - $interval->format('%i');
							?>
							<?php echo JText::sprintf('COM_ES_BAN_PERIOD', $lapsed); ?>
						<?php } ?>
					</td>

					<td class="center">
						<?php echo $userObj->id;?>
					</td>
				</tr>
				<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
			<tr>
				<td class="empty center" colspan="6">
					<div><?php echo JText::_('COM_ES_BANNED_USERS_EMPTY'); ?></div>
				</td>
			</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="6">
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
	<input type="hidden" name="controller" value="users" />
</form>
