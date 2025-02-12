<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<img src="<?php echo $group->getAvatar();?>" width="16" height="16" /> <?php echo $group->getName(); ?>

<input type="hidden" value="<?php echo $this->html('string.escape', $group->getName());?>" data-suggest-title />
<input type="hidden" name="group_ids[]" value="<?php echo $group->id;?>" data-suggest-id />