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
<div class="popbox-content__bd">
	<div class="o-media o-media--rev">
		<div class="o-media__image">
			<?php echo $this->html('avatar.page', $page, 'md', false); ?>
		</div>
		<div class="o-media__body">
			<div class="o-title t-text--truncate">
				<?php echo $this->html('html.cluster', $page, false); ?>
			</div>
		</div>
	</div>
</div>

<div class="popbox-content__ft">
	<div class="o-media o-media--top">
		<div class="o-media__image t-lg-pr--md">
			<i class="fa fa-lock t-lg-mt--sm"></i>
		</div>
		<div class="o-media__body t-text--left">
			<?php echo JText::_('COM_EASYSOCIAL_PAGES_PRIVATE_PAGE_INFO');?>
		</div>
	</div>
</div>
