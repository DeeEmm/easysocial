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

$timeformat = $params->get('agenda_timeformat', '12') == '24' ? JText::_('COM_EASYSOCIAL_DATE_DMY24H') : JText::_('COM_EASYSOCIAL_DATE_DMY12H');
?>
<div class="es-container">
	<div class="es-content">

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo ESR::profile(array('id' => $user->getAlias(), 'appId' => $app->getAlias()));?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_( 'APP_CALENDAR_CANVAS_RETURN_TO_CALENDAR' );?></a>
				</div>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry">
				<div class="es-apps-entry__hd">
					<div class="es-apps-entry__title"><?php echo $calendar->get('title'); ?></div>
				</div>

				<div class="es-apps-entry__ft es-bleed--middle">
					<div class="o-grid">
						<div class="o-grid__cell">
							<div class="es-apps-entry__meta">
								<div class="es-apps-entry__meta-item">
									<ol class="g-list-inline g-list-inline--dashed">
										<li>
											<?php echo $calendar->getStartDate()->format( $timeformat );?> - <?php echo $calendar->getEndDate()->format( $timeformat );?>
										</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="es-apps-entry__bd">
					<div class="es-apps-entry__desc">
						<?php echo $calendar->get('description'); ?>
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
						<?php echo $likes->toHTML(); ?>
					</div>
					<div class="es-actions__item es-actions__item-comment">
						<div class="es-comments-wrapper">
							<?php echo $comments->getHTML(array('hideEmpty' => false));?>
						</div>
					</div>
				</div>
			</div>
		</div>


	</div>
</div>
