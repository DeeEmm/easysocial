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
<a href="javascript:void(0);"
	data-reports-link
	data-actorid="<?php echo $actorid; ?>"
	data-url="<?php echo $url; ?>"
	data-uid="<?php echo $uid; ?>"
	data-type="<?php echo $type; ?>"
	data-extension="<?php echo $extension;?>"
	data-object="<?php echo $this->html('string.escape', $itemTitle); ?>"
	data-title="<?php echo $this->html('string.escape', $title); ?>"
	data-description="<?php echo $this->html('string.escape', $description); ?>"
>

	<?php if ($icon) { ?>
	<i class="fa fa-exclamation-triangle"></i>&nbsp;
	<?php } ?>

	<?php echo $text; ?>
</a>
