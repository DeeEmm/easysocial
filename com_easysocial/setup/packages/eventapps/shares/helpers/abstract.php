<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEventSharesHelper
{
    protected $item = null;
    protected $share = null;

    public function __construct(SocialStreamItem &$item, $share)
    {
        $this->item = $item;
        $this->share = $share;
    }

    public function formatContent($content)
    {
        if (empty($this->item->tags)) {
            return $content;
        }

        $content = ES::string()->processTags($this->item->tags, $content);

        return $content;
    }
}
