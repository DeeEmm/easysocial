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

<?php if ($activeCategory && $this->config->get('groups.category.header', true)) { ?>
<div class="t-lg-mb--xl">
	<?php echo $this->html('miniheader.groupCategory', $activeCategory); ?>
</div>
<?php } ?>

<?php if (!empty($featuredGroups)) { ?>
<div class="es-snackbar2">
	<div class="es-snackbar2__context">
		<div class="es-snackbar2__title">
			<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FEATURED_GROUPS');?>
		</div>
	</div>
	<div class="es-snackbar2__actions">
		<a href="<?php echo ESR::groups(array('filter' => 'featured')); ?>" class="btn btn-sm btn-es-default-o">
			<?php echo JText::_('COM_ES_VIEW_ALL'); ?>
		</a>
	</div>
</div>

<div class="<?php echo $this->isMobile() ? 'es-list' : 'es-cards es-cards--2';?>">
	<?php foreach ($featuredGroups as $group) { ?>
		<?php echo $this->html('listing.group', $group, array('displayType' => false, 'showDistance' => $showDistance, 'style' => $this->isMobile() ? 'listing' : 'card')); ?>
	<?php } ?>
</div>
<?php } ?>

<div data-result>

	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_($heading);?>
			</div>
		</div>
		<?php if ($browseView) { ?>
		<div class="es-snackbar2__actions">
			<?php if ($showDistanceSorting) { ?>
				<?php
					$sortOptions = array();
					$sortOptions[] = $this->html('form.popdownOption', '1', '1 ' . $distanceUnit, '', false, array('data-radius="1"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '5', '5 ' . $distanceUnit, '', false, array('data-radius="5"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '10', '10 ' . $distanceUnit, '', false, array('data-radius="10"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '25', '25 ' . $distanceUnit, '', false, array('data-radius="25"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '50', '50 ' . $distanceUnit, '', false, array('data-radius="50"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '100', '100 ' . $distanceUnit, '', false, array('data-radius="100"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '200', '200 ' . $distanceUnit, '', false, array('data-radius="200"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '300', '300 ' . $distanceUnit, '', false, array('data-radius="300"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '400', '400 ' . $distanceUnit, '', false, array('data-radius="400"'), '');
					$sortOptions[] = $this->html('form.popdownOption', '500', '500 ' . $distanceUnit, '', false, array('data-radius="500"'), '');
				?>
				<?php echo $this->html('form.popdown', 'radius', $distance, $sortOptions, 'left'); ?>
			<?php } ?>

			<?php if ($groups) { ?>
				<?php echo $this->html('form.popdown', 'sorting_test', $ordering, array(
					$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_LATEST', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
					$this->html('form.popdownOption', 'name', 'COM_ES_SORT_BY_ALPHABETICALLY', '', false, $sortItems->name->attributes, $sortItems->name->url),
					$this->html('form.popdownOption', 'popular', 'COM_ES_SORT_BY_POPULARITY', '', false, $sortItems->popular->attributes, $sortItems->popular->url)
				)); ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>

	<div class="es-list-result" data-sub-wrapper>
		<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true)); ?>

		<div class="<?php echo !$groups && empty($featuredGroups) ? ' is-empty' : '';?>" data-list>
			<?php if ($groups) { ?>
				<?php echo $this->loadTemplate('site/groups/default/items', array('groups' => $groups, 'pagination' => $pagination, 'browseView' => $browseView, 'heading' => $heading, 'showDistance' => $showDistance)); ?>
			<?php } ?>

			<?php echo $this->html('html.emptyBlock', $emptyText, 'fa-users'); ?>
		</div>
	</div>
</div>
