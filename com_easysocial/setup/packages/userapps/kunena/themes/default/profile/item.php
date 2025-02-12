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

$dateFormat = $kunenaHelper->isKunena6() ? 'config_postDateFormat' : 'config_post_dateformat';
$datetime = $kunenaHelper->date($topic->first_post_time)->toKunena($dateFormat);
?>
<div class="es-apps-item es-island">
	<div class="es-apps-item__hd">
		<a href="<?php echo $topic->getPermaUrl($topic->category_id);?>" class="es-apps-item__title"><?php echo $topic->subject;?></a>
	</div>

	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<a href="<?php echo $topic->getCategory()->getUrl();?>">
									<i class="fa fa-folder"></i>&nbsp; <?php echo $topic->getCategory()->name;?>
								</a>
							</li>
							<li>
								<i class="fa fa-calendar"></i>&nbsp; <?php echo $datetime; ?>
							</li>
							<li>
								<i class="fa fa-eye"></i>&nbsp; <?php echo $topic->hits;?> <?php echo JText::_('COM_ES_VIEWS');?>
							</li>
						</ol>
					</div>
				</div>		
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
				<div class="es-apps-item__state">
					<?php echo $kunenaTemplate->getTopicIcon($topic); ?>
				</div>		
			</div>
		</div>
	</div>
</div>