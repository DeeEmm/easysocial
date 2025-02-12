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
<div class="panel-body t-hidden" data-version-widget="updated">
	<div class="l-stack t-text--center">
		<div>
			<div>
				<i class="fa fa-check-circle t-text--success"></i>&nbsp; You are running the latest version of EasySocial
			</div>
		</div>

		<div>
			<b>Installed version: <?php echo $installedVersion;?></b>
		</div>
	</div>
</div>

<div class="panel-body t-hidden" data-version-widget="outdated">
	<div class="l-stack t-text--center">
		<div>
			<div>
				<i class="fa fa-exclamation-circle t-text--danger"></i>&nbsp; You are running on an outdated version of EasySocial
			</div>
		</div>

		<div>
			Installed version: <b><?php echo $installedVersion;?></b>
		</div>

		<div class="t-text--success">
			New version available: <a href="https://stackideas.com/changelog/easysocial" target="_blank" class="t-text--success" style="text-decoration: underline;"><b><span data-latest-version></span></b></a>
		</div>

		<a href="<?php echo JRoute::_('index.php?option=com_easysocial&controller=system&task=upgrade');?>" class="btn btn-es-primary">
			<i class="fa fa-bolt"></i>&nbsp; Update EasySocial
		</a>
	</div>
</div>

<input type="hidden" value="<?php echo $installedVersion;?>" data-es-version />
