<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<a href="javascript:void(0);"
	data-es-repost-counter
	data-id="<?php echo $uid; ?>"
	data-element="<?php echo $element; ?>.<?php echo $group;?>"
	data-count="<?php echo $count; ?>"
	data-popbox="module://easysocial/repost/authors"
	data-popbox-toggle="click" rel="nofollow"
>
	<i class="fa fa-retweet"></i>&nbsp; <?php echo $count;?>
</a>
