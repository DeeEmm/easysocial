<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php for ($i = 1; $i <= $rows; $i++) { ?>
<div class="o-grid o-grid--gutters">
	<?php for ($x = 1; $x <= $columns; $x++) { ?>
	<div class="o-grid__cell o-grid--<?php echo $columnStyle;?>">
		<div class="ph-item">
			<div class="ph-col-12">
				<div class="ph-picture"></div>
			</div>

			<?php if (!$pictureOnly) { ?>
			<div>
				<div class="ph-row">
					<div class="ph-col-12 big"></div>

					<div class="ph-col-12 empty"></div>

					<div class="ph-col-12"></div>
					<div class="ph-col-12"></div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
<?php } ?>
