<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div data-sharing-popover data-sharing-popover-<?php echo $uniqueid; ?> style="position:relative;">
	<a href="javascript:void(0);" data-sharing-popover-link data-url="<?php echo $url; ?>"><?php if ($icon) { ?><i class="fa fa-share"></i> <?php } ?><?php echo $text; ?></a>

	<div data-sharing-contents class="dropdown-menu">
		<div class="arrow"></div>

		<h3 class="popover-title"><?php echo $text; ?></h3>

		<div class="popover-content">
			<?php echo $contents; ?>
		</div>
	</div>
</div>
