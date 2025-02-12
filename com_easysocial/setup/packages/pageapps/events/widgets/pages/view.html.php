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

class EventsWidgetsPages extends SocialAppsWidgets
{
    public function sidebarBottom($pageId, SocialPage $page)
    {
        $params = $this->getParams();

        if (!$params->get('widget', true)) {
            return;
        }

        $page = ES::page($pageId);

        if (!$page->getAccess()->get('events.pageevent', true)) {
            return;
        }

        $my = ES::user();

        $days = $params->get('widget_days', 14);
        $total = $params->get('widget_total', 5);

        $date = ES::date();

        $now = $date->toSql();

        $future = ES::date($date->toUnix() + ($days * 24*60*60))->toSql();

        $options = array();

        $options['start-after'] = $now;
        $options['start-before'] = $future;
        $options['limit'] = $total;
        $options['state'] = SOCIAL_STATE_PUBLISHED;
        $options['ordering'] = 'start';
        $options['page_id'] = $pageId;

        $events = ES::model('Events')->getEvents($options);

        if (empty($events)) {
            return;
        }

		$this->set('events', $events);
		$this->set('app', $this->app);

        echo parent::display('widgets/upcoming');
    }

    public function pageAdminStart($page)
    {
        $my = ES::user();
        $config = ES::config();

        if (!$config->get('events.enabled') || !$my->getAccess()->get('events.create')) {
            return;
        }

        if (!$page->canCreateEvent() || !$page->getCategory()->getAcl()->get('events.pageevent')) {
            return;
        }

        $theme = ES::themes();
        $theme->set('page', $page);
        $theme->set('app', $this->app);

        echo $theme->output('themes:/apps/page/events/widgets/widget.menu');
    }
}
