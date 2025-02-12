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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<a href="<?php echo $file->getPreviewURI();?>" target="_blank">
	<span class="discussion-embed-item discussion-embed-file">
		<i class="fa fa-download"></i>
		<span class="discussion-embed-caption">
			<?php echo $file->name;?> (<?php echo $file->getSize();?><?php echo JText::_('COM_EASYSOCIAL_UNIT_KILOBYTES');?>)
		</span>
	</span>
</a>
