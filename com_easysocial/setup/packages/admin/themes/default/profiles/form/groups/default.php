<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="panel-table">
	<table class="app-table table">
		<thead>
			<tr>
				<th colspan="2">
					<a href="javascript:void(0);" class="t-lg-pull-right btn btn-es-primary-o" data-insert-groups><?php echo JText::_('COM_EASYSOCIAL_INSERT_GROUPS');?></a>

					<?php echo JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_GROUPS'); ?><br />
					<span style="font-weight:normal;"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_GROUPS_INFO');?></span>
				</th>
			</tr>
		</thead>
		<tbody data-profile-groups>
			<?php if ($defaultGroups) { ?>
				<?php echo $this->output('admin/profiles/form/groups/item', array('clusters' => $defaultGroups)); ?>
			<?php } ?>

			<tr class="is-empty" data-groups-empty <?php echo !$defaultGroups ? '' : 'style="display: none;"';?>>
				<td colspan="2">
					<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_PROFILES_DEFAULT_GROUPS_EMPTY', 'fa-users'); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>