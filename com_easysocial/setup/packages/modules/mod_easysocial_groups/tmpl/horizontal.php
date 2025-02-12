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
<div id="es" class="mod-es mod-es-groups <?php echo $lib->getSuffix();?>">
	<div class="es-cards es-cards--<?php echo $params->get('total_columns', 3);?>">
		<?php foreach ($groups as $group) { ?>
			<div class="es-cards__item">
				<div class="es-card">
					<div class="es-card__hd">
						<a class="embed-responsive embed-responsive-16by9" href="<?php echo $group->getPermalink();?>">
							<div class="embed-responsive-item es-card__cover" style="background-image : url(<?php echo $group->getCover($coverSize)?>); background-position : <?php echo $group->getCoverPosition();?>">
							</div>
						</a>
					</div>

					<div class="es-card__bd es-card--border">
						<?php if ($params->get('display_avatar', true)) { ?>
							<?php echo $lib->html('card.avatar', $group); ?>
						<?php } ?>

						<a class="es-card__title" href="<?php echo $group->getPermalink();?>"><?php echo $group->getName();?></a>

						<div class="es-card__meta t-lg-mb--sm">
							<ol class="g-list-inline g-list-inline--delimited">
								<?php if ($params->get('display_category', true)) { ?>
								<li>
									<i class="fa fa-folder"></i>&nbsp; <a href="<?php echo $group->getCategory()->getFilterPermalink();?>"><?php echo $group->getCategory()->getTitle();?></a>
								</li>
								<?php } ?>

								<?php if ($params->get('display_member_counter', true)) { ?>
								<li data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_GROUPS_MEMBERS_COUNT', $group->getTotalMembers()), $group->getTotalMembers()); ?>">
									<i class="fa fa-users"></i>&nbsp; <span data-group-join-count-<?php echo $group->id; ?> ><?php echo $group->getTotalMembers();?></span>
								</li>
								<?php } ?>

								<?php if ($params->get('display_actions', true)) { ?>
								<li class="t-lg-pull-right">
									<?php echo $lib->html('group.action', $group); ?>
								</li>
								<?php } ?>

							</ol>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php if ($params->get('display_alllink', true)) { ?>
	<div>
		<a href="<?php echo ESR::groups();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_GROUPS_ALL_GROUP'); ?></a>
	</div>
	<?php } ?>
</div>
