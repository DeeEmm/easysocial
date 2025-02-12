<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($files) { ?>
<div class="discussion-files">
	<h5><?php echo JText::_('APP_PAGE_DISCUSSIONS_FILES'); ?></h5>
	<ul class="g-list-unstyled">
		<?php foreach ($files as $file) { ?>
		<li>
			<a href="#">
				<i class="icon-es-<?php echo $file->getIconClass();?> mr-5"></i> <?php echo $file->name; ?>
			</a>

			<span class="attach-size t-fs--sm">&mdash; <?php echo $file->getSize('kb');?> <?php echo JText::_('COM_EASYSOCIAL_UNIT_KILOBYTES');?></span>

			<?php if ($file->hasPreview()) { ?>
			<div class="attachment-preview">
				<a href="<?php echo $file->getPreviewURI();?>" target="_blank"><img src="<?php echo $file->getPreviewURI();?>" /></a>
			</div>
			<?php } ?>
		</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>
