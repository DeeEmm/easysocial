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
<div class="text-center">

	<?php if (!$unsyncedPrivacyCount) { ?>
		<p class="mb-5"><?php echo JText::_('Congratulations! EasySocial has been successfully installed on your site and you may start using it.');?></p>

		<div id="svg__ani" class="lottie"></div>

		<div class="d-flex justify-content--c mt-5 mb-3">
			<div class="pr-3">
				<a href="<?php echo JURI::root();?>index.php?option=<?php echo SI_IDENTIFIER;?>&view=dashboard" class="btn btn-outline-secondary" target="_blank">
					Launch Frontend
				</a>
			</div>
			<div class="pl-3">
				<a href="<?php echo JURI::root();?>administrator/index.php?option=<?php echo SI_IDENTIFIER;?>" class="btn btn-outline-secondary">
					Launch Backend
				</a>
			</div>
		</div>
	<?php } ?>

	<?php if ($unsyncedPrivacyCount) { ?>
		<p>
			EasySocial has been successfully updated on your site.
			<br /><br /><br /><br />
			There are important changes to improve the performance of EasySocial and <br />
			you will need to run the maintenance script to complete the installation process.
		</p>

		<div class="d-flex justify-content--c mt-5 mb-3">
			<div class="pr-3">
				<a href="/administrator/index.php?option=com_easysocial&view=maintenance&layout=privacy" class="btn btn-primary">
					<b style="color:#FFF;">Run Maintenance Script &rarr;</b>
				</a>
			</div>
		</div>
	<?php } ?>
</div>

<script>
var animationRemote = bodymovin.loadAnimation({
	container: document.getElementById('svg__ani'),
	path: '/administrator/components/<?php echo SI_IDENTIFIER;?>/setup/assets/images/success.json',
	autoplay: true,
	renderer: 'svg',
	loop: false
});
</script>
