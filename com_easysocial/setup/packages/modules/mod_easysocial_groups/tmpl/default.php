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
	<div class="mod-es-list--vertical">
		<?php foreach ($groups as $group) { ?>
			<div class="mod-es-item">
				<div class="o-flag">

					<?php if ($params->get('display_avatar', true)) { ?>
						<div class="o-flag__img t-lg-mr--md">
							<?php echo $lib->html('avatar.cluster', $group); ?>
						</div>
					<?php } ?>

					<div class="o-flag__body">
						<a href="<?php echo $group->getPermalink();?>" class="mod-es-title">
							<?php echo $group->getName();?>
						</a>

						<div class="mod-es-meta">
							<ol class="g-list-inline g-list-inline--delimited">

								<?php if ($params->get('display_category', true)) { ?>
									<li>
										<i class="fa fa-folder"></i>&nbsp;
										<a href="<?php echo $group->getCategory()->getPermalink(); ?>" title="<?php echo $lib->html('string.escape', $group->getCategory()->get('title'));?>">
											<?php echo $lib->html('string.escape', $group->getCategory()->get('title'));?>
										</a>
									</li>
								<?php } ?>

								<?php if ($params->get('display_member_counter', true)) { ?>
									<li>
										<i class="fa fa-users"></i>&nbsp;
										<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_GROUPS_MEMBERS_COUNT', $group->getTotalMembers()), $group->getTotalMembers()); ?>
									</li>
								<?php } ?>

							</ol>
						</div>
						<?php if ($params->get('display_actions', true) && !$group->isMember()) { ?>
							<div class="mod-es-action">
								<?php echo $lib->html('group.action', $group); ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if ($params->get('display_alllink', true)) { ?>
		<div class="mod-es-action">
			<a href="<?php echo ESR::groups();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_GROUPS_ALL_GROUP'); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
