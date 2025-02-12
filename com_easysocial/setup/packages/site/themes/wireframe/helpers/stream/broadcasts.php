<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-broadcasts">
	<div class="es-stream-embed__context">
		<a href="<?php echo $broadcast->link ? $broadcast->link : 'javascript:void(0);';?>" class="es-stream-embed__broadcasts-title">
			 <?php echo $broadcast->title;?>
		</a>

		<div class="es-stream-embed__broadcasts-text">
			<?php if ($this->config->get('stream.content.truncate')) { ?>
				<?php echo $this->html('string.truncate', $broadcast->content, $this->config->get('stream.content.truncatelength')); ?>
			<?php } else { ?>
				<?php echo nl2br($broadcast->content);?>
			<?php } ?>
		</div>
	</div>

	<div class="es-stream-embed__broadcasts-icon">
		<i class="fa fa-bullhorn"></i>
	</div>
</div>
