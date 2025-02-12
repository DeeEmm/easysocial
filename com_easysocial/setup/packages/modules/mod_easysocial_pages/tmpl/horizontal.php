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
	<div class="es-cards es-cards--<?php echo $params->get('total_columns', 3);?>">
		<?php foreach ($pages as $page) { ?>
			<div class="es-cards__item">
				<div class="es-card">
					<div class="es-card__hd">
						<a class="embed-responsive embed-responsive-16by9" href="<?php echo $page->getPermalink();?>">
							<div class="embed-responsive-item es-card__cover" style="background-image : url(<?php echo $page->getCover($coverSize)?>); background-position : <?php echo $page->getCoverPosition();?>">
							</div>
						</a>
					</div>

					<div class="es-card__bd es-card--border">
						<?php if ($params->get('display_avatar', true)) { ?>
							<?php echo $lib->html('card.avatar', $page); ?>
						<?php } ?>

						<a class="es-card__title" href="<?php echo $page->getPermalink();?>"><?php echo $page->getName();?></a>

						<div class="es-card__meta t-lg-mb--sm">
							<ol class="g-list-inline g-list-inline--delimited">
								<?php if ($params->get('display_category', true)) { ?>
								<li>
									<i class="fa fa-folder"></i>&nbsp; <a href="<?php echo $page->getCategory()->getFilterPermalink();?>"><?php echo $page->getCategory()->getTitle();?></a>
								</li>
								<?php } ?>

								<?php if ($params->get('display_like_counter', true)) { ?>
								<li data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_LIKERS', $page->getTotalMembers()), $page->getTotalMembers()); ?>">
									<i class="far fa-thumbs-up"></i>&nbsp; <span data-page-like-count-<?php echo $page->id; ?> ><?php echo $page->getTotalMembers();?></span>
								</li>
								<?php } ?>

								<li class="t-lg-pull-right">
									<?php echo $lib->html('page.action', $page); ?>
								</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php if ($params->get('display_alllink', true)) { ?>
	<div>
		<a href="<?php echo ESR::pages();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_PAGES_ALL_PAGE'); ?></a>
	</div>
	<?php } ?>
</div>
