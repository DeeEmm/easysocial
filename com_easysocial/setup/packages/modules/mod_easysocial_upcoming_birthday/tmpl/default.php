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
<div id="es" class="mod-es mod-es-upcoming-birthday <?php echo $lib->getSuffix();?>" data-upcoming-birthday="<?php echo $module->id;?>">
	<div data-birthday-contents>
		<?php echo $lib->output('site/modules/mod_easysocial_upcoming_birthday/default', array('lib' => $lib, 'birthdays' => $birthdays, 'paginationData' => $paginationData, 'pagination' => $pagination)); ?>
	</div>

	<div class="o-grid" data-birthday-pagination>
		<div class="o-grid__cell o-grid__cell--auto-size">
			<button type="button" class="btn btn-es-default-o btn-xs" <?php echo !$paginationData->previous->link ? 'disabled' : '';?> data-birthday-paginate="<?php echo $paginationData->previous->base;?>" data-previous>
				<i class="fa fa-chevron-left"></i>
			</button>
		</div>

		<div class="o-grid__cell t-text--center">
			<span data-page-current><?php echo $pagination->pagesCurrent; ?></span> / <span data-page-total><?php echo $pagination->pagesTotal;?></span>
		</div>

		<div class="o-grid__cell o-grid__cell--auto-size t-text--right">
			<button type="button" class="btn btn-es-default-o btn-xs" <?php echo !$paginationData->next->link ? 'disabled' : '';?> data-birthday-paginate="<?php echo $paginationData->next->base;?>" data-next>
				<i class="fa fa-chevron-right"></i>
			</button>
		</div>
	</div>
</div>
