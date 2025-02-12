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
<div class="es-stream-repost">

	<?php if ($message) { ?>
	<div class="es-stream-repost__text t-lg-mb--md"><?php echo $this->html('string.truncate', $message, $this->config->get('stream.content.truncatelength'));?></div>
	<?php } ?>

	<div class="es-stream-repost__meta">
		<div class="es-stream-repost__meta-inner">

			<?php if ($sourceActor) { ?>
			<div class="es-stream-repost__heading t-text--muted t-lg-mb--md">
				<i class="fa fa-retweet"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_REPOSTED_FROM', $this->html('html.' . $sourceActor->getType(), $sourceActor));?>
			</div>
			<?php } ?>

			<div class="es-stream-repost__content">
				<?php echo $this->html('string.truncate', $content, $this->config->get('stream.content.truncatelength'));?>
			</div>

			<?php if ($preview) { ?>
			<div class="es-stream-repost__preview">
				<?php echo $preview; ?>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
