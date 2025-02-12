<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<span class="es-gif-browser <?php echo $story ? 'is-story' : 'is-comment';?>" data-giphy-browser>
	<div class="o-tabs o-tabs--es t-justify-content--se" >
		<div class="o-tabs__item t-flex-grow--1 active" data-giphy-gifs-tab>
			<a class="o-tabs__link" href="javascript:void(0);"><?php echo JText::_('COM_ES_GIPHY_GIFS'); ?></a>
		</div>
		<div class="o-tabs__item t-flex-grow--1" data-giphy-stickers-tab>
			<a class="o-tabs__link" href="javascript:void(0);"><?php echo JText::_('COM_ES_GIPHY_STICKERS'); ?></a>
		</div>
	</div>
	<div class="ed-giphy" data-giphy-container>
		<span class="es-gif-browser__input-search">
			<input data-giphy-search type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_ES_GIPHY_GIFS_SEARCH'); ?>">
		</span>
		<span class="es-gif-browser__result">
			<span class="o-loader o-loader--sm t-lg-mt--xl" data-giphy-loading></span>
			<?php if ($gifs) { ?>
				<span class="es-gif-browser__result-label" data-giphy-trending><?php echo JText::_('COM_ES_GIPHY_TRENDING'); ?></span>

				<div class="es-gif-list-container" data-gifs-list data-offset="<?php echo $this->config->get('giphy.limit'); ?>">
					<?php if (!empty($gifs)) { ?>
					<ul class="es-gif-list">
						<?php echo $this->output('site/giphy/browser/list', ['giphies' => $gifs, 'story' => $story]); ?>
					</ul>
					<?php } ?>
				</div>

				<div class="es-gif-list-container" data-stickers-list data-offset="0">
					<ul class="es-gif-list">
					</ul>
				</div>

				<div class="<?php echo empty($gifs) ? '' : 't-hidden'; ?>" data-giphy-empty>
					<?php echo $this->output('site/giphy/browser/empty'); ?>
				</div>
			<?php } else { ?>
				<span class="es-gif-browser__result-text" data-giphy-no-result><?php echo JText::_('COM_ES_GIPHY_NO_RESULT'); ?></span>
			<?php } ?>

			<div class="t-position--relative t-py--lg t-hidden" data-giphy-loadmore-loading>
				<div class="o-loader o-loader--sm is-active"></div>
			</div>

			<div class="t-lg-mt--md" data-giphy-loadmore-wrapper>
				<a href="javascript:void(0);" class="t-text--center t-lg-mt--md" data-giphy-loadmore>
					<?php echo JText::_('COM_ES_GIPHY_LOAD_MORE'); ?>
				</a>
			</div>
		</span>
	</div>

	<span class="es-gif-browser__result-footer">
		<span class="es-powered-by-giphy"></span>
	</span>
</span>