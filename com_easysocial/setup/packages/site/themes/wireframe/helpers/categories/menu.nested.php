<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<ul class="o-tabs o-tabs--stacked dl-submenu">
	<li class="o-tabs__item o-tabs__item-back" data-submenu-back>
		<a href="javascript:void(0);" title="Business" class="o-tabs__link">
			<i class="fa fa-angle-left"></i>&nbsp; <?php echo JText::_('COM_ES_BACK'); ?>
		</a>
	</li>
	<?php foreach ($category->childs as $child) { ?>
	<li class="o-tabs__item has-notice <?php echo $activeCategory && $activeCategory->id == $child->id ? 'active' : '';?>" data-filter-item data-type="<?php echo $type == SOCIAL_TYPE_AUDIO ? 'genre' : 'category' ?>" data-id="<?php echo $child->id;?>">
		<a href="<?php echo $child->getFilterPermalink();?>"
			title="<?php echo $this->html('string.escape' , $child->get('title'));?>"
			class="o-tabs__link">
			<?php echo $child->getTitle();?>
		</a>
		<?php if (!empty($child->childs)) { ?>
			<a href="javascript:void(0);" class="o-tabs__toggle-submenu" data-submenu-link><i class="fa fa-angle-right"></i></a>
		<?php } ?>
		<span class="o-tabs__bubble" data-counter="<?php echo $child->total;?>">
			<?php echo $child->total;?>
		</span>
		<div class="o-loader o-loader--sm"></div>
		<?php if (!empty($child->childs)) { ?>
			<?php echo $this->includeTemplate('site/helpers/categories/menu.nested', array('category' => $child, 'type' => $type, 'activeCategory' => $activeCategory)); ?>
		<?php } ?>
	</li>
	<?php } ?>
</ul>
