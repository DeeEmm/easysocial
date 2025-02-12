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
<div class="es-container">
	<div class="es-content">
		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $backLink;?>" class="t-lg-pull-left btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_ES_OTHER_FEEDS'); ?></a>
				</div>
			</div>
		</div>

		<div class="es-apps-entry" data-item data-id="<?php echo $feed->id;?>">
			<div class="es-apps-entry__bd">
				<?php foreach ($feed->items as $item) { ?>
				<div class="es-apps-item es-island" data-item data-id="<?php echo $feed->id;?>">
					<div class="es-apps-item__hd">
						<div class="o-flag">
							<div class="o-flag__body">
								<a href="<?php echo $item->link;?>" class="t-font-weight--bold" target="_blank"><?php echo $item->title;?></a>
							</div>
						</div>
					</div>

					<div class="es-apps-item__bd">
						<?php echo $this->html('string.truncate', $item->content, 350); ?>
					</div>

					<div class="es-apps-item__ft es-bleed--bottom">
						<div class="o-grid">
							<div class="o-grid__cell">
								<div class="es-apps-item__meta">
									<div class="es-apps-item__meta-item">
										<ol class="g-list-inline g-list-inline--dashed">
											<li>
												<i class="far fa-clock"></i>&nbsp; <?php echo JFactory::getDate($item->publishedDate)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>
											</li>
										</ol>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
