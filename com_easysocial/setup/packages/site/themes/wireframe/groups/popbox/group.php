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
<div class="popbox-content__bd">
	<div class="o-media o-media--rev t-lg-mb--lg">
		<div class="o-media__image">
			<?php echo $this->html('avatar.cluster', $group, 'md', false, false, '', true, '', false); ?>
		</div>
		<div class="o-media__body">
			<div class="o-title t-text--truncate">
				<?php echo $this->html('html.cluster', $group, false); ?>

				<div class="o-meta t-lg-mt--sm">
					<?php echo $this->html('group.type', $group, 'bottom', false, false); ?>

					&middot;

					<a href="<?php echo $group->getCategory()->getFilterPermalink();?>">
						<?php echo $group->getCategory()->getTitle(); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="popbox-label-group t-lg-mb--md">
		<div class="popbox-label t-text--truncate">
			<a href="<?php echo $group->getAppPermalink('members');?>" class="">
				<?php echo $group->getTotalMembers();?> <span class="popbox-label__meta"><?php echo JText::_('COM_ES_MEMBERS');?></span>
			</a>
		</div>

		<div class="popbox-label t-text--truncate">
			<?php echo $group->hits;?> <span class="popbox-label__meta"><?php echo JText::_('COM_ES_VIEWS');?></span>
		</div>
	</div>

</div>

<?php if (!$group->isOwner()) { ?>
<div class="popbox-content__ft">
	<?php echo $this->html('group.action', $group, false); ?>
</div>
<?php } ?>
