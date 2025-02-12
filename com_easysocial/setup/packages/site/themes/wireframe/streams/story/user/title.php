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
<?php echo $this->html('html.user', $actor); ?>

<?php if ($target && $actor->id != $target->id) { ?>
	&nbsp;<i class="fa fa-caret-right"></i>&nbsp;<?php echo $this->html('html.user', $target); ?>
<?php } ?>

<?php if ($articleStream && $articleStreamLink && $articleStreamTitle) { ?>
	&nbsp;<i class="fa fa-caret-right"></i>&nbsp;<a href="<?php echo $articleStreamLink;?>"><?php echo $articleStreamTitle;?></a>
<?php } ?>

<?php if ($stream->showPageTitle()) { ?>
	<?php echo $this->html('html.anywhereTitle', $stream); ?>
<?php } ?>
