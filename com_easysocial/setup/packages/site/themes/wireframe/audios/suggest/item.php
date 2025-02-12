<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<img src="<?php echo $audio->getAlbumArt();?>" alt="<?php echo $this->html('string.escape', $audio->getTitle());?>" width="16" height="16" data-suggest-avatar />&nbsp;<?php echo ESJString::substr($audio->getTitle(), 0, 40) . '..'; ?>

<input type="hidden" value="<?php echo $this->html('string.escape', $audio->getTitle());?>" data-suggest-title />
<input type="hidden" name="<?php echo $inputName;?>" value="<?php echo $audio->id;?>" data-suggest-id />