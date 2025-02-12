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
<form id="adminForm" name="adminForm" action="index.php" method="post" enctype="multipart/form-data" data-profile-form>
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">
				<?php echo $this->loadTemplate('admin/profiles/form/tabs' , array('isNew' => $profile->id == 0 , 'activeTab' => $activeTab)); ?>

				<div class="tab-content">
					<div id="settings" class="tab-pane<?php echo $activeTab == 'settings' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/settings'); ?>
					</div>

					<?php if ($profile->id) { ?>
					<div id="avatars" class="tab-pane<?php echo $activeTab == 'avatars' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/avatars'); ?>
					</div>

					<div id="headerapps" class="tab-pane<?php echo $activeTab == 'headerapps' ? ' active in' : '';?>">
						<?php echo $this->html('form.headerApps', 'params[header_apps]', $profile->getParams()->get('header_apps'), 'user'); ?>
					</div>

					<div id="groups" class="tab-pane<?php echo $activeTab == 'groups' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/groups/default'); ?>
					</div>

					<div id="pages" class="tab-pane<?php echo $activeTab == 'pages' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/pages/default'); ?>
					</div>

					<div id="apps" class="tab-pane<?php echo $activeTab == 'apps' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/apps/default'); ?>
					</div>

					<div id="privacy" class="tab-pane<?php echo $activeTab == 'privacy' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/privacy/default'); ?>
					</div>

					<div id="access" class="tab-pane<?php echo $activeTab == 'access' ? ' active in' : '';?>">
						<?php echo $this->includeTemplate('admin/profiles/form/access'); ?>
					</div>
					<?php } ?>

				</div>

			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'profiles', 'store');?>
	<input type="hidden" name="activeTab" data-tab-active value="<?php echo $activeTab; ?>" />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="id" value="<?php echo $profile->id; ?>" />
	<input type="hidden" name="cid" value="" />
</form>
