<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/views/views');

class EasySocialViewRegions extends EasySocialAdminView
{
    public function display($tpl = null)
    {
        // Check access
        if (!$this->authorise('easysocial.access.regions')) {
            $this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
        }

        $this->setHeading('COM_EASYSOCIAL_REGIONS_COUNTRIES_TITLE');
        $this->setDescription('COM_EASYSOCIAL_REGIONS_COUNTRIES_DESCRIPTION');

        JToolbarHelper::addNew('form', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
        JToolbarHelper::publishList('publish');
        JToolbarHelper::unpublishList('unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));
        JToolbarHelper::divider();
        JToolbarHelper::custom('init', 'refresh', '', JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_DATABASE'), false);

        $model = ES::model('Regions', array('initState' => true, 'namespace' => 'regions.listing'));

        $regions = $model->getItems(array('type' => SOCIAL_REGION_TYPE_COUNTRY));

        $pagination = $model->getPagination();

        $this->set('regions', $regions);
        $this->set('pagination', $pagination);

        $search = $model->getState('search');
        $ordering = $model->getState('ordering');
        $direction = $model->getState('direction');
        $state = $model->getState('state');
        $type = $model->getState('type');
        $limit = $model->getState('limit');

        $this->set('search', $search);
        $this->set('ordering', $ordering);
        $this->set('direction', $direction);
        $this->set('state', $state);
        $this->set('type', $type);
        $this->set('limit', $limit);

        $this->set('layout', '');

        // Mark this as true so that country can link to states page
        $this->set('childType', SOCIAL_REGION_TYPE_STATE);

        $this->set('showOrdering', true);

        echo parent::display('admin/regions/default/default');
    }

    public function state($tpl = null)
    {
        // Check access
        if (!$this->authorise('easysocial.access.regions')) {
            $this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
        }

        $parentuid = $this->input->get('parent', 0, 'int');

        $parent = ES::table('Region');
        $result = $parent->load(array('uid' => $parentuid, 'type' => SOCIAL_REGION_TYPE_COUNTRY));

        if (!$result) {
            $this->setHeading('COM_EASYSOCIAL_REGIONS_STATES_TITLE');
            $this->setDescription('COM_EASYSOCIAL_REGIONS_STATES_DESCRIPTION');
        } else {
            $this->setHeading(JText::sprintf('COM_EASYSOCIAL_REGIONS_STATES_OF_COUNTRY_TITLE', $parent->name));
            $this->setDescription(JText::sprintf('COM_EASYSOCIAL_REGIONS_STATES_OF_COUNTRY_DESCRIPTION', $parent->name));
        }

        JToolbarHelper::addNew('form', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
        JToolbarHelper::publishList('publish');
        JToolbarHelper::unpublishList('unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));
        JToolbarHelper::divider();
        JToolbarHelper::custom('init', 'refresh', '', JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_DATABASE'), false);

        $model = ES::model('Regionsstate', array('initState' => true, 'namespace' => 'states.listings'));

        $itemOptions = array('type' => SOCIAL_REGION_TYPE_STATE);

        if ($result && $parent->uid) {
            $itemOptions['parent_uid'] = $parent->uid;
            $itemOptions['parent_type'] = SOCIAL_REGION_TYPE_COUNTRY;
        }

        $regions = $model->getItems($itemOptions);

        $pagination = $model->getPagination();

        $this->set('regions', $regions);
        $this->set('parent', $parent);
        $this->set('pagination', $pagination);

        $search = $model->getState('search');
        $ordering = $model->getState('ordering');
        $direction = $model->getState('direction');
        $state = $model->getState('state');
        $type = $model->getState('type');
        $limit = $model->getState('limit');

        $this->set('search', $search);
        $this->set('ordering', $ordering);
        $this->set('direction', $direction);
        $this->set('state', $state);
        $this->set('type', $type);
        $this->set('limit', $limit);

        $this->set('layout', $this->input->get('layout', '', 'string'));

        // Mark this as false for now. In the future when cities are ready then mark this as true
        $this->set('childType', false);

        $this->set('showOrdering', !empty($parent->id));

        echo parent::display('admin/regions/default/default');
    }

    public function init()
    {
        $this->setHeading('COM_EASYSOCIAL_REGIONS_INITIALISE_TITLE');
        $this->setDescription('COM_EASYSOCIAL_REGIONS_INITIALISE_DESCRIPTION');

        echo parent::display('admin/regions/init');
    }

    /**
     * Renders the form for the region and state
     *
     * @since   2.0
     * @access  public
     */
    public function form()
    {
        // Check access
        if (!$this->authorise('easysocial.access.regions')) {
            $this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
        }

        $id = $this->input->get('id', 0, 'int');

        $region = ES::table('Region');
        $region->load($id);

        $this->setHeading('COM_EASYSOCIAL_REGIONS_NEW_REGION_TITLE');

        if ($region->id) {
            $this->setHeading($region->name, JText::sprintf('COM_EASYSOCIAL_REGIONS_EDIT_REGION_DESCRIPTION', $region->name));
        }

        JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
        JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
        JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));
        JToolbarHelper::divider();
        JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

        if (!empty($region->parent_type)) {
            $parents = ES::model('Regions')->getRegions(array('type' => $region->parent_type));

            $this->set('parents', $parents);
        }

        $this->set('isNew', empty($region->id));

        $this->set('region', $region);

        echo parent::display('admin/regions/form/default');
    }

    public function delete()
    {
        ES::info()->set($this->getMessage());

        return $this->redirect(FRoute::url(array('view' => 'regions')));
    }

    public function togglePublish()
    {
        ES::info()->set($this->getMessage());

        return $this->redirect(FRoute::url(array('view' => 'regions')));
    }

    public function store($task, $region)
    {
        ES::info()->set($this->getMessage());

        if ($task === 'apply') {
            return $this->redirect(FRoute::url(array('view' => 'regions', 'layout' => 'form', 'id' => $region->id)));
        }

        if ($task === 'savenew') {
            return $this->redirect(FRoute::url(array('view' => 'regions', 'layout' => 'form')));
        }

        return $this->redirect(FRoute::url(array('view' => 'regions')));
    }

    public function move()
    {
        ES::info()->set($this->getMessage());

        $parent = $this->input->getInt('parent');
        $layout = $this->input->getString('layout');

        $url = array('view' => 'regions');

        if (!empty($layout)) {
            $url['layout'] = $layout;
        }

        if (!empty($parent)) {
            $url['parent'] = $parent;
        }

        return $this->redirect(FRoute::url($url));
    }

    public function export()
    {
        ES::model('regions')->export();
    }
}
