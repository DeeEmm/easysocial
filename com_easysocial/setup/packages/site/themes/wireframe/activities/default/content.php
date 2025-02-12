<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$filterType = isset($filtertype) ? $filtertype : 'all';
?>
<?php echo $this->html('html.snackbar', $title, 'h1'); ?>

<div data-activities-content>
	<?php if ($activities) { ?>
		<ul class="es-stream-list" data-activities-list>
			<?php if ($filterType == 'hiddenapp') { ?>
				<?php echo $this->loadTemplate( 'site/activities/items/hiddenapp' , array('items' => $activities)); ?>
			<?php } else if ($filterType == 'hiddenactor') { ?>
				<?php echo $this->loadTemplate( 'site/activities/items/hiddenactor' , array('items' => $activities)); ?>
			<?php } else { ?>
				<?php echo $this->loadTemplate( 'site/activities/items/default' , array('items' => $activities, 'nextlimit' => $nextlimit, 'active' => $active)); ?>
			<?php } ?>
		</ul>

		<?php if (($filterType != 'hiddenapp' && $filterType != 'hiddenactor') && $nextlimit){ ?>
		<div class="es-pagination">
			<a class="btn btn-es-default-o btn-block" href="javascript:void(0);" data-pagination data-startlimit="<?php echo $nextlimit; ?>" data-type="<?php echo $active; ?>">
				<i class="fa fa-refresh"></i>
				<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_LOG_LOAD_PREVIOUS_STREAM_ITEMS'); ?>
				<div class="o-loader o-loader--sm"></div>
			</a>
		</div>
		<?php } ?>
	<?php } ?>
</div>

<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_ACTIVITY_NO_ACTIVITY_LOG', 'fa-database'); ?>
