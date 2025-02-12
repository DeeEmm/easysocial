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
<div class="es-stream-repost">

	<?php if ($message) { ?>
	<div class="es-stream-repost__text t-lg-mb--md"><?php echo $message;?></div>
	<?php } ?>

	<div class="es-stream-repost__meta">
		<div class="es-stream-repost__meta-inner">
			<div class="es-stream-repost__heading t-text--muted t-lg-mb--md">
				<i class="fa fa-retweet"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_REPOSTED_FROM', $this->html('html.user', $album->user_id));?>
			</div>

			<div class="es-stream-repost__content">

				<div class="o-media o-media--top">
					<div class="o-media__image">
						<a href="<?php echo $album->getPermalink();?>">
							<div class="o-media__img-square" style="background-image: url('<?php echo $album->getCover('square'); ?>');background-position: center center;">
							</div>
						</a>
					</div>
					<div class="o-media__body o-media__body--text-overflow">
						<h4 class="o-media__title">
							 <a href="<?php echo $album->getPermalink();?>"><?php echo $album->_('title');?></a>
						</h4>

						<div class="o-media__desc">
							<?php echo $album->_('caption'); ?>
						</div>

						<a href="<?php echo $album->getPermalink();?>" class="btn btn-es-default-o btn-sm t-lg-mt--md"><?php echo JText::_('APP_SHARES_VIEW_ALBUM'); ?></a>
					</div>
				</div>

			</div>


		</div>
	</div>
</div>
