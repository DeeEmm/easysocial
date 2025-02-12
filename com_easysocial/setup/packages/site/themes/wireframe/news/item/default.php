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
<div class="es-container">

	<div class="es-content" data-news-item data-id="<?php echo $news->id;?>">

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $cluster->getAppPermalink('news');?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_ES_BACK'); ?></a>
				</div>

				<?php if ($cluster->canCreateNews()) { ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right">
						<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
							<i class="fa fa-ellipsis-h"></i>
						</button>

						<ul class="dropdown-menu dropdown-menu-right">
							<li>
								<a href="<?php echo $news->getEditPermalink();?>"><?php echo JText::_('APP_GROUP_NEWS_EDIT_ITEM'); ?></a>
							</li>
							<li class="divider"></li>
							<li>
								<a href="javascript:void(0);" data-delete><?php echo JText::_('APP_GROUP_NEWS_DELETE_ITEM'); ?></a>
							</li>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry">
				<div class="es-apps-entry__hd">
					<div class="es-apps-entry__title"><?php echo ES::string()->escape($news->_('title'));?></div>
				</div>

				<?php if($params->get('display_author', true) || $params->get('display_hits', true) || $params->get('display_date', true)){ ?>
				<div class="es-apps-entry__ft es-bleed--middle">
					<div class="o-grid">
						<div class="o-grid__cell">
							<div class="es-apps-entry__meta">
								<div class="es-apps-entry__meta-item">
									<ol class="g-list-inline g-list-inline--dashed">
										<?php if($params->get('display_author', true)){ ?>
										<li>
											<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $author); ?>
										</li>
										<?php } ?>

										<?php if($params->get('display_date', true)){ ?>
										<li>
											<i class="far fa-calendar-alt"></i>&nbsp; <?php echo ES::date($news->created)->format(JText::_('DATE_FORMAT_LC'));?>
										</li>
										<?php } ?>

										<?php if($params->get('display_hits', true)){ ?>
										<li>
											<i class="fa fa-eye"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('APP_GROUP_NEWS_HITS', $news->hits), $news->hits); ?>
										</li>
										<?php } ?>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>

				<div class="es-apps-entry__bd">
					<div class="es-apps-entry__desc">
						<?php echo $news->getContent();?>
					</div>
				</div>

				<div class="es-actions es-bleed--bottom" data-stream-actions>
					<div class="es-actions__item es-actions__item-action">
						<div class="es-actions-wrapper">
							<ul class="es-actions-list">
								<li>
									<?php echo $likes->button(true);?>
								</li>

								<?php if ($this->config->get('sharing.enabled')) { ?>
								<li>
									<?php echo ES::sharing(array('url' => ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'newsId' => $news->id, 'external' => true), false), 'display' => 'dialog', 'text' => JText::_('COM_EASYSOCIAL_STREAM_SOCIAL'), 'css' => ''))->getHTML(false); ?>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<div class="es-actions__item es-actions__item-stats">
						<div data-news-counter>
							<?php echo $likes->toHTML(); ?>
						</div>
					</div>
					<div class="es-actions__item es-actions__item-comment">
						<?php if ($params->get('allow_comments', true) && $news->comments) { ?>
							<?php echo $comments->getHTML(array('hideEmpty' => false));?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
