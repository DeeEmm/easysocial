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

// On this page we can perform the necessary requirement checks that we want

// Ensure that PHP Fileinfo extension is enabled
$fileInfoEnabled = extension_loaded('fileinfo');
?>
<form action="index.php?option=com_easysocial" method="post" name="installation" data-form>

	<?php if (!$fileInfoEnabled) { ?>
	<div class="si-alert si-alert--danger" data-source-errors data-api-errors>
		<h3>Missing PHP Module</h3>
		<p>
			EasySocial requires the <a href="https://www.php.net/manual/en/fileinfo.installation.php" target="_blank">PHP Fileinfo extension</a> to be enabled on the site so that image uploads can take full advantage of the features offered by PHP.
		</p>
		<div class="mt-4">
			<a href="https://stackideas.com/forums" class="btn btn-danger" target="_blank">Contact Support</a>
		</div>
	</div>
	<?php } ?>

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="active" value="<?php echo $active; ?>" />
</form>


<script>
$(document).ready(function() {
	var submitButton = $('[data-installation-submit]');
	var form = $('[data-form]');

	submitButton.addClass('d-none');

	<?php if ($fileInfoEnabled) { ?>
		form.submit();
	<?php } ?>
});
</script>
