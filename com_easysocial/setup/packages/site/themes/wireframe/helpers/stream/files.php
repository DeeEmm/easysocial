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
<?php foreach ($files as $file) { ?>
<div class="es-stream-embed is-file">
	<div class="es-stream-embed__file-icon">
		<i class="far fa-file-archive"></i>
	</div>
	<div class="es-stream-embed__file-context">
		<a href="<?php echo $file->getDownloadURI();?>" class="es-stream-embed__file-link">
			 <?php echo $file->name;?>
		</a>
		<b><?php echo $file->getSize('kb');?> <?php echo JText::_('COM_EASYSOCIAL_UNIT_KILOBYTES');?></b>
	</div>
</div>
<?php } ?>
