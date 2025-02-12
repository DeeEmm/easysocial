<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.utilities.date');

class SocialDateHelper extends JDate
{
    public function __construct($date = 'now', $withoffset = true)
    {
        parent::__construct($date);

        $offset = null;

        if ($withoffset) {
            $offset = $this->getOffSetObject();
            $this->setTimeZone($offset);
        }

        // incase we need to add the dst, we can do it here.
        // $this->modify('-1 hours');
    }

    public function getOffSetObject()
    {
        jimport('joomla.form.formfield');

        $user = JFactory::getUser();
        $jConfig = JFactory::getConfig();

        // temporary ignore the dst in joomla 1.6
        if ($user->id != 0) {
            $userTZ = $user->getParam('timezone');
        }

        if (empty($userTZ)) {
            $userTZ = $jConfig->get('offset');
        }

        $newTZ = new DateTimeZone($userTZ);

        return $newTZ;
    }

    /**
     * Compatibility framework.
     *
     * @since   1.0
     * @access  public
     *
     */
    public function toFormat($format = '%Y-%m-%d %H:%M:%S' , $local = false, $translate = true)
    {
        return $this->format($format , $local , $translate);
    }

    /**
     * Compatibility framework.
     *
     * @since   1.0
     * @access  public
     *
     */
    public function toMySQL($local = false)
    {
        return $this->toSQL($local);
    }
}
