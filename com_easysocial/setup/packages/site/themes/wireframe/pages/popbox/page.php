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
			<?php echo $this->html('avatar.page', $page, 'md', false); ?>
		</div>
		<div class="o-media__body">
			<div class="o-title t-text--truncate">
				<?php echo $this->html('html.cluster', $page, false); ?>

				<div class="o-meta t-lg-mt--sm">
					<?php echo $this->html('page.type', $page, 'bottom', false, false); ?>

					&middot;

					<a href="<?php echo $page->getCategory()->getFilterPermalink();?>" class="">
						<?php echo $page->getCategory()->getTitle(); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="popbox-label-group t-lg-mb--md">
		<div class="popbox-label t-text--truncate">
			<a href="<?php echo $page->getAppPermalink('followers');?>" class="">
				<?php echo $page->getTotalMembers();?> <span class="popbox-label__meta"><?php echo JText::_('COM_ES_LIKES');?></span>
			</a>
		</div>

		<div class="popbox-label t-text--truncate">
			<?php echo $page->hits;?> <span class="popbox-label__meta"><?php echo JText::_('COM_ES_VIEWS');?></span>
		</div>
	</div>

</div>

<?php if (!$page->isOwner()) { ?>
<div class="popbox-content__ft">
	<?php echo $this->html('page.action', $page, true); ?>
</div>
<?php } ?>
