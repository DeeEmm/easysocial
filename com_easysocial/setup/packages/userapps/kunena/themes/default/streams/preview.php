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
<div class="es-stream-apps type-discuss">
	<div class="es-stream-apps__hd">
		<?php if ($topic->getIcon()) { ?>
		<span class="t-lg-mr--sm">
			<?php echo $topic->getIcon();?>
		</span>
		<?php } ?>

		<a href="<?php echo $topic->getUrl();?>" class="es-stream-apps__title"><?php echo $topic->subject;?></a>
	</div>

	<div class="es-stream-apps__bd es-stream-apps--border">
		<div class="es-stream-apps__desc">
			<?php echo $topic->message;?>
		</div>

		<ol class="g-list--horizontal has-dividers--right">
			<li class="g-list__item">
				<a href="<?php echo $topic->getPermaUrl();?>">
					<?php echo JText::_('APP_KUNENA_BTN_VIEW_THREAD'); ?>
				</a>
			</li>
		</ol>
	</div>
</div>