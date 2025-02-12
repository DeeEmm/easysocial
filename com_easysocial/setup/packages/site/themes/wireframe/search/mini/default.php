<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-search-mini-result">
	<?php if ($result) { ?>
		<div class="es-search-mini-result-wrap">
			<?php foreach ($result as $group) { ?>
			<div class="es-search-mini-group">
				<div class="es-search-mini-result-list" data-nav-search-ul>
				<?php if (isset($group->result) && $group->result) { ?>
					<?php foreach ($group->result as $item) { ?>
						<?php echo $this->loadTemplate('site/search/mini/item', array('item' => $item)); ?>
					<?php } ?>
				<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>

		<div class="es-search-mini-footer">
			<div class="text-center t-fs--sm muted">
				<?php echo JText::sprintf('COM_EASYSOCIAL_SEARCH_NUMBER_ITEM_FOUND_TOOLBAR', $total); ?>
			</div>

			<div class="t-text--center">
				<ul class="g-list-inline g-list-inline--dashed">
					<li>
						<a href="<?php echo $searchLink; ?>"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_VIEW_ALL_RESULTS'); ?></a>
					</li>

					<?php if ($showadvancedlink) { ?>
					<li>
						<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_LINK'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	<?php } else { ?>

		<div class="es-search-mini-empty t-text--center">
			<?php echo JText::_('COM_EASYSOCIAL_SEARCH_NO_RECORDS_FOUND_MINI'); ?>

			<ul class="g-list-inline g-list-inline--dashed t-lg-mt--xl">
				<li>
					<a href="<?php echo $searchLink; ?>"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_GO_TO_SEARCH_PAGE'); ?></a>
				</li>

				<?php if ($showadvancedlink) { ?>
				<li>
					<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_LINK'); ?></a>
				</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>
