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
<form name="adminForm" id="adminForm" class="groupsForm" method="post" enctype="multipart/form-data">
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<?php echo $this->loadTemplate('admin/clusters/category.form/tabs', array('isNew' => $category->id == 0, 'activeTab' => $activeTab)); ?>

				<div class="tab-content">
					<div id="settings" class="tab-pane<?php echo $activeTab == 'settings' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/clusters/category.form/settings'); ?>
					</div>

					<?php if ($category->id) { ?>
					<div id="access" class="tab-pane<?php echo $activeTab == 'access' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/clusters/category.form/access'); ?>
					</div>

					<div id="headerapps" class="tab-pane<?php echo $activeTab == 'headerapps' ? ' active in' : '';?>">
						<?php echo $this->html('form.headerApps', 'params[header_apps]', $category->getParams()->get('header_apps'), $clusterType, array(), $category->id); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<span class="t-hidden" data-error-maxsize-photo><?php echo JText::_('COM_EASYSOCIAL_MAXUPLOADSIZE_ERROR_PHOTOS_MAXSIZE'); ?></span>
	<span class="t-hidden" data-error-maxsize-file><?php echo JText::_('COM_EASYSOCIAL_MAXUPLOADSIZE_ERROR_FILES_MAXSIZE'); ?></span>
	<span class="t-hidden" data-error-maxsize-video><?php echo JText::_('COM_EASYSOCIAL_MAXUPLOADSIZE_ERROR_VIDEOS_MAXSIZE'); ?></span>
	<span class="t-hidden" data-error-maxsize-uploader><?php echo JText::_('COM_EASYSOCIAL_MAXUPLOADSIZE_ERROR_PHOTOS_UPLOADER_MAXSIZE'); ?></span>

	<input type="hidden" name="activeTab" value="<?php echo $activeTab; ?>" data-tab-active />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="<?php echo $controller; ?>" />
	<input type="hidden" name="task" value="saveCategory" />
	<input type="hidden" name="id" value="<?php echo $category->id; ?>" />
	<input type="hidden" name="cid" value="" />
	<?php echo JHTML::_('form.token');?>
</form>
