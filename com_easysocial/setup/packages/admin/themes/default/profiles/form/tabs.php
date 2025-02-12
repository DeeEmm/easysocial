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
<ul id="userForm" class="nav nav-tabs nav-tabs-icons" data-es-form-tabs>
	<li class="tabItem<?php echo $activeTab == 'settings' ? ' active' : '';?>">
		<a data-es-toggle="tab" href="#settings" data-item="settings">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PROFILE_GENERAL');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'avatars' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#avatars" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="avatars">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_DEFAULT_AVATARS');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'headerapps' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#headerapps" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="headerapps">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_HEADER');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'groups' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#groups" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="groups">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_GROUPS');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'pages' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#pages" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="pages">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PAGES');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'apps' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#apps" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="apps">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_APPS');?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'privacy' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#privacy" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="privacy">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PRIVACY' );?>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'access' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#access" <?php echo !$isNew ? 'data-es-toggle="tab"' : '';?> data-item="access">
			<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_ACCESS' );?>
		</a>
	</li>
</ul>
