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
	class="btn btn-es-default-o btn-sm"
	data-es-share-button
	data-url="<?php echo $url; ?>"
	data-title="<?php echo $this->html('string.escape', $title); ?>"
	data-summary="<?php echo $this->html('string.escape', $summary); ?>"
	data-es-provide="tooltip"
	data-original-title="<?php echo $this->html('string.escape', $text); ?>"
>
	<i class="fa fa-share-alt"></i>
</a>
