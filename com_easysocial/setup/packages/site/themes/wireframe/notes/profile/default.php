<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-es-container data-profile-user-apps-notes data-app-id="<?php echo $app->id;?>">
	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/notes/profile/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-notes <?php echo !$notes ? ' is-empty' : '';?>" data-notes-list>
			<?php if ($notes) { ?>
				<?php foreach ($notes as $note) { ?>
					<?php echo $this->output('site/notes/profile/item', array('note' => $note, 'user' => $user)); ?>
				<?php } ?>
			<?php } ?>

			<?php echo $this->html('html.emptyBlock', JText::sprintf('APP_NOTES_EMPTY_NOTES_PROFILE', $user->getName()), 'fa-info-circle'); ?>
		</div>
	</div>
</div>
