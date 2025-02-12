<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="dl-menu-wrapper mod-es mod-es-groups-categories <?php echo $lib->getSuffix();?> <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
	<ul class="o-tabs o-tabs--stacked o-tabs--dlmenu" data-sidebar-menu>
	<?php if ($categories) { ?>
		<?php foreach ($categories as $category) { ?>
		<li class="o-tabs__item has-notice" data-filter-item data-type="category" data-id="<?php echo $category->id;?>">
			<a href="<?php echo $category->getFilterPermalink();?>"
				title="<?php echo $lib->html('string.escape', $category->_('title'));?>"
				class="o-tabs__link">
				<?php echo $category->getTitle();?>
			</a>
			<?php if (!empty($category->childs)) { ?>
			<a href="javascript:void(0);" class="o-tabs__toggle-submenu" data-submenu-link><i class="fa fa-angle-right"></i></a>
			<?php } ?>

			<?php if ($params->get('display_counter', true)) { ?>
				<span class="o-tabs__bubble" data-counter="<?php echo $category->getTotalCluster(SOCIAL_TYPE_EVENT);?>">
					<?php echo $category->getTotalCluster(SOCIAL_TYPE_EVENT);?>
				</span>
			<?php } ?>

			<div class="o-loader o-loader--sm"></div>
			<?php if (!empty($category->childs)) { ?>
				<?php echo ES::themes()->includeTemplate('site/helpers/categories/menu.nested', array('category' => $category, 'type' => SOCIAL_TYPE_EVENT, 'activeCategory' => '')); ?>
			<?php } ?>
		</li>
		<?php } ?>
	<?php } ?>
	</ul>
</div>
