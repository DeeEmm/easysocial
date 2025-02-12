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
<div class="es-apps-item es-island" data-feed-item data-id="<?php echo $feed->id;?>">
	<div class="es-apps-item__hd">
		<div class="o-flag">
			<div class="o-flag__image">
				<i class="fa fa-rss-square"></i>
			</div>
			<div class="o-flag__body">
				<a href="<?php echo $feed->getPermalink();?>"><?php echo $feed->title;?></a>
			</div>
		</div>

		<?php if ($user->isViewer()) { ?>
		<div class="es-apps-item__action">
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="javascript:void(0);" data-feeds-remove><?php echo JText::_('COM_ES_DELETE');?></a>
					</li>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($feed->created)->toLapsed();?>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
