<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="<?php echo !$polls ? ' is-empty' : '';?>" data-result>
	<div class="es-cards es-cards--2">
		<?php if ($polls) { ?>
			<?php foreach ($polls as $poll) { ?>
			<div class="es-cards__item">
				<div class="es-card no-hd">
					<div class="es-card__bd">
						<div class="es-card__title">
							<a href="<?php echo $poll->getPermalink();?>"><?php echo $poll->title;?></a>
						</div>

						<div class="es-card__meta">
							<?php echo JText::sprintf('COM_EASYSOCIAL_POLLS_CREATED_BY', $this->html('html.' . $poll->getAuthor()->getType(), $poll->getAuthor()));?>
						</div>
					</div>

					<div class="es-card__ft es-card--border">
						<ul class="g-list-inline g-list-inline--space-right">
							<?php if ($poll->hasExpirationDate() && !$poll->hasExpired()) { ?>
							<li data-es-provide="tooltip" data-title="<?php echo JText::sprintf('COM_ES_POLL_WILL_EXPIRE_ON', $poll->getExpiryDate()->format('DATE_FORMAT_LC4'));?>">
								<i class="fa fa-clock"></i>
							</li>
							<?php } ?>

							<li>
								<i class="fa fa-chart-bar"></i>&nbsp;
								<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_POLLS_VOTES_COUNT', $poll->getTotalVotes()), $poll->getTotalVotes()); ?>
							</li>

							<li>
							<?php if ($poll->isMultiple()) { ?>
								<i class="fa fa-list-ul"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_MULTIPLE_CHOICES'); ?>
							<?php } else { ?>
								<i class="fa fa-list-ol"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_SINGLE_CHOICE'); ?>
							<?php } ?>
							</li>

							<?php if ($poll->hasExpired()) { ?>
							<li>
								<span class="o-label o-label--danger-o">
									<i class="far fa-clock"></i> <?php echo JText::_('COM_EASYSOCIAL_POLLS_EXPIRED'); ?>
								</span>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
	</div>

	<?php echo $this->html('html.emptyBlock', $filter == 'mine' ? 'COM_EASYSOCIAL_POLLS_NEVER_CREATED_YET' : ($filter == 'user' ? 'COM_ES_POLLS_USER_EMPTY' : 'COM_EASYSOCIAL_POLLS_EMPTY_POLLS'), 'fa-chart-bar'); ?>
</div>

<?php if ($pagination) { ?>
	<?php echo $pagination->getListFooter('site');?>
<?php } ?>
