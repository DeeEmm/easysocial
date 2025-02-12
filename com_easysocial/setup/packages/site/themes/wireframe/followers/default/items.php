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
<div class="es-followers-wrapper" data-followers-content>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php if ($filter == 'followers') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FOLLOWERS_FOLLOWERS_TITLE'); ?>
				<?php } ?>

				<?php if ($filter == 'following') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FOLLOWERS_FOLLOWING_TITLE'); ?>
				<?php } ?>

				<?php if ($filter == 'suggest') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FOLLOWERS_SUGGEST_TITLE'); ?>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="es-list-wrapper <?php echo !$users ? 'is-empty' : '';?>" data-followers-items>

		<div class="es-list">
			<?php if ($users) { ?>
				<?php foreach ($users as $user) { ?>
					<?php echo $this->html('listing.user', $user); ?>
				<?php } ?>
			<?php } ?>
		</div>

		<?php if ($filter == 'followers') { ?>
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FOLLOWERS_NO_FOLLOWERS_YET', 'fa-users'); ?>
		<?php } ?>

		<?php if ($filter == 'following') { ?>
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FOLLOWERS_NOT_FOLLOWING_YET', 'fa-users'); ?>
		<?php } ?>

		<?php if ($filter == 'suggest') { ?>
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FOLLOWERS_NO_ONE_TO_FOLLOW', 'fa-users'); ?>
		<?php } ?>
	</div>


	<div data-followers-pagination>
		<?php echo $pagination->getListFooter('site');?>
	</div>
</div>
