<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-apps">
	<div class="es-stream-apps__hd">
		
		<a href="<?php echo $note->getPermalink();?>" class="es-stream-apps__title"><?php echo $note->title;?></a>
	
		<div class="es-stream-apps__meta">
			<i class="fa fa-calendar"></i>&nbsp; <?php echo $this->html('string.date', $note->created, JText::_('DATE_FORMAT_LC3')); ?>
		</div>
	</div>
	<div class="es-stream-apps__bd es-stream-apps--border">
		<div class="es-stream-apps__desc">
			<?php if ($params->get('stream_truncate', true)) { ?>
				<?php echo $this->html('string.truncate', $note->getContent(), $params->get('stream_truncate_length', 250)); ?>
			<?php } else { ?>
				<?php echo $note->getContent(); ?>
			<?php } ?>
		</div>
	</div>
</div>
