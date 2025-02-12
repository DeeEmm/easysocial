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
<div class="dl-menu-wrapper">
	<ul class="o-tabs o-tabs--stacked o-tabs--dlmenu" data-sidebar-menu>
	<?php if ($categories) { ?>
		<?php foreach ($categories as $category) { ?>
		<li class="o-tabs__item has-notice <?php echo $activeCategory && $activeCategory->id == $category->id ? 'active' : '';?>" data-filter-item data-type="<?php echo $type == SOCIAL_TYPE_AUDIO ? 'genre' : 'category' ?>" data-id="<?php echo $category->id;?>">
			<a href="<?php echo $category->getFilterPermalink();?>"
				title="<?php echo $this->html('string.escape' , $category->get('title'));?>"
				class="o-tabs__link">
				<?php echo $category->getTitle();?>
			</a>
			<?php if (!empty($category->childs)) { ?>
			<a href="javascript:void(0);" class="o-tabs__toggle-submenu" data-submenu-link><i class="fa fa-angle-right"></i></a>
			<?php } ?>
			<?php if ($displayCounters) { ?>
			<span class="o-tabs__bubble" data-counter="<?php echo $category->total;?>">
				<?php echo $category->total;?>
			</span>
			<?php } ?>
			<div class="o-loader o-loader--sm"></div>
			<?php if (!empty($category->childs)) { ?>
				<?php echo $this->includeTemplate('site/helpers/categories/menu.nested', array('category' => $category, 'type' => $type, 'activeCategory' => $activeCategory)); ?>
			<?php } ?>
		</li>
		<?php } ?>
	<?php } ?>
	</ul>
</div>

<?php if (!$categories) { ?>
	<?php echo $this->html('widget.emptyBlock', 'COM_EASYSOCIAL_GROUPS_NO_CATEGORY_CREATED_YET'); ?>
<?php } ?>
