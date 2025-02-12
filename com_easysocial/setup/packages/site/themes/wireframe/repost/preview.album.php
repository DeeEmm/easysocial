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
<div class="stream-media-preview-body pl-10 mt-10 mb-20">
	<div class="stream-meta">
		<div class="stream-content">
			<p>
				<img alt="<?php echo $this->html('string.escape', $album->getCoverObject()->get('title'));?>" src="<?php echo $album->getCover('square'); ?>" class="mr-10 mb-10 pull-left" />
				<?php echo $album->get('caption'); ?>
			</p>
		</div>
	</div>
</div>
