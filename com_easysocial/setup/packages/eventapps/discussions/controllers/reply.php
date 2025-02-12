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

class DiscussionsControllerReply extends SocialAppsController
{
    /**
     * Accepts a reply as an answer
     *
     * @since    1.2
     * @access    public
     * @param    string
     * @return
     */
    public function accept()
    {
        // Check for request forgeriess
        ES::checkToken();

        // Ensure that the user is logged in.
        ES::requireLogin();

        // Get the reply object
        $id = $this->input->get('id', 0, 'int');
        $reply = ES::table('Discussion');
        $reply->load($id);

        // Load the discussion
        $discussion = ES::table('Discussion');
        $discussion->load($reply->parent_id);

        // Get the event
        $event = ES::event($reply->uid);

        // Check if the viewer can accept this reply as an answer.
        if (!$event->getGuest()->isAdmin() && $this->my->id != $discussion->created_by && !$this->my->isSiteAdmin()) {
            return $ajax->reject();
        }

        // Set this discussion as answered
        $discussion->setAnswered($reply);

        // @points: events.discussion.answer
        // Earn points when a reply is marked as an answer
        $points = ES::points();
        $points->assign('events.discussion.answer', 'com_easysocial', $reply->created_by);

        // Synchronize the items
        $discussion->sync();

        // Create a new stream item for this discussion
        $stream = ES::stream();

        // Get the stream template
        $tpl = $stream->getTemplate();

        // Someone just joined the event
        $tpl->setActor($this->my->id, SOCIAL_TYPE_USER);

        // Set the context
        $tpl->setContext($discussion->id, 'discussions');

        // Set the cluster
        $tpl->setCluster($event->id, SOCIAL_TYPE_EVENT);

        // Set the verb
        $tpl->setVerb('answered');

        // Set the params to cache the event data
        $registry = ES::registry();
        $registry->set('event', $event);
        $registry->set('reply', $reply);
        $registry->set('discussion', $discussion);

        $tpl->setParams($registry);

        // Add the stream
        $stream->add($tpl);

        // Send notification to accepted answer user only if the reply is not the user who accepts it
        if ($this->my->id != $reply->created_by)
        $options = array();
        $options['permalink'] = FRoute::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $this->getApp()->getAlias(), 'discussionId' => $discussion->id, 'external' => true), false);
        $options['title'] = $discussion->title;
        $options['content'] = $reply->getContent();
        $options['discussionId'] = $reply->id;
        $options['userId'] = $reply->created_by;
        $options['targets'] = array($reply->created_by);

        $event->notifyMembers('discussion.answered', $options);

        return $this->ajax->resolve($discussion);
    }

    /**
     * Displays the delete confirmation dialog
     *
     * @since    1.2
     * @access    public
     * @return
     */
    public function delete()
    {
        // Check for request forgeriess
        ES::checkToken();

        // Ensure that the user is logged in.
        ES::requireLogin();

        // Load up ajax lib
        $ajax = ES::ajax();

        // Get the reply object
        $id = $this->input->get('id', 0, 'int');
        $reply = ES::table('Discussion');
        $reply->load($id);

        // Load the discussion
        $discussion = ES::table('Discussion');
        $discussion->load($reply->parent_id);

        // Get the event object
        $event = ES::event($reply->uid);

        // Check if the viewer can really delete the reply.
        if (!$event->getGuest()->isAdmin() && !$this->my->isSiteAdmin()) {
            return $ajax->reject();
        }

        $state     = $reply->delete();

        // Synchronize the items
        $discussion->sync();

        return $ajax->resolve($discussion);
    }

    /**
     * Displays the delete confirmation dialog
     *
     * @since    1.2
     * @access    public
     * @return
     */
    public function confirmDelete()
    {
        // Check for request forgeriess
        ES::checkToken();

        // Ensure that the user is logged in.
        ES::requireLogin();

        // Load up ajax lib
        $ajax = ES::ajax();

        // Get the discussion
        $id = $this->input->get('id', 0, 'int');
        $reply = ES::table('Discussion');
        $reply->load($id);

        // Get the event
        $event = ES::event($reply->uid);

        // Get the current logged in user.
        $user = ES::user();

        $theme = ES::themes();
        $output = $theme->output('apps/event/discussions/canvas/dialog.delete');

        return $ajax->resolve($output);
    }

    /**
     * Submits a reply
     *
     * @since    1.2
     * @access    public
     * @return
     */
    public function submit()
    {
        // Check for request forgeriess
        ES::checkToken();

        // Ensure that the user is logged in.
        ES::requireLogin();

        // Load up ajax lib
        $ajax = ES::ajax();

        // Get the event
        $eventId = $this->input->get('eventId', 0, 'int');
        $event = ES::event($eventId);

        // Get the discussion
        $id = $this->input->get('id', 0, 'int');
        $discussion = ES::table('Discussion');
        $discussion->load($id);

        // Check whether the viewer can really reply to the discussion
        if (!$event->getGuest()->isGuest()) {
            $obj = new stdClass();
            $obj->message = JText::_('APP_EVENT_DISCUSSIONS_DISCUSSION_IS_AVAILABLE_TO_GUESTS_ONLY');
            $obj->type = SOCIAL_MSG_ERROR;
            return $ajax->reject($obj);
        }

        // Test for locked discussion.
        if ($discussion->lock && !$event->isAdmin()) {
            $obj = new stdClass();
            $obj->message = JText::_('APP_EVENT_DISCUSSIONS_DISCUSSION_IS_LOCKED');
            $obj->type = SOCIAL_MSG_ERROR;

            return $ajax->reject($obj);
        }


        // Get the content
        $content = $this->input->get('content', '', 'default');

        if (empty($content)) {
            $obj = new stdClass();
            $obj->message = JText::_('APP_EVENT_DISCUSSIONS_EMPTY_REPLY_ERROR');
            $obj->type = SOCIAL_MSG_ERROR;

            return $ajax->reject($obj);
        }

        $reply = ES::table('Discussion');
        $reply->uid = $discussion->uid;
        $reply->type = $discussion->type;
        $reply->content = $content;
        $reply->created_by = $this->my->id;
        $reply->parent_id = $discussion->id;
        $reply->state = SOCIAL_STATE_PUBLISHED;

        // Save the reply.
        $reply->store();

        if (!$id) {
            // @points: events.discussion.reply
            // Earn points when posting a reply
            $points = ES::points();
            $points->assign('events.discussion.reply', 'com_easysocial', $reply->created_by);
        }

        // Create a new stream item for this discussion
        $stream = ES::stream();

        // Get the stream template
        $tpl = $stream->getTemplate();

        // Someone just joined the event
        $tpl->setActor($this->my->id, SOCIAL_TYPE_USER);

        // Set the context
        $tpl->setContext($discussion->id, 'discussions');

        // Set the cluster
        $tpl->setCluster($event->id, SOCIAL_TYPE_EVENT);

        // Set the verb
        $tpl->setVerb('reply');

        // Set the params to cache the event data
        $registry = ES::registry();
        $registry->set('event', $event);
        $registry->set('reply', $reply);
        $registry->set('discussion', $discussion);

        $tpl->setParams($registry);

        // Add the stream
        $stream->add($tpl);

        // Update the parent's reply counter.
        $discussion->sync($reply);

        // Before we populate the output, we need to format it according to the theme's specs.
        $reply->author = $this->my;

        // Load the contents
        $theme = ES::themes();

        // Since this reply is new, we don't have an answer for this item.
        $answer = false;

        $theme->set('question', $discussion);
        $theme->set('event', $event);
        $theme->set('answer', $answer);
        $theme->set('reply', $reply);

        $access = $event->getAccess();
        $files = $access->get('files.enabled', true);

        $theme->set('files', $files);

        $contents = $theme->output('apps/event/discussions/canvas/item.reply');

        // Send notification to event members
        $options = array();
        $options['permalink'] = FRoute::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $this->getApp()->getAlias(), 'discussionId' => $discussion->id, 'external' => true), false);
        $options['title'] = $discussion->title;
        $options['content'] = $reply->getContent();
        $options['discussionId'] = $reply->id;
        $options['userId'] = $reply->created_by;
        $options['targets'] = $discussion->getParticipants(array($reply->created_by));

        $event->notifyMembers('discussion.reply', $options);

        return $ajax->resolve($contents);
    }

    /**
     * Allows caller to update a reply
     *
     * @since    1.2
     * @access    public
     * @return
     */
    public function update()
    {
        // Check for request forgeriess
        ES::checkToken();

        // Ensure that the user is logged in.
        ES::requireLogin();

        // Load up ajax lib
        $ajax = ES::ajax();

        // Get the discussion
        $id = $this->input->get('id', 0, 'int');
        $reply = ES::table('Discussion');
        $reply->load($id);

        // Get the event
        $eventId = $this->input->get('eventId', 0, 'int');
        $event = ES::event($eventId);

        // Get the discussion
        $discussion = ES::table('Discussion');
        $discussion->load($reply->parent_id);

        // Check whether the viewer can really reply to the discussion
        if (!$event->getGuest()->isGuest()) {
            return $this->reject();
        }

        // Get the content
        $content = $this->input->get('content', '', 'default');

        if (empty($content)) {
            $obj = new stdClass();
            $obj->message = JText::_('APP_EVENT_DISCUSSIONS_EMPTY_REPLY_ERROR');
            $obj->type = SOCIAL_MSG_ERROR;

            return $ajax->reject($obj);
        }

        // Update the content
        $reply->content = $content;

        // Save the reply.
        $reply->store();

        // Update the parent's reply counter.
        $discussion->sync($reply);

        return $ajax->resolve($reply->getContent());
    }

}
