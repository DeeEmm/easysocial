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
<div class="es-single-app o-box" data-apps-store>
	<div data-app-item data-id="<?php echo $app->id;?>">
		
		<?php echo $this->html('avatar.mini', $app->getTitle(), '', $app->getLogo(), 'lg', '', '', false, 'square'); ?>

		<?php if ($app->getTypeLabel()) { ?>
		<span class="o-label o-label--info es-card__btn-published">
			<b><?php echo $app->getTypeLabel();?></b>
		</span>
		<?php } ?>

		<?php if ($app->isInstalled()) { ?>
		<a href="javascript:void(0);" class="es-single-app__btn-installed btn btn-es-default-o btn-sm disabled">
			<b><?php echo JText::_('Installed');?></b>
		</a>
		<?php } else { ?>

			<?php if ($app->isDownloadable() && $app->isDownloadableFromApi()) { ?>
			<button type="button" class="es-single-app__btn-installed btn btn-es-primary-o btn-sm" data-app-install>
				<b><?php echo JText::_('Install');?></b>
			</button>
			<?php } else { ?>

				<?php if ($app->isFree()) { ?>
				<button type="button" class="es-single-app__btn-installed btn btn-es-primary-o btn-sm" data-app-install>
					<b><?php echo JText::_('Install (FREE)');?></b>
				</button>
				<?php } ?>

				<?php if (!$app->isFree()) { ?>
				<button type="button" class="es-single-app__btn-installed btn btn-es-primary-o btn-sm" data-app-install>
					<b><?php echo JText::sprintf('Install ($%1$s)', $app->getPrice());?></b>
				</button>
				<?php } ?>
			<?php } ?>
		<?php } ?>

		<a class="es-single-app__title" href="/"><?php echo $app->getTitle();?></a>

		<div class="es-single-app__meta">
			<ul class=" g-list-inline g-list-inline--dashed ">
				<li>
					<b><?php echo $app->category;?></b>
				</li>
				<li>
					<b>v<?php echo $app->version;?></b>
				</li>
			</ul>
		</div>
		<div class="es-single-app__desc"><?php echo $app->getDescription(false);?></div>

		<div style="display: inline-block;">
			<div class="stars" data-ratings data-score="<?php echo $app->getScore();?>" style="display: inline-block;"></div> 
			<a href="<?php echo $app->getExternalPermalink();?>" target="_blank">
			<?php if (!$app->votes) { ?>
				No votes yet. Be the first to vote for this app
			<?php } else { ?>
				Based on <?php echo $app->votes;?> reviews
			<?php } ?>
			</a>
		</div>        
	</div>
	<div class="o-box--border">
		<div class="es-single-app__title">
			<?php echo JText::sprintf('Screenshots (%1$s)', $app->getTotalScreenshots());?>
		</div>
		<div class="es-single-app__screen-list">
			<?php foreach ($app->getScreenshots() as $screenshot) { ?>
				<a href="<?php echo $screenshot;?>" target="_blank" class="es-single-app__screen-item" style="background-image:url('<?php echo $screenshot;?>');">
					
				</a>
			<?php } ?>
		</div>
	</div>
</div>