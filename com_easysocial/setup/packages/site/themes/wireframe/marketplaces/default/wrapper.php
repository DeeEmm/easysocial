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
<?php if ($activeCategory && $this->config->get('marketplaces.layout.header', true)) { ?>
<div class="t-lg-mb--xl">
	<?php echo $this->html('miniheader.marketplaceCategory', $activeCategory); ?>
</div>
<?php } ?>


<?php if (!empty($featuredListings)) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_('COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_FEATURED');?>
			</div>
		</div>
	</div>

	<div class="<?php echo $this->isMobile() ? 'es-list' : 'es-cards es-cards--2';?>">
		<?php foreach ($featuredListings as $listing) { ?>
			<?php echo $this->html('listing.marketplace', $listing, array(
					'style' => $this->isMobile() ? 'listing' : 'card',
					'browseView' => $browseView,
					'from' => $from
				)); ?>
		<?php } ?>
	</div>
<?php } ?>

<div>

	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_($title);?>
			</div>
		</div>

		<div class="es-snackbar2__actions">
			<?php if ($browseView) { ?>
				<?php if ($showDistanceSorting) { ?>
					<?php echo $this->html('form.popdown', 'radius', $distance, [
						$this->html('form.popdownOption', '1', '1 ' . $distanceUnit, '', false, array('data-radius="1"'), ''),
						$this->html('form.popdownOption', '5', '5 ' . $distanceUnit, '', false, array('data-radius="5"'), ''),
						$this->html('form.popdownOption', '10', '10 ' . $distanceUnit, '', false, array('data-radius="10"'), ''),
						$this->html('form.popdownOption', '25', '25 ' . $distanceUnit, '', false, array('data-radius="25"'), ''),
						$this->html('form.popdownOption', '50', '50 ' . $distanceUnit, '', false, array('data-radius="50"'), ''),
						$this->html('form.popdownOption', '100', '100 ' . $distanceUnit, '', false, array('data-radius="100"'), ''),
						$this->html('form.popdownOption', '200', '200 ' . $distanceUnit, '', false, array('data-radius="200"'), ''),
						$this->html('form.popdownOption', '300', '300 ' . $distanceUnit, '', false, array('data-radius="300"'), ''),
						$this->html('form.popdownOption', '400', '400 ' . $distanceUnit, '', false, array('data-radius="400"'), ''),
						$this->html('form.popdownOption', '500', '500 ' . $distanceUnit, '', false, array('data-radius="500"'), '')
					], 'left'); ?>
				<?php } ?>
				<?php if ($listings) { ?>
					<?php echo $this->html('form.popdown', 'sorting', $sort, array(
						$this->html('form.popdownOption', 'alphabetical', 'COM_ES_SORT_BY_ALPHABETICALLY', '', false, $sortItems->alphabetical->attributes, $sortItems->alphabetical->url),
						$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_LATEST', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
						$this->html('form.popdownOption', 'oldest', 'COM_ES_SORT_BY_SHORT_OLDEST', '', false, $sortItems->oldest->attributes, $sortItems->oldest->url),
						$this->html('form.popdownOption', 'price_high', 'COM_ES_SORT_BY_SHORT_PRICE_HIGH', '', false, $sortItems->price_high->attributes, $sortItems->price_high->url),
						$this->html('form.popdownOption', 'price_low', 'COM_ES_SORT_BY_SHORT_PRICE_LOW', '', false, $sortItems->price_low->attributes, $sortItems->price_low->url),
						$this->html('form.popdownOption', 'stock_high', 'COM_ES_SORT_BY_SHORT_STOCK_HIGH', '', false, $sortItems->stock_high->attributes, $sortItems->stock_high->url),
						$this->html('form.popdownOption', 'stock_low', 'COM_ES_SORT_BY_SHORT_STOCK_LOW', '', false, $sortItems->stock_low->attributes, $sortItems->stock_low->url),
						$this->html('form.popdownOption', 'commented', 'COM_ES_SORT_BY_MOST_COMMENTED', '', false, $sortItems->commented->attributes, $sortItems->commented->url),
						$this->html('form.popdownOption', 'likes', 'COM_ES_SORT_BY_MOST_LIKES', '', false, $sortItems->likes->attributes, $sortItems->likes->url)
					)); ?>
				<?php } ?>
			<?php } ?>
		</div>
	</div>

	<div class="es-list-result" data-sub-wrapper>

		<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => false)); ?>

		<div data-items-list>
			<?php echo $this->includeTemplate('site/marketplaces/default/items'); ?>
		</div>
	</div>
</div>
