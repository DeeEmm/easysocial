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
<div class="es-container" data-es-search>
	<div class="es-content">
		<div class="es-search-form es-island">
			<form name="search" method="post" action="<?php echo JRoute::_('index.php');?>">
				<div class="o-input-group t-mb--md">
					<input type="text" class="o-form-control" value="<?php echo $this->html( 'string.escape' , $query ); ?>" name="q" autocomplete="off" data-search-query>
					<div class="o-input-group__btn">
						<button class="btn btn-es-primary-o"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_BUTTON'); ?></button>
					</div>
				</div>

				<div class="es-search-advance">
					<a href="<?php echo ESR::search(array('layout' => 'advanced')); ?>"><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_LINK'); ?></a>
				</div>

				<?php if ($filters) { ?>
				<div class="es-search-filter">
					<?php foreach ($filters as $filter) { ?>
					<div class="es-search-filter-item">
						<input type="checkbox" id="<?php echo $filter->id;?>" name="filtertypes[]" value="<?php echo $filter->alias;?>" data-search-filtertypes
						<?php echo in_array($filter->alias, $selectedFilters) ? 'checked="checked"' : '';?>
						/>

						<label for="<?php echo $filter->id;?>"><?php echo $filter->displayTitle;?></label>
					</div>
					<?php } ?>
				</div>
				<?php } ?>

				<?php echo $this->html('form.action', 'search', 'query'); ?>
				<?php echo $this->html('form.itemid'); ?>
			</form>
		</div>

		<div class="es-search-result">
			<?php if ($query && $total) { ?>
			<div class="t-lg-mt--xl t-lg-mb--xl">
				<i class="fa fa-search"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_SEARCH_NUMBER_ITEM_FOUND', '<b>' . $total . '</b>'); ?>
			</div>
			<?php } ?>

			<div data-contents>
				<?php if (!$query) { ?>
					<div class="o-alert o-alert--warning">
						<?php echo JText::_('COM_EASYSOCIAL_SEARCH_PLEASE_ENTER_TO_SEARCH'); ?>
					</div>
				<?php } else { ?>
					<?php if ($this->config->get('search.minimum') && $length < $this->config->get('search.characters')) { ?>
					<div class="o-alert o-alert--error">
						<?php echo JText::sprintf('COM_ES_MIN_CHARACTERS_SEARCH', $this->config->get('search.characters')); ?>
					</div>
					<?php } else { ?>
						<?php echo $this->loadTemplate('site/search/default/list', array('result' => $result, 'next_limit' => $next_limit, 'total' => $total)); ?>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
