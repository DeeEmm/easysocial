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
<div id="es" class="mod-es mod-es-pages <?php echo $lib->getSuffix();?>">
	<?php foreach ($pages as $page) { ?>
		<div class="mod-card mod-card--no-avatar-holder">
			<div class="mod-card__cover-wrap">
				<div style="background-image : url(<?php echo $page->getCover($coverSize)?>);
				background-position : <?php echo $page->getCoverPosition();?>" class="mod-card__cover">
				</div>
			</div>
			<div class="mod-card__context">
				<?php if ($params->get('display_avatar', true)) { ?>
					<div class="mod-card__avatar-holder">
						<div class="mod-card__avatar <?php echo ES::config()->get('layout.avatar.style') == 'rounded' ? 'es-card__avatar--rounded' : '';?>">
							<?php echo $lib->html('avatar.cluster', $page, 'md'); ?>
						</div>
					</div>
				<?php } ?>
				<a class="es-card__title" href="<?php echo $page->getPermalink();?>"><?php echo $page->getName();?></a>

				<?php if ($params->get('display_category', true)) { ?>
					<div class="es-card__meta">
						<a href="<?php echo $page->getCategory()->getPermalink(); ?>" title="<?php echo $lib->html('string.escape', $page->getCategory()->get('title'));?>">
							<?php echo $lib->html('string.escape', $page->getCategory()->get('title'));?>
						</a>
					</div>
				<?php } ?>

				<?php if ($params->get('display_like_counter', true)) { ?>
					<div class="es-card__meta">
						<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_PAGES_LIKES_COUNT', $page->getTotalMembers()), $page->getTotalMembers()); ?>
					</div>
				<?php } ?>

				<?php if ($params->get('display_actions', true) && !$page->isMember()) { ?>
					<div class="mod-es-action">
						<?php echo $lib->html('page.action', $page); ?>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<?php if ($params->get('display_alllink', true)) { ?>
	<div class="mod-es-action">
		<a href="<?php echo ESR::pages();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_PAGES_ALL_PAGE'); ?></a>
	</div>
	<?php } ?>
</div>
