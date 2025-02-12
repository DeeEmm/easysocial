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
<div class="es-data-wrapper" data-wrapper>

	<?php if (isset($activeProfile) && $activeProfile) { ?>
	<div class="t-lg-mb--lg">
		<?php echo $this->html('miniheader.profileType', $activeProfile); ?>
	</div>
	<?php } ?>

	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_($snackbarTitle);?>
			</div>
		</div>

		<?php if ($showSort) { ?>
		<div class="es-snackbar2__actions">
			<?php echo $this->html('form.popdown', 'sorting_test', $sort, array(
				$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_RECENTLY_REGISTERED', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
				$this->html('form.popdownOption', 'lastlogin', 'COM_ES_SORT_BY_RECENTLY_LOGGED_IN', '', false, $sortItems->lastlogin->attributes, $sortItems->lastlogin->url),
				$this->html('form.popdownOption', 'alphabetical', 'COM_ES_SORT_BY_NAME', '', false, $sortItems->alphabetical->attributes, $sortItems->alphabetical->url)
			)); ?>
		</div>
		<?php } ?>
	</div>

	<div class="<?php echo $this->isMobile() ? 'es-list' : 'es-cards es-cards--2';?> <?php echo !$users ? ' is-empty' : '';?>" data-es-users-result>
		<?php echo $this->includeTemplate('site/users/default/items'); ?>

		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_USERS_NO_USERS_HERE', 'fa-users'); ?>
	</div>

	<?php if ($pagination) { ?>
	<div class="es-pagination-footer" data-es-users-pagination>
		<?php echo $pagination->getListFooter('site');?>
	</div>
	<?php } ?>

</div>
