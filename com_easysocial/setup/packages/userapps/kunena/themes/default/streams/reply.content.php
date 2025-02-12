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
<div class="stream-kunena">
	<div class="o-media">

		<?php if ($topic->getIcon()) { ?>
		<div class="o-media__images">
			<?php echo $topic->getIcon();?>
		</div>
		<?php } ?>

		<div class="o-media__body">
			<h4 class="es-stream-content-title">
				<a href="<?php echo $topic->getUrl();?>"><?php echo $message->subject;?></a>
			</h4>

			<p><?php echo $message->message;?></p>

			<ul class="g-list-unstyled nav-actions">
				<li>
					<a href="<?php echo $message->getPermaUrl();?>#<?php echo $message->id;?>"><?php echo JText::_('APP_KUNENA_BTN_VIEW_REPLY'); ?></a>
				</li>
				<li>
					<a href="<?php echo $topic->getPermaUrl();?>"><?php echo JText::_('APP_KUNENA_BTN_VIEW_THREAD'); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>
