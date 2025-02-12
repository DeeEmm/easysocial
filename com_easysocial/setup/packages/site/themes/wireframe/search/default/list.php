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
<div class="es-list <?php echo !$result ? 'is-empty' : '';?>" data-search-list>
	<?php if ($result) { ?>
		<?php echo $this->output('site/search/default/list.result'); ?>

		<div data-search-pagination data-limitstart="<?php echo $next_limit; ?>">
			<?php if ($total > ES::getLimit('search_limit')) { ?>
			<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm btn-block" data-more>
				<i class="fa fa-refresh"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_SEARCH_LOAD_MORE_ITEMS'); ?>
			</a>
			<?php } ?>
		</div>
	<?php } ?>

	<?php echo $this->html("html.emptyBlock", 'COM_EASYSOCIAL_SEARCH_NO_RECORDS_FOUND', 'fa-search'); ?>
</div>
