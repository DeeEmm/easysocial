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
<div class="es-snackbar2">
	<div class="es-snackbar2__context">
		<div class="es-snackbar2__title">
			<?php echo $activeList->get('title');?>
		</div>
	</div>

	<?php if ($activeList->user_id == $this->my->id) { ?>
	<div class="es-snackbar2__actions">
		<div class="dropdown_" data-list-actions data-id="<?php echo $activeList->id;?>">

			<button type="button" class="btn btn-sm btn-es-default-o dropdown-toggle_" data-es-toggle="dropdown" aria-expanded="false">
				<?php echo JText::_('COM_ES_MANAGE_PLAYLIST_BUTTON');?>&nbsp; <i class="fa fa-caret-down"></i>
			</button>

			<ul class="dropdown-menu dropdown-menu-right dropdown-menu-lists dropdown-arrow-topright">
				<li>
					<a href="javascript:void(0);" data-add>
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_ADD');?>
					</a>
				</li>

				<li>
					<a href="<?php echo ESR::audios(array('layout' => 'playlistform', 'listId' => $activeList->id));?>">
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_EDIT');?>
					</a>
				</li>
				</li>
				<li>
					<a href="javascript:void(0);" data-delete>
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_DELETE');?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php } ?>
</div>
