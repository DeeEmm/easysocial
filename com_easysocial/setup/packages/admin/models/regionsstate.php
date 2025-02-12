<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelRegionsstate extends EasySocialModel
{
    private $_tbl = '#__social_regions';

    // Currently we don't include city first
    private $types = array(SOCIAL_REGION_TYPE_COUNTRY, SOCIAL_REGION_TYPE_STATE);

    public function __construct($config = array())
    {
        parent::__construct('regionsstate' , $config);
    }

    public function initStates()
    {
        // Direction, search, limit, limitstart, ordering is handled by parent::initStates();
        parent::initStates();

        $currentLayout = JFactory::getApplication()->input->getString('layout');

        if (empty($currentLayout)) {
            $currentLayout = 'country';
        }

        $previousLayout = $this->getUserState('layout');

        if ($currentLayout !== $previousLayout) {
            $this->setUserState('search', '');
        }

        $this->setUserState('layout', $currentLayout);

        // Init other parameters
        $state = $this->getUserStateFromRequest('state', 'all');

        $this->setState('state', $state);
    }

    public function getItems($options = array())
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select($this->_tbl);

        if (isset($options['type'])) {
            $sql->where('type', $options['type']);
        }

        if (isset($options['parent_uid'])) {
            $sql->where('parent_uid', $options['parent_uid']);
        }

        if (isset($options['parent_type'])) {
            $sql->where('parent_type', $options['parent_type']);
        }

        $state = $this->getState('state');
        if ($state !== 'all') {
            $sql->where('state', $state);
        }

        $search = $this->getState('search');
        if (!empty($search)) {
            $sql->where('name', '%' . $search . '%', 'LIKE');
        }

        $sql->order($this->getState('ordering'), $this->getState('direction'));

        $this->setTotal($sql->getTotalSql());

        $result = $this->getData($sql);

        return $this->bindTable('Region', $result);
    }
}
