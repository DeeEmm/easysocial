<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if($params->get('format', 1) == 1){ ?>
	<?php echo $name->first;?> <?php echo $name->middle;?> <?php echo $name->last;?>
<?php } ?>

<?php if($params->get('format', 1) == 2){ ?>
	<?php echo $name->last;?> <?php echo $name->middle;?> <?php echo $name->first;?>
<?php } ?>

<?php if($params->get('format', 1) == 3){ ?>
	<?php echo $name->name;?>
<?php } ?>

<?php if($params->get('format', 1) == 4){ ?>
	<?php echo $name->first;?> <?php echo $name->last;?>
<?php } ?>

<?php if($params->get('format', 1) == 5){ ?>
	<?php echo $name->last;?> <?php echo $name->first;?>
<?php } ?>
