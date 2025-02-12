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
<!-- repost stream item output after repost from the single audio page -->
<div class="es-stream-repost">
	<?php if ($message) { ?>
		<div class="es-stream-repost__text t-lg-mb--md"><?php echo $message; ?></div>
	<?php } ?>

	<div class="es-stream-repost__meta">
		<div class="es-stream-repost__meta-inner">
			<div class="es-stream-repost__heading t-text--muted t-lg-mb--md">
				<i class="fa fa-retweet"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_REPOSTED_FROM', $sourceActor ? $this->html('html.cluster', $sourceActor) : $this->html('html.user', $audio->user_id)); ?>
			</div>

			<?php echo $this->includeTemplate('site/streams/audios/preview'); ?>
		</div>
	</div>
</div>

