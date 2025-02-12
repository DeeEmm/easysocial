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
<div class="es-apps-item es-island" data-review-item data-uid="<?php echo $review->uid;?>" data-type="<?php echo $review->type;?>" data-id="<?php echo $review->id;?>">
	<div class="es-apps-item__hd">
		<a href="<?php echo $review->getPermalink();?>" class="es-apps-item__title"><?php echo $review->title;?></a>
	</div>

	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc">
			<?php echo $review->message; ?>
		</div>

		<?php if ($review->isPending()) { ?>
		<div class="es-reviews-item__desp t-lg-mt--lg">
			<div class="o-grid o-grid--center">
				<div class="o-grid__cell">
					<?php echo JText::_('APP_REVIEWS_IS_PENDING_MODERATION'); ?>
				</div>

				<div class="o-grid__cell o-grid__cell--auto-size">
					<?php if ($isAdmin) { ?>
					<a href="javascript:void(0);" class="btn btn-es-default-o t-lg-mr--md" data-approve></i>&nbsp;<?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></a>
					<a href="javascript:void(0);" class="btn btn-es-danger-o" data-reject></i>&nbsp;<?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON'); ?></a>
				<?php } else { ?>
					<a href="javascript:void(0);" class="btn btn-es-default-o t-lg-mr--md" data-withdraw></i>&nbsp;<?php echo JText::_('COM_ES_WITHDRAW_BUTTON'); ?></a>
				<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>

	<?php if ($params->get('display_author_name', true) || $params->get('display_date', true)) { ?>
	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<?php if ($params->get('display_author_name', true)) { ?>
							<li>
								<i class="fa fa-user"></i>&nbsp;
								<?php echo $this->html('html.' . $review->getAuthor()->getType(), $review->getAuthor()->id); ?>
							</li>
							<?php } ?>

							<?php if ($params->get('display_date', true)) { ?>
							<li>
								<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($review->created)->format(JText::_('DATE_FORMAT_LC'));?>
							</li>
							<?php } ?>
						</ol>
					</div>
				</div>
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
				<div class="es-apps-entry__state">
					<?php echo $this->includeTemplate('site/reviews/ratings/default', array('id' => $review->id, 'score' => $review->value)); ?>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
