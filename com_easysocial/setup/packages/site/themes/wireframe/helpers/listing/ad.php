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
<div class="es-cards__item" data-id="<?php echo $ad->id;?>">
	<div class="es-card">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
						<ul class="dropdown-menu">
							<?php if ($ad->canEdit()) { ?>
							<li>
								<a href="<?php echo ESR::ads(['layout' => 'form', 'id' => $ad->id]);?>">
									<?php echo JText::_('COM_EASYSOCIAL_EDIT'); ?>
								</a>
							</li>
							<?php } ?>

							<li class="divider"></li>
							<li>
								<a href="javascript:void(0);" data-es-ads-delete data-id="<?php echo $ad->id;?>">
									<?php echo JText::_('COM_EASYSOCIAL_DELETE'); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<a href="<?php echo $ad->link;?>" class="embed-responsive embed-responsive-16by9">
				<div class="embed-responsive-item es-card__cover" 
				style="
					background-image: url('<?php echo $ad->getCover();?>');
					background-position: center center;
				">
				</div>
			</a>
		</div>
		<div class="es-card__bd es-card--border">
			<div class="es-card__title">
				<?php echo $ad->title;?>
			</div>

			<?php if ($ad->link) { ?>
			<div>
				<a href="<?php echo $ad->link;?>"><?php echo $this->html('string.truncate', $ad->link, 50, false, false, false, false, true); ?></a>
			</div>
			<?php } ?>
			
			<div>
				<span><?php echo $ad->getCreatedDate()->format(JText::_('DATE_FORMAT_LC3'));?></span>
			</div>
			
			<?php if ($ad->intro) { ?>
			<div class="es-card__meta t-lg-mb--sm">
				<?php echo $this->html('string.truncate', $ad->intro, 120, false, false, false, false, true); ?>
			</div>
			<?php } ?>
		</div>
		<div class="es-card__ft es-card--border">
			<ul class="g-list-flex">
				<li>
					<div><?php echo JText::sprintf('COM_ES_AD_IMPRESSIONS', ES::formatNumbers($ad->view));?></div>
				</li>

				<li>
					<div><?php echo JText::sprintf('COM_ES_AD_CLICKS', ES::formatNumbers($ad->click));?></div>
				</li>

				<li>
					<span><?php echo $ad->getPriority();?></span>
				</li>

				<?php if ($ad->isUnpublished()) { ?>
				<li>
					<span class="t-text--danger" data-title="<?php echo JText::_('COM_ES_AD_UNPUBLISHED_INFO');?>" data-es-provide="tooltip">
						<?php echo JText::_('COM_ES_UNPUBLISHED'); ?>
					</span>
				</li>
				<?php } ?>

				<?php if ($ad->isDraft()) { ?>
				<li>
					<span class="t-text--warning" data-title="<?php echo JText::_('COM_ES_AD_DRAFT_INFO');?>" data-es-provide="tooltip">
						<?php echo JText::_('COM_ES_DRAFT'); ?>
					</span>
				</li>
				<?php } ?>

				<?php if ($ad->isUnderModeration()) { ?>
				<li>
					<span class="t-text--warning" data-title="<?php echo JText::_('COM_ES_AD_PENDING_APPROVALS_INFO');?>" data-es-provide="tooltip">
						<?php echo JText::_('COM_ES_PENDING_APPROVALS'); ?>
					</span>
				</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>



