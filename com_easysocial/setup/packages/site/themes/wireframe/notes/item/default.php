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
<div class="es-container" data-canvas-app-notes data-id="<?php echo $note->id; ?>">
	<div class="es-content">
		
		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $backLink;?>" class="t-lg-pull-left btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_ES_BACK'); ?></a>
				</div>
			</div>
		</div>
		
		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry">
				<div class="es-apps-entry__hd">
					<a href="<?php echo $note->getPermalink();?>" class="es-apps-entry__title">
						<?php echo $note->title;?>
					</a>
				</div>

				<div class="es-apps-entry__ft es-bleed--middle">
					<div class="o-grid">
						<div class="o-grid__cell">
							<div class="es-apps-entry__meta">
								<div class="es-apps-entry__meta-item">
									<ol class="g-list-inline g-list-inline--dashed">
										<li>
											<time datetime="<?php echo $this->html('string.date' , $note->created ); ?>">
												<i class="fa fa-calendar"></i>&nbsp; <?php echo $this->html('string.date', $note->created, JText::_('DATE_FORMAT_LC3')); ?>
											</time>
										</li>
									</ol>
								</div>
							</div>		
						</div>
					</div>
				</div>

				<div class="es-apps-entry__bd">
					<div class="es-apps-entry__desc">
						<?php echo $note->getContent();?>
					</div>
				</div>

				<div class="es-actions es-bleed--bottom" data-stream-actions>
					<div class="es-actions__item es-actions__item-action">
						<div class="es-actions-wrapper">
							<ul class="es-actions-list">
								<li>
									<?php echo $likes->button(true);?>
								</li>
							</ul>
						</div>
					</div>
					<div class="es-actions__item es-actions__item-stats">
						<?php echo $likes->html(); ?>
					</div>
					<div class="es-actions__item es-actions__item-comment">
						<?php echo $comments->html(array('hideEmpty' => false));?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>