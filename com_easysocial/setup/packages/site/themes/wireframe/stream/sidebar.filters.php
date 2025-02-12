<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if (!$cluster) { ?>
<li class="o-tabs__item <?php echo $filterId == $filter->id ? ' active' : '';?>" class="o-tabs__item" data-filter-item data-type="custom" data-id="<?php echo $filter->id; ?>">
	<a href="<?php echo $url; ?>" class="o-tabs__link">
		<?php echo $filter->_('title'); ?>
	</a>
</li>
<?php } else { ?>
<li class="o-tabs__item <?php echo $filterId == $filter->id ? ' active' : '';?>" class="o-tabs__item" data-filter-item="filters" data-type="filters" data-id="<?php echo $filter->id; ?>">
    <a href="<?php echo $url;?>" class="o-tabs__link">
        <?php echo $filter->_('title'); ?>
    </a>
</li>
<?php } ?>