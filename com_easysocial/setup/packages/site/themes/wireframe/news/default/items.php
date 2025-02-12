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
?>
<div class="es-apps-item es-island" data-news-item data-id="<?php echo $news->id;?>">
	<div class="es-apps-item__hd">

		<a href="<?php echo $news->getPermalink();?>" class="es-apps-item__title"><?php echo $news->title; ?></a>

		<?php if ($cluster->canCreateNews()) { ?>
		<div class="es-apps-item__action">
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="<?php echo $news->getEditPermalink();?>"><?php echo JText::_('APP_GROUP_NEWS_EDIT_ITEM'); ?></a>
					</li>
					<?php if ($cluster->canDeleteNews($news)) { ?>
					<li class="divider"></li>
					<li>
						<a href="javascript:void(0);" data-delete><?php echo JText::_('APP_GROUP_NEWS_DELETE_ITEM'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

	</div>

	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc">
			<?php echo $news->content; ?>
		</div>
		<div class="es-apps-item__item-action">
			<a href="<?php echo $news->getPermalink();?>" class="btn btn-es-default-o btn-sm"><?php echo JText::_('COM_ES_VIEW_POST');?></a>
		</div>
	</div>

	<?php if ($params->get('display_date', true) || $params->get('display_author_name', true)) { ?>
	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<?php if ($params->get('display_author_name', true)) { ?>
							<li>
								<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $news->author);?>
							</li>
							<?php } ?>

							<li>
								<i class="fa fa-calendar"></i>&nbsp; <?php echo ES::date($news->created)->format(JText::_('DATE_FORMAT_LC'));?>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
