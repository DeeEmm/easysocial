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
<div class="es-container" data-review-item data-uid="<?php echo $cluster->id;?>" data-type="<?php echo $cluster->getType();?>" data-id="<?php echo $reviews->id;?>">
	<div class="es-content">

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $appPermalink; ?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('APP_REVIEWS_BACK_TO_REVIEWS'); ?></a>
				</div>

				<?php if ($isAdmin || $this->my->isSiteAdmin() || $reviews->created_by == $this->my->id) { ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right" role="group">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
							<i class="fa fa-ellipsis-h"></i>
						</a>

						<ul class="dropdown-menu dropdown-menu-user messageDropDown">
							<li>
								<a href="<?php echo $reviews->getEditPermalink();?>"><?php echo JText::_('APP_REVIEWS_EDIT'); ?></a>
							</li>
							<li>
								<a href="javascript:void(0);" data-delete><?php echo JText::_('APP_REVIEWS_DELETE'); ?></a>
							</li>

							<?php if ($reviews->isPending() && $isAdmin) { ?>
								<li class="divider"></li>

								<li>
									<a href="javascript:void(0);" data-approve><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></a>
								</li>
								<li>
									<a href="javascript:void(0);" data-reject><?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON'); ?></a>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry">

				<div class="es-apps-entry__hd">
					<h3 class="es-app-reviews-title">
						<a href="<?php echo $reviews->getPermalink();?>"><?php echo $reviews->_('title');?></a>
					</h3>
				</div>
				<div class="es-apps-entry__bd">
					<div class="es-apps-entry__desc">
						<div class="es-app-reviews-content">
							<?php echo $reviews->message;?>
						</div>
					</div>
				</div>

				<div class="es-apps-entry__ft es-bleed--bottom">
					<div class="o-grid">
						<div class="o-grid__cell">
							<div class="es-apps-entry__meta">
								<div class="es-apps-entry__meta-item">
									<ol class="g-list-inline g-list-inline--dashed">
										<li>
											<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.' . $author->getType(), $author->id); ?>
										</li>
										<li>
											<i class="fa fa-calendar"></i>&nbsp; <?php echo ES::date($reviews->created)->format(JText::_('DATE_FORMAT_LC1'));?>
										</li>
									</ol>
								</div>
							</div>
						</div>
						<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
							<div class="es-apps-entry__state">
								<?php echo $this->includeTemplate('site/reviews/ratings/default', array('id' => $reviews->id, 'score' => $reviews->value)); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
