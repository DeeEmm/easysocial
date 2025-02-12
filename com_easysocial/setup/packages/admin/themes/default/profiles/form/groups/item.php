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
<?php foreach ($clusters as $group) { ?>
<tr data-groups-item>
	<td width="5%">
		<a href="javascript:void(0);" class="btn btn-es-danger-o btn-xs" data-groups-remove>
			<i class="fa fa-times"></i>
		</a>
	</td>
	<td>
		<?php echo $group->title;?>
		<input type="hidden" name="params[default_groups][]" value="<?php echo $group->id;?>" data-groups-id />
	</td>
</tr>
<?php } ?>