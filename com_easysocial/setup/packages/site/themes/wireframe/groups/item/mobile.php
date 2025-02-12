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
<div class="es-mobile-info">
	<?php echo $this->render('widgets', SOCIAL_TYPE_GROUP, 'groups', 'mobileBeforeIntro', array('uid' => $group->id, 'group' => $group), 'site/widgets/mobile.wrapper'); ?>

	<div class="es-side-widget" data-type="info">
		<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS_INTRO'); ?>

			<div class="es-side-widget__bd">
				<?php if ($this->config->get('groups.layout.description')) { ?>
					<?php echo $this->html('string.truncate', $group->getDescription(), 120, '', false, false, false, true);?>
					<a href="<?php echo $aboutPermalink;?>"><?php echo JText::_('COM_EASYSOCIAL_READMORE');?></a>
				<?php } ?>

				<ul class="o-nav o-nav--stacked t-lg-mt--sm">
					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<a class="o-nav__link t-text--muted" href="<?php echo $group->getCreator()->getPermalink();?>">
							<i class="es-side-widget__icon fa fa-user t-lg-mr--md"></i>
							<?php echo $group->getCreator()->getName();?>
						</a>
					</li>

					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<a class="o-nav__link t-text--muted" href="<?php echo $group->getAppPermalink('members');?>">
							<i class="es-side-widget__icon fa fa-users t-lg-mr--md"></i>
							<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_MEMBERS_MINI', $group->getTotalMembers()), $group->getTotalMembers()); ?>
						</a>
					</li>
				</ul>
			</div>
	</div>

	<?php echo $this->render('widgets', SOCIAL_TYPE_GROUP, 'groups', 'mobileAfterIntro', array('uid' => $group->id, 'group' => $group), 'site/widgets/mobile.wrapper'); ?>
</div>
