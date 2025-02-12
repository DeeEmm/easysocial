<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>
		
		<!-- do not remove this element. This element is needed for the stream loodmore to work properly -->
		<div data-filter-item 
			data-type="groupcategory" 
			data-id="<?php echo $category->id;?>" 
			class="active" 
			data-stream-identifier="<?php echo $stream->getIdentifier(); ?>"
		></div>

		<?php echo $this->lib->render('module', 'es-groups-category-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

 		<?php echo ES::themes()->includeTemplate('site/groups/category/about', array('groups' => $groups, 'randomMembers' => $randomMembers, 'randomAlbums' => $randomAlbums)); ?>

		<?php echo $this->lib->render('module', 'es-groups-category-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>

	</div>
</div>