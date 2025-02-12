<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="file-item"
	data-files-item
	data-id="<?php echo $file->id;?>"
	data-edit="<?php echo isset($isEdit) ? $isEdit : '0'; ?>"
>
	<div class="file-icon">
		<i class="fa fa-archive"></i>
		<div class="file-name" data-name><?php echo $file->name;?></div>
	</div>
	<div class="remove-button" data-files-item-remove>
		<i class="fa fa-trash"></i> <?php echo JText::_('APP_USER_FILES_STORY_REMOVE_FILE');?>
	</div>
</div>
