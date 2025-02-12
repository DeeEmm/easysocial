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
<div class="panel-table">
	<table class="app-table table">
		<thead>
			<tr>
				<th colspan="2">
					<?php echo JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_APPS'); ?><br />
					<span style="font-weight:normal;"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_APPS_INFO');?></span>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($apps as $app) { ?>
			<tr>
				<td>
					<input type="checkbox" name="apps[]" id="<?php echo $app->id;?>" value="<?php echo $app->id;?>" data-id="<?php echo $app->id;?>"  data-profiles-app-item-shadow 
						<?php echo (in_array($app->id, $selectedApps)) ? ' checked="checked"' : '';?>
					/>
				</td>
				<td>
					<label for="<?php echo $app->id;?>">
						<b><?php echo $app->title;?></b>
						<div style="font-weight: normal;"><?php echo $app->getMeta()->desc;?></div>
					</label>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>