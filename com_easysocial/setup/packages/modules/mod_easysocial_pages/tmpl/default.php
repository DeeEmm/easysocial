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
	<div class="mod-es-list--vertical">
		<?php foreach ($pages as $page) { ?>
			<div class="mod-es-item">
				<div class="o-flag">

					<?php if ($params->get('display_avatar', true)) { ?>
						<div class="o-flag__img t-lg-mr--md">
							<?php echo $lib->html('avatar.cluster', $page); ?>
						</div>
					<?php } ?>

					<div class="o-flag__body">
						<a href="<?php echo $page->getPermalink();?>" class="mod-es-title">
							<?php echo $page->getName();?>
						</a>

						<div class="mod-es-meta">
							<ol class="g-list-inline g-list-inline--delimited">

								<?php if ($params->get('display_category', true)) { ?>
									<li>
										<i class="fa fa-folder"></i>&nbsp;
										<a href="<?php echo $page->getCategory()->getPermalink(); ?>" title="<?php echo $lib->html('string.escape', $page->getCategory()->get('title'));?>">
											<?php echo $lib->html('string.escape', $page->getCategory()->get('title'));?>
										</a>
									</li>
								<?php } ?>

								<?php if ($params->get('display_like_counter', true)) { ?>
									<li>
										<i class="fa fa-users"></i>&nbsp;
										<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_PAGES_LIKES_COUNT', $page->getTotalMembers()), $page->getTotalMembers()); ?>
									</li>
								<?php } ?>
							</ol>
						</div>
						<?php if ($params->get('display_actions', true) && !$page->isMember()) { ?>
							<div class="mod-es-action">
								<?php echo $lib->html('page.action', $page); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if ($params->get('display_alllink', true)) { ?>
		<div class="mod-es-action">
			<a href="<?php echo ESR::pages();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_PAGES_ALL_PAGE'); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
