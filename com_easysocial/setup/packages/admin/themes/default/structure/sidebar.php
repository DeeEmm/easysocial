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
<div class="container-nav hidden">
	<a class="nav-sidebar-toggle" data-bp-toggle="collapse" data-target=".app-sidebar-collapse">
		<i class="fa fa-bars"></i>
	</a>
	<a class="nav-subhead-toggle" data-bp-toggle="collapse" data-target=".subhead-collapse">
		<i class="fa fa-cog"></i>
	</a>
</div>

<div class="hidden" data-j4-sidebar>
	<li class="item item-level-1">
		<a class="has-arrow" href="javascript:void(0);" aria-expanded="false" data-back-easysocial>
			<span class="icon-" > <!-- es#4860 icon- Make it on purpose so that follow J4 style  -->
				<img src="<?php echo rtrim(JURI::root(), '/') . '/media/com_easysocial/images/easysocial-256x256.png';?>" style="width: 20px;height: 20px;">
			</span>

			<span class="sidebar-item-title">EasySocial</span>
		</a>
	</li>
</div>


<div class="app-sidebar app-sidebar-collapse" data-sidebar>
	<ul class="app-sidebar-nav list-unstyled">

		<?php if (ES::isJoomla4()) { ?>
		<li class="sidebar-item sidebar-item--joomla-4-btn">
			<a href="javascript:void(0);" data-back-joomla>
				<i class="fa fa-chevron-left"></i> <span class="app-sidebar-item-title"><?php echo JText::_('Back');?></span>
			</a>
		</li>
		<?php } ?>

		<?php foreach ($menus as $menu) { ?>

			<?php if ((isset($menu->access) && $this->my->authorise($menu->access, 'com_easysocial')) || !isset($menu->access)) { ?>
			<li class="sidebar-item
				menuItem<?php echo !empty($menu->childs) ? ' dropdown' : '';?>
				<?php echo $menu->isActive ? ' active' : '';?>"
				data-sidebar-item
			>

				<a href="<?php echo isset($menu->childs) && $menu->childs ? 'javascript:void(0);' : $menu->link;?>" data-sidebar-parent data-childs="<?php echo isset($menu->childs) && $menu->childs ? '1' : '0';?>">
					<i class="<?php echo $menu->class;?>"></i><span class="sidebar-item-title"><?php echo JText::_($menu->title); ?></span>
					<span class="badge

					<?php echo $menu->count > 0 ? 'has-counter' : '';?>
					<?php echo !$menu->isActive && $menu->count > 0 ? 't-block' : '';?>
					<?php echo $menu->isActive && $menu->childs ? 't-hidden' : '';?>
					" data-parent-badge>&nbsp;</span>
				</a>


				<?php if (isset($menu->childs) && $menu->childs) { ?>
				<ul role="menu" class="dropdown-menu<?php echo $menu->view == $view ? ' in' : '';?>" id="menu-<?php echo $menu->uid;?>" data-sidebar-child>
					<?php foreach ($menu->childs as $child) { ?>

						<li class="menu-<?php echo isset($child->class) && $child->class ? $child->class : '';?> childItem <?php echo $child->isActive ? 'active' : '';?>">
							<a href="<?php echo $child->link;?>">
								<span class="app-sidebar-item-title"><?php echo JText::_($child->title); ?></span>
							</a>
							<span class="badge <?php echo $child->count > 0 ? 't-block' : '';?>" data-child-badge>&nbsp;</span>
						</li>
					<?php } ?>
				</ul>
				<?php } ?>
			</li>
			<?php } ?>

		<?php } ?>
	</ul>
</div>
