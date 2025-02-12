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
<div id="es" class="mod-es mod-es-search <?php echo $lib->getSuffix();?>">
	<div class="mod-bd">
		<form action="<?php echo JRoute::_('index.php');?>" method="post">
			<div class="o-input-group" data-mod-search data-showadvancedlink="<?php echo $params->get('showadvancedlink', 1); ?>">
				<input type="text" name="q" class="o-form-control" autocomplete="off" data-nav-search-input placeholder="<?php echo JText::_('MOD_EASYSOCIAL_SEARCH_PHASE', true );?>">

				<?php if ($filters) { ?>

				<span class="o-input-group__btn dropdown" data-nav-search-filter data-filters>

					<button class="btn btn-es-default-o dropdown-toggle" data-es-toggle="dropdown" data-filter-button type="button"><i class="fa fa-cog"></i></button>

					<ul class="dropdown-menu dropdown-menu-right mod-es-search__dropdown" data-filters-wrapper>
						<li class="">
							<div class="mod-es-search__filter-header">
								<div><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESC');?></div>
							</div>
							<ol class="g-list-inline g-list-inline--delimited">
								<li>
									<a href="javascript:void(0);" data-filter="select"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_SELECT_ALL'); ?></a>
								</li>
								<li data-breadcrumb="|">
									<a href="javascript:void(0);" data-filter="deselect"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESELECT_ALL'); ?></a>
								</li>
							</ol>
						</li>
						<?php foreach ($filters as $filter) { ?>
						<li class="mod-es-search__filter-item">
							<div class="o-checkbox">
								<input type="checkbox" name="filtertypes[]" id="mod-search-type-<?php echo $filter->id;?>" value="<?php echo $filter->alias; ?>"
									<?php echo (isset($filter->checked) && $filter->checked) ? ' checked="true"' : ''; ?>
									data-search-filtertypes
								/>
								<label for="mod-search-type-<?php echo $filter->id;?>"><?php echo $filter->displayTitle;?></label>
							</div>
						</li>
						<?php } ?>
					</ul>
				</span>
				<?php } ?>
			</div>

			<input type="hidden" name="Itemid" value="<?php echo FRoute::getItemId('search');?>" />
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="search" />
			<input type="hidden" name="task" value="query" />
			<?php echo $lib->html('form.token');?>
		</form>

		<?php if ($params->get('showadvancedlink', true)) { ?>
		<div class="t-lg-mt--sm">
			<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('MOD_EASYSOCIAL_SEARCH_ADVANCED_SEARCH');?></a>
		</div>
		<?php } ?>
	</div>
</div>
