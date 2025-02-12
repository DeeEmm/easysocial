<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="<?php echo !$discussions ? ' is-empty' : '';?> " data-result>
	<?php if ($discussions) { ?>
		<?php foreach ($discussions as $discussion) { ?>
		<div class="es-apps-item es-island <?php echo $discussion->answer_id ? ' is-resolved' : '';?><?php echo $discussion->lock ? ' is-locked' : '';?><?php echo !$discussion->last_reply_id ? ' is-unanswered' : '';?>">
			<div class="es-apps-item__hd">
				<a href="<?php echo $discussion->getPermalink();?>" class="es-apps-item__title"><?php echo $discussion->_('title');?></a>
			</div>

			<div class="es-apps-item__bd">
				<div class="es-apps-item__desc">
					<?php echo $this->html('string.truncate', $discussion->getContent(), 250, '', false, false);?>
				</div>
				<div class="es-apps-item__item-action">
					<a href="<?php echo $discussion->getPermalink();?>" class="btn btn-es-default-o btn-sm"><?php echo JText::_('COM_ES_VIEW_POST');?></a>
				</div>
			</div>

			<?php if($params->get('display_total_hits', true) || $params->get('display_total_replies', true) || ($params->get('display_last_replied', true) && $discussion->lastreply)) { ?>
			<div class="es-apps-item__ft es-bleed--bottom">
				<div class="o-grid">
					<div class="o-grid__cell">
						<div class="es-apps-item__meta">
							<div class="es-apps-item__meta-item">
								<ol class="g-list-inline g-list-inline--dashed">
									<li>
										<?php if ($discussion->author instanceof SocialPage) { ?>
											<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.cluster', $discussion->author); ?>
										<?php } else {  ?>
											<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $discussion->author); ?>
										<?php } ?>
									</li>

									<?php if($params->get('display_total_hits', true)){ ?>
									<li>
										<i class="fa fa-eye"></i>&nbsp; <?php echo $discussion->hits;?> <?php echo JText::_('APP_GROUP_DISCUSSIONS_HITS'); ?>
									</li>
									<?php } ?>

									<?php if ($params->get('display_total_replies', true)) { ?>
									<li>
										<i class="fa fa-comments"></i>&nbsp; <?php echo $discussion->total_replies;?> <?php echo JText::_('APP_GROUP_DISCUSSIONS_REPLIES'); ?>
									</li>
									<?php } ?>

									<?php if ($params->get('display_last_replied', true) && $discussion->lastreply) {   ?>
									<li class="g-list__item">
										<?php echo JText::sprintf('APP_GROUP_DISCUSSIONS_LAST_REPLIED_BY', $this->html('html.' . $discussion->lastreply->author->getType(), $discussion->lastreply->author->id)); ?>
									</li>
									<?php } ?>
								</ol>
							</div>
						</div>
					</div>

					<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
						<div class="es-apps-item__state">
							<span class="o-label o-label--success-o label-resolved"><?php echo JText::_('APP_GROUP_DISCUSSIONS_RESOLVED'); ?></span>
							<span class="o-label o-label--warning-o label-locked"><i class="fa fa-lock locked-icon"></i> <?php echo JText::_('APP_GROUP_DISCUSSIONS_LOCKED'); ?></span>
							<span class="o-label o-label--danger-o label-unanswered"><?php echo JText::_('APP_GROUP_DISCUSSIONS_UNANSWERED'); ?></span>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	<?php } ?>

	<?php echo $this->html('html.emptyBlock', 'APP_GROUP_DISCUSSIONS_EMPTY', 'fa-chart-bar'); ?>
</div>

<?php if ($pagination) { ?>
	<?php echo $pagination->getListFooter('site');?>
<?php } ?>
