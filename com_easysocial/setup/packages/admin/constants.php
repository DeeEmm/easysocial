<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

define('SOCIAL_JOOMLA', JPATH_ROOT);
define('SOCIAL_JOOMLA_URI', rtrim(JURI::root(), '/'));
define('SOCIAL_JOOMLA_ADMIN', SOCIAL_JOOMLA . '/administrator');
define('SOCIAL_JOOMLA_ADMIN_URI', SOCIAL_JOOMLA_URI . '/administrator');
define('SOCIAL_JOOMLA_SITE_TEMPLATES', SOCIAL_JOOMLA . '/templates');
define('SOCIAL_JOOMLA_SITE_TEMPLATES_URI', SOCIAL_JOOMLA_URI . '/templates');
define('SOCIAL_JOOMLA_ADMIN_TEMPLATES', SOCIAL_JOOMLA_ADMIN . '/templates');
define('SOCIAL_JOOMLA_ADMIN_TEMPLATES_URI', SOCIAL_JOOMLA_ADMIN_URI . '/templates');
define('SOCIAL_JOOMLA_MODULES', SOCIAL_JOOMLA . '/modules');
define('SOCIAL_JOOMLA_MODULES_URI', SOCIAL_JOOMLA_URI . '/modules');
define('SOCIAL_OVERRIDE_GLOBAL', JPATH_ROOT . '/templates/stackideas/easysocial');

// Component
define('SOCIAL_COMPONENT_NAME', 'com_easysocial');
define('SOCIAL_ROOT', JPATH_ROOT . '/components/com_easysocial' );
define('SOCIAL_SITE', SOCIAL_JOOMLA . '/components/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_SITE_URI', SOCIAL_JOOMLA_URI . '/components/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_ADMIN', SOCIAL_JOOMLA_ADMIN . '/components/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_ADMIN_DEFAULTS', SOCIAL_JOOMLA_ADMIN . '/components/com_easysocial/defaults');
define('SOCIAL_ADMIN_UPDATES', SOCIAL_ADMIN . '/updates');
define('SOCIAL_ADMIN_QUERIES', SOCIAL_ADMIN . '/queries' );
define('SOCIAL_ADMIN_URI', SOCIAL_JOOMLA_ADMIN_URI . '/components/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_MEDIA', SOCIAL_JOOMLA . '/media/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_MEDIA_URI', SOCIAL_JOOMLA_URI . '/media/' . SOCIAL_COMPONENT_NAME);
define('SOCIAL_SCRIPTS', SOCIAL_MEDIA . '/scripts');
define('SOCIAL_SCRIPTS_URI', SOCIAL_MEDIA_URI . '/scripts');
define('SOCIAL_RESOURCES', SOCIAL_MEDIA . '/resources');
define('SOCIAL_RESOURCES_URI', SOCIAL_MEDIA_URI . '/resources');
define('SOCIAL_APPS', SOCIAL_MEDIA  . '/apps');
define('SOCIAL_APPS_URI', SOCIAL_MEDIA_URI . '/apps');
define('SOCIAL_TMP', SOCIAL_MEDIA . '/tmp');
define('SOCIAL_TMP_URI', SOCIAL_MEDIA_URI . '/tmp');
define('SOCIAL_LIB', SOCIAL_ADMIN . '/includes');
define('SOCIAL_MODELS', SOCIAL_ADMIN . '/models');
define('SOCIAL_MANIFESTS', SOCIAL_ADMIN . '/manifests');
define('SOCIAL_TABLES', SOCIAL_ADMIN . '/tables');
define('SOCIAL_FIELDS', SOCIAL_APPS . '/fields');
define('SOCIAL_PROFILETYPES', SOCIAL_MEDIA . '/avatars/profiletypes');
define('SOCIAL_COMPOSER_AUTOLOAD', SOCIAL_LIB . '/vendor/autoload.php');

// ES File cache path
define('SOCIAL_FILE_CACHE_DIR', SOCIAL_MEDIA . '/cache');
define('SOCIAL_FILE_CACHE_FILENAME', 'easysocial.cache');

// ES CSV import file tmp path
define('SOCIAL_IMPORT_CSV_DIR', SOCIAL_MEDIA . '/files/import/csv');

// Default configuration path
define('SOCIAL_CONFIG_DEFAULTS', SOCIAL_ADMIN . '/defaults');

// Themes
define('SOCIAL_SITE_THEMES', SOCIAL_SITE . '/themes');
define('SOCIAL_SITE_THEMES_URI', SOCIAL_SITE_URI . '/themes');
define('SOCIAL_ADMIN_THEMES', SOCIAL_ADMIN . '/themes');
define('SOCIAL_ADMIN_THEMES_URI', SOCIAL_ADMIN_URI . '/themes');

// GDPR Download Folder
define('SOCIAL_GDPR_DOWNLOADS', SOCIAL_MEDIA . '/downloads');


// Avatar sizes
define('SOCIAL_AVATAR_SMALL', 'small');
define('SOCIAL_AVATAR_MEDIUM', 'medium');
define('SOCIAL_AVATAR_LARGE', 'large');
define('SOCIAL_AVATAR_SQUARE', 'square');

// Avatar dimensions
define('SOCIAL_AVATAR_SMALL_WIDTH', 32);
define('SOCIAL_AVATAR_SMALL_HEIGHT', 32);
define('SOCIAL_AVATAR_MEDIUM_WIDTH', 64);
define('SOCIAL_AVATAR_MEDIUM_HEIGHT', 64);
define('SOCIAL_AVATAR_LARGE_WIDTH', 200);
define('SOCIAL_AVATAR_SQUARE_LARGE_WIDTH', 200);
define('SOCIAL_AVATAR_SQUARE_LARGE_HEIGHT', 200);

// Cover sizes
define('SOCIAL_COVER_SMALL', 'small');
define('SOCIAL_COVER_LARGE', 'large');
define('SOCIAL_COVER_DEFAULT', 'default');

// Cover dimensions
define('SOCIAL_COVER_SMALL_WIDTH', 320);
define('SOCIAL_COVER_SMALL_HEIGHT', 120);
define('SOCIAL_COVER_LARGE_WIDTH', 960);
define('SOCIAL_COVER_LARGE_HEIGHT', 360);

// video watermark dimensions
define('SOCIAL_VIDEO_WATERMARK_WIDTH', 160);
define('SOCIAL_VIDEO_WATERMARK_HEIGHT', 160);

// Location types
define('SOCIAL_LOCATION_SITE', 'site');
define('SOCIAL_LOCATION_ADMIN', 'admin');

// Default storage location for default images.
define('SOCIAL_DEFAULTS_URI', SOCIAL_JOOMLA_URI . '/media/com_easysocial/images/defaults');

// Error codes
define('ERROR_INSTALLER_XML', 100);

// Application types
define('SOCIAL_APPS_TYPE_APPS', 'apps');
define('SOCIAL_APPS_TYPE_FIELDS', 'fields');
define('SOCIAL_APPS_VIEW_TYPE_EMBED', 'embed');
define('SOCIAL_APPS_VIEW_TYPE_CANVAS', 'canvas');
define('SOCIAL_APPS_VIEW_TYPE_ALERTS', 'alerts');

// Application icons
define('SOCIAL_APPS_ICON_LARGE', 'large');
define('SOCIAL_APPS_ICON_SMALL', 'small');

// Application groups
define('SOCIAL_APPS_GROUP_USER', 'user');
define('SOCIAL_APPS_GROUP_GROUP', 'group');
define('SOCIAL_APPS_GROUP_EVENT', 'event');
define('SOCIAL_APPS_GROUP_PAGE', 'page');

// States
define('SOCIAL_STATE_TRASHED', -1);
define('SOCIAL_STATE_DEFAULT', 2);
define('SOCIAL_STATE_PUBLISHED', 1);
define('SOCIAL_STATE_UNPUBLISHED', 0);
define('SOCIAL_STATE_SCHEDULED', 4);
define('SOCIAL_APP_STATE_DISCOVERED', -1);

// Review States
define('SOCIAL_REVIEW_STATE_PUBLISHED', 1);
define('SOCIAL_REVIEW_STATE_UNPUBLISHED', 0);
define('SOCIAL_REVIEW_STATE_PENDING', 2);

// User states
define('SOCIAL_JOOMLA_USER_UNBLOCKED', 0);
define('SOCIAL_JOOMLA_USER_BLOCKED', 1);
define('SOCIAL_USER_STATE_DISABLED', 0);
define('SOCIAL_USER_STATE_ENABLED', 1);
define('SOCIAL_USER_STATE_ACTIVATION', 2);
define('SOCIAL_USER_STATE_PENDING', 3);

// This user state mean user haven't confirm their email account yet
define('SOCIAL_USER_STATE_CONFIRMATION', 4);

// Skeleton file
define('SOCIAL_SKELETON_INDEX', SOCIAL_MEDIA . '/index.html');

// Posted data
define('SOCIAL_POSTED_DATA', true);

// Mailer priorities
define('SOCIAL_MAILER_PRIORITY_IMMEDIATE', 5);
define('SOCIAL_MAILER_PRIORITY_HIGH', 3);
define('SOCIAL_MAILER_PRIORITY_NORMAL', 2);
define('SOCIAL_MAILER_PRIORITY_LOW', 1);

// Mailer sent status
define('SOCIAL_MAILER_SENT', true);
define('SOCIAL_MAILER_PENDING', 0);
define('SOCIAL_MAILER_NO_TEMPLATE', null);

// ACL Types
define('SOCIAL_ACCESS_BOOLEAN', 'boolean');
define('SOCIAL_ACCESS_LIMIT', 'limits');
define('SOCIAL_ACCESS_HEADER', 'header');

// Session namespaces
define('SOCIAL_SESSION_NAMESPACE', 'com_easysocial');

// State for translating registration type
define('SOCIAL_TRANSLATE_REGISTRATION', true);

// Color states for info messages
define('SOCIAL_MSG_SUCCESS', 'success');
define('SOCIAL_MSG_WARNING', 'warning');
define('SOCIAL_MSG_ERROR', 'error');
define('ES_ERROR', 'error');
define('SOCIAL_MSG_INFO', 'info');

// The initial step number
define('SOCIAL_REGISTER_SELECTPROFILE_STEP', 0);
define('SOCIAL_REGISTER_COMPLETED_STEP', 'completed');

// States for registration types
define('SOCIAL_REGISTER_AUTO', 1);
define('SOCIAL_REGISTER_LOGIN', 1);
define('SOCIAL_REGISTER_VERIFY', 2);
define('SOCIAL_REGISTER_APPROVALS', 3);
// This state for user confirmation and admin approval
define('SOCIAL_REGISTER_CONFIRMATION_APPROVAL', 4);


define('SOCIAL_REGISTER_AUTO_TEXT', 'auto');
define('SOCIAL_REGISTER_VERIFY_TEXT', 'verify');
define('SOCIAL_REGISTER_APPROVAL_TEXT', 'approval');
define('SOCIAL_REGISTER_CONFIRMATION_APPROVAL_TEXT', 'confirmation_approval');

// Conversation types
define('SOCIAL_CONVERSATION_SINGLE', 1);
define('SOCIAL_CONVERSATION_MULTIPLE', 2);
define('SOCIAL_CONVERSATION_MARKETPLACE', 3);

// Conversation states
define('SOCIAL_CONVERSATION_READ', 1);
define('SOCIAL_CONVERSATION_UNREAD', 0);
define('SOCIAL_CONVERSATION_STATE_ARCHIVED', 0);
define('SOCIAL_CONVERSATION_STATE_PUBLISHED', 1);
define('SOCIAL_CONVERSATION_STATE_LEFT', 0);
define('SOCIAL_CONVERSATION_STATE_PARTICIPATING', 1);
define('SOCIAL_CONVERSATION_TYPE_LEAVE', 'leave');
define('SOCIAL_CONVERSATION_TYPE_JOIN', 'join');
define('SOCIAL_CONVERSATION_TYPE_DELETE', 'delete');
define('SOCIAL_CONVERSATION_TYPE_MESSAGE', 'message');
define('SOCIAL_CONVERSATION_TYPE_GIPHY', 'giphy');
define('SOCIAL_CONVERSATION_TYPE_CREATE', 'create');
define('SOCIAL_CONVERSATION_ATTACHMENTS_PUBLISHED', 1);

// Calendar/Date
define('SOCIAL_DATE_YEAR_LIST_LIMIT', 100);

// Custom field
define('SOCIAL_CUSTOM_FIELD_PREFIX', 'es-fields');

// Friends
define('SOCIAL_FRIENDS_STATE_PENDING', -1);
define('SOCIAL_FRIENDS_STATE_FRIENDS', 1);
define('SOCIAL_FRIENDS_STATE_REJECTED', 2);
define('SOCIAL_FRIENDS_SEARCH_NAME', 'name');
define('SOCIAL_FRIENDS_SEARCH_REALNAME', 'realname');
define('SOCIAL_FRIENDS_SEARCH_USERNAME', 'username');
define('SOCIAL_FRIENDS_SUGGEST_MAXLIMIT', 50);


// Friends list
define('SOCIAL_FRIENDS_LIST_PUBLISHED', 1);
define('SOCIAL_FRIENDS_LIST_PUBLISHED_CORE', 2);

// Node types
define('SOCIAL_TYPE_USER', 'user');
define('SOCIAL_TYPE_FIELD', 'field');
define('SOCIAL_TYPE_USERGROUP', 'usergroup');
define('SOCIAL_TYPE_PROFILES', 'profiles');
define('SOCIAL_TYPE_GROUP', 'group');
define('SOCIAL_TYPE_GROUPS', 'groups');
define('SOCIAL_TYPE_PAGE', 'page');
define('SOCIAL_TYPE_PAGES', 'pages');
define('SOCIAL_TYPE_CLUSTERS', 'clusters');
define('SOCIAL_TYPE_STREAM', 'stream');
define('SOCIAL_TYPE_LIKES', 'likes');
define('SOCIAL_TYPE_COMMENTS', 'comments');
define('SOCIAL_TYPE_CONVERSATIONS', 'conversations');
define('SOCIAL_TYPE_FRIEND', 'friends');
define('SOCIAL_TYPE_LISTS', 'lists');
define('SOCIAL_TYPE_REGISTRATIONS', 'registrations');
define('SOCIAL_TYPE_FOLLOWERS', 'followers');
define('SOCIAL_TYPE_STORY', 'story');
define('SOCIAL_TYPE_LINKS', 'links');
define('SOCIAL_TYPE_ALBUM', 'albums');
define('SOCIAL_TYPE_PHOTO', 'photos');
define('SOCIAL_TYPE_BADGES', 'badges');
define('SOCIAL_TYPE_SHARE', 'shares');
define('SOCIAL_TYPE_AVATAR', 'avatar');
define('SOCIAL_TYPE_FACEBOOK', 'facebook');
define('SOCIAL_TYPE_TWITTER', 'twitter');
define('SOCIAL_TYPE_LINKEDIN', 'linkedin');
define('SOCIAL_TYPE_GOOGLE', 'google');
define('SOCIAL_TYPE_USERS', 'users');
define('SOCIAL_TYPE_APPS', 'apps');
define('SOCIAL_TYPE_ACTIVITY', 'activity');
define('SOCIAL_TYPE_FILES', 'files');
define('SOCIAL_TYPE_EVENT', 'event');
define('SOCIAL_TYPE_EVENTS', 'events');
define('SOCIAL_TYPE_POLLS', 'polls');
define('SOCIAL_TYPE_VIDEO', 'video');
define('SOCIAL_TYPE_VIDEOS', 'videos');
define('SOCIAL_TYPE_AUDIO', 'audio');
define('SOCIAL_TYPE_AUDIOS', 'audios');
define('SOCIAL_TYPE_ADVERTISEMENT', 'advertisement');
define('SOCIAL_TYPE_DISCUSSIONS', 'discussions');

// since those previous cluster discussion everything respect "discussions" element instead of "discussion"
// Added one more constant for declare this.
define('SOCIAL_TYPE_DISCUSSION', 'discussion');

define('SOCIAL_TYPE_FEEDS', 'feeds');
define('SOCIAL_TYPE_NEWS', 'news');
define('SOCIAL_TYPE_REVIEWS', 'reviews');
define('SOCIAL_TYPE_TASKS', 'tasks');

// Region types
define('SOCIAL_REGION_TYPE_COUNTRY', 'country');
define('SOCIAL_REGION_TYPE_STATE', 'state');
define('SOCIAL_REGION_TYPE_CITY', 'city');

// Model pagination
define('SOCIAL_PAGINATION_ENABLE', true);
define('SOCIAL_PAGINATION_NO_LIMIT', -1);

// Notification states
define('SOCIAL_NOTIFICATION_STATE_READ', 1);
define('SOCIAL_NOTIFICATION_STATE_HIDDEN', 2);
define('SOCIAL_NOTIFICATION_STATE_UNREAD', 0);
define('SOCIAL_NOTIFICATION_STATE_SCHEDULED', 4);
define('SOCIAL_NOTIFICATION_AGGREGATE_ITEMS', true);
define('SOCIAL_NOTIFICATION_GROUP_ITEMS', true);

// Cluster Notification types
define('SOCIAL_NOTIFICATION_TYPE_BOTH', 1);
define('SOCIAL_NOTIFICATION_TYPE_EMAIL', 2);
define('SOCIAL_NOTIFICATION_TYPE_INTERNAL', 3);
define('SOCIAL_NOTIFICATION_TYPE_NONE', 4);

define('SOCIAL_PRIVACY_PUBLIC', 0);
define('SOCIAL_PRIVACY_MEMBER', 10);
define('SOCIAL_PRIVACY_FRIENDS_OF_FRIEND', 20);
define('SOCIAL_PRIVACY_FRIEND', 30);
define('SOCIAL_PRIVACY_ONLY_ME', 40);
define('SOCIAL_PRIVACY_CUSTOM', 100);
define('SOCIAL_PRIVACY_FIELD', 200);
define('SOCIAL_PRIVACY_0', 'public');
define('SOCIAL_PRIVACY_10', 'member');
define('SOCIAL_PRIVACY_20', 'friends_of_friend');
define('SOCIAL_PRIVACY_30', 'friend');
define('SOCIAL_PRIVACY_40', 'only_me');
define('SOCIAL_PRIVACY_100', 'custom');
define('SOCIAL_PRIVACY_200', 'field');

define('SOCIAL_PRIVACY_TYPE_USER', 'user');
define('SOCIAL_PRIVACY_TYPE_PROFILES', 'profiles');
define('SOCIAL_PRIVACY_TYPE_ITEM', 'item');

// Services Server
define('SOCIAL_SERVICE_NEWS', 'https://stackideas.com/updater/manifests/easysocial');
define('SOCIAL_SERVICE_VERSION', 'https://stackideas.com/updater/manifests/easysocial');
define('SOCIAL_SERVICE_JUPDATE', 'https://stackideas.com/jupdates/manifest/easysocial');
define('SOCIAL_OPTIMIZER_SERVER', 'https://services.stackideas.com/optimizer');
define('SOCIAL_JWT_SERVER', 'https://services.stackideas.com/jwt?product=easysocial');

// Api Validater
define('SOCIAL_API_VALIDATER', 'https://stackideas.com/index.php');

// Define theme variables
define('SOCIAL_THEME_COMPILE_AUTO', 'auto');
define('SOCIAL_THEME_COMPILE_MANUAL', 'manual');
define('SOCIAL_THEME_COMPILE_CACHE', 'cache');

// Define custom fields constants
define('SOCIAL_FIELDS_GROUP_USER', 'user');
define('SOCIAL_FIELDS_GROUP_GROUP', 'group');
define('SOCIAL_FIELDS_GROUP_EVENT', 'event');
define('SOCIAL_FIELDS_GROUP_PAGE', 'page');
define('SOCIAL_FIELDS_PREFIX', 'es-fields-');

// Profiles
define('SOCIAL_PROFILES_VIEW_REGISTRATION', 'registration');
define('SOCIAL_PROFILES_VIEW_EDIT', 'edit');
define('SOCIAL_PROFILES_VIEW_DISPLAY', 'display');

// Clusters
define('ES_CLUSTER_TYPE_INVITE', 3);

// Groups
define('SOCIAL_GROUPS_VIEW_REGISTRATION', 'registration');
define('SOCIAL_GROUPS_VIEW_EDIT', 'edit');
define('SOCIAL_GROUPS_VIEW_DISPLAY', 'display');
define('SOCIAL_GROUPS_PUBLIC_TYPE', 1);
define('SOCIAL_GROUPS_PRIVATE_TYPE', 2);
define('SOCIAL_GROUPS_INVITE_TYPE', 3);
define('SOCIAL_GROUPS_SEMI_PUBLIC_TYPE', 4);
define('SOCIAL_GROUPS_MEMBER_PUBLISHED', 1);
define('SOCIAL_GROUPS_MEMBER_PENDING', 2);
define('SOCIAL_GROUPS_MEMBER_INVITED', 3);
define('SOCIAL_GROUPS_MEMBER_BEING_JOINED', 4);

// Pages
define('SOCIAL_PAGES_VIEW_REGISTRATION', 'registration');
define('SOCIAL_PAGES_VIEW_EDIT', 'edit');
define('SOCIAL_PAGES_VIEW_DISPLAY', 'display');
define('SOCIAL_PAGES_PUBLIC_TYPE', 1);
define('SOCIAL_PAGES_PRIVATE_TYPE', 2);
define('SOCIAL_PAGES_INVITE_TYPE', 3);
define('SOCIAL_PAGES_MEMBER_PUBLISHED', 1);
define('SOCIAL_PAGES_MEMBER_PENDING', 2);
define('SOCIAL_PAGES_MEMBER_INVITED', 3);
define('SOCIAL_PAGES_MEMBER_BEING_LIKED', 4);

// Define points constants
define('SOCIAL_POINTS_EVERY_TIME', 0);
define('SOCIAL_POINTS_STORY_TYPES_STORY', 'story');
define('SOCIAL_POINTS_STORY_TYPES_LINKS', 'links');

// Define stream display modes.
define('SOCIAL_STREAM_DISPLAY_FULL', 'full');
define('SOCIAL_STREAM_DISPLAY_MINI', 'mini');

// define stream actor type
define('SOCIAL_STREAM_ACTOR_TYPE_USER', 'user');

// defune stream context type core.
define('SOCIAL_STREAM_CONTEXT_TASKS', 'tasks');
define('SOCIAL_STREAM_CONTEXT_LISTS', 'lists');
define('SOCIAL_STREAM_CONTEXT_FRIENDS', 'friends');
define('SOCIAL_STREAM_CONTEXT_PHOTOS', 'photos');
define('SOCIAL_STREAM_CONTEXT_ALBUMS', 'albums');

//define stream hide type
define('SOCIAL_STREAM_HIDE_TYPE_STREAM', 'stream');
define('SOCIAL_STREAM_HIDE_TYPE_ACTIVITY', 'activity');

// define stream tagging type
define('SOCIAL_STREAM_TAGGING_TYPE_USER', 'user');

//define subsription type.
define('SOCIAL_SUBSCRIPTION_TYPE_USER', 'user');
define('SOCIAL_SUBSCRIPTION_TYPE_STREAM', 'stream');

// Photos
define('SOCIAL_PHOTOS_SQUARE_WIDTH', 128);
define('SOCIAL_PHOTOS_SQUARE_HEIGHT', 128);
define('SOCIAL_PHOTOS_THUMB_WIDTH', 256);
define('SOCIAL_PHOTOS_THUMB_HEIGHT', 256);
define('SOCIAL_PHOTOS_FEATURED_WIDTH', 512);
define('SOCIAL_PHOTOS_FEATURED_HEIGHT', 512);
define('SOCIAL_PHOTOS_LARGE_WIDTH', 1280);
define('SOCIAL_PHOTOS_LARGE_HEIGHT', 1280);
define('SOCIAL_PHOTOS_GIF_THUMB_WIDTH', 256);
define('SOCIAL_PHOTOS_GIF_THUMB_HEIGHT', 256);
define('SOCIAL_PHOTOS_GIF_LARGE_WIDTH', 640);
define('SOCIAL_PHOTOS_GIF_LARGE_HEIGHT', 640);
define('SOCIAL_PHOTOS_META_PATH', 'path');
define('SOCIAL_PHOTOS_META_EXIF', 'exif');
define('SOCIAL_PHOTOS_META_TRANSFORM', 'transform');
define('SOCIAL_PHOTOS_LARGE', 'large');
define('SOCIAL_PHOTOS_THUMB', 'thumb');
define('SOCIAL_PHOTOS_STATE_TMP', '-1');
define('SOCIAL_PHOTOS_META_WIDTH', 'width');
define('SOCIAL_PHOTOS_META_HEIGHT', 'height');
define('SOCIAL_PHOTOS_META_SIZE', 'size');

// stream type
define('SOCIAL_STREAM_CONTEXT_TYPE_ALL', 'all');

// Define notification property.
define('SOCIAL_NOTIFICATION_FORMAT_ITEM', true);

define('SOCIAL_LIKES_MAX_NAME', 3);

// define indexer core type.
define('SOCIAL_INDEXER_TYPE_LISTS', 'lists');
define('SOCIAL_INDEXER_TYPE_USERS', 'users');
define('SOCIAL_INDEXER_TYPE_PHOTOS', 'photos');
define('SOCIAL_INDEXER_TYPE_ALBUMS', 'albums');
define('SOCIAL_INDEXER_TYPE_GROUPS', 'groups');
define('SOCIAL_INDEXER_TYPE_EVENTS', 'events');
define('SOCIAL_INDEXER_TYPE_PAGES', 'pages');
define('SOCIAL_INDEXER_TYPE_MARKETPLACES', 'marketplaces');
define('SOCIAL_INDEXER_TYPE_VIDEOS', 'videos');
define('SOCIAL_INDEXER_TYPE_AUDIOS', 'audios');

// Album constants
define('SOCIAL_ALBUM_PROFILE_PHOTOS', 1);
define('SOCIAL_ALBUM_PROFILE_COVERS', 2);
define('SOCIAL_ALBUM_STORY_ALBUM', 3);
define('SOCIAL_ALBUM_MARKETPLACE_ALBUM', 4);

define('SOCIAL_ALBUM_READY_TO_NOTIFY', 2);
define('SOCIAL_ALBUM_NOTIFIED', 1);

// Services location
define('SOCIAL_UPDATER_LANGUAGE', 'https://services.stackideas.com/translations/easysocial');
define('SOCIAL_SERVICE_IMAGE_RESIZER', 'https://services.stackideas.com/resizer/easysocial');
define('SOCIAL_SERVICE_PACKAGES_DISCOVER', 'https://services.stackideas.com/packages/discover/easysocial');
define('SOCIAL_SERVICE_PACKAGES_DOWNLOAD', 'https://services.stackideas.com/packages/download/easysocial');

// Languages
define('SOCIAL_LANGUAGES_INSTALLED', 1);
define('SOCIAL_LANGUAGES_NOT_INSTALLED', 0);
define('SOCIAL_LANGUAGES_NEEDS_UPDATING', 3);

// Packages
define('SOCIAL_PACKAGE_INSTALLED', 1);
define('SOCIAL_PACKAGE_NOT_INSTALLED', 0);
define('SOCIAL_PACKAGE_NEEDS_UPDATING', 3);

// stream hard limit for pagination
define('SOCIAL_STREAM_HARD_LIMIT', 25);
define('SOCIAL_STREAM_GUEST_LIMIT', 10);


// Remote storage contants
define('SOCIAL_STORAGE_DEFAULT_CONTAINER', 'easysocial');
define('SOCIAL_STORAGE_JOOMLA', 'joomla');

// Exceptions
define('SOCIAL_EXCEPTION_MESSAGE', 'message');
define('SOCIAL_EXCEPTION_UPLOAD', 'upload');

// Clusters
define('SOCIAL_CLUSTER_PUBLISHED', 1);
define('SOCIAL_CLUSTER_UNPUBLISHED', 0);
define('SOCIAL_CLUSTER_PENDING', 2);
define('SOCIAL_CLUSTER_DRAFT', 3);
define('SOCIAL_CLUSTER_UPDATE_PENDING', 4); // Cluster was published before

// Events
define('SOCIAL_EVENT_VIEW_REGISTRATION', 'registration');
define('SOCIAL_EVENT_VIEW_EDIT', 'edit');
define('SOCIAL_EVENT_VIEW_DISPLAY', 'display');
define('SOCIAL_EVENT_TYPE_PUBLIC', 1);
define('SOCIAL_EVENT_TYPE_PRIVATE', 2);
define('SOCIAL_EVENT_TYPE_INVITE', 3);
define('SOCIAL_EVENT_TYPE_SEMI_PUBLIC', 4);

define('SOCIAL_EVENTS_MEMBER_PUBLISHED', 1);
define('SOCIAL_EVENTS_MEMBER_PENDING', 2);
define('SOCIAL_EVENTS_MEMBER_INVITED', 3);
define('SOCIAL_EVENTS_MEMBER_BEING_JOINED', 4);

define('SOCIAL_EVENT_FILTER_YEAR', 1);
define('SOCIAL_EVENT_FILTER_MONTH', 2);
define('SOCIAL_EVENT_FILTER_DAY', 3);

// Event guests state
define('SOCIAL_EVENT_GUEST_INVITED', 0);
define('SOCIAL_EVENT_GUEST_GOING', 1);
define('SOCIAL_EVENT_GUEST_PENDING', 2);
define('SOCIAL_EVENT_GUEST_MAYBE', 3);
define('SOCIAL_EVENT_GUEST_NOT_GOING', 4);
define('SOCIAL_EVENT_GUEST_NOTGOING', 4);

define('SOCIAL_STREAM_STATE_TRASHED', '0');
define('SOCIAL_STREAM_STATE_PUBLISHED', '1');
define('SOCIAL_STREAM_STATE_RESTORED', '2');
define('SOCIAL_STREAM_STATE_ARCHIVED', '3');
define('SOCIAL_STREAM_STATE_SCHEDULED', '4');
define('SOCIAL_STREAM_STATE_MODERATE', '5');

define('SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT', 10);

define('SOCIAL_TASK_UNPUBLISHED', 0);
define('SOCIAL_TASK_UNRESOLVED', 1);
define('SOCIAL_TASK_OPEN', 1);
define('SOCIAL_TASK_RESOLVED', 2);
define('SOCIAL_TASK_CLOSED', 2);

// Source: http://www.phpro.org/tutorials/Geo-Targetting-With-PHP-And-MySQL.html
define('SOCIAL_LOCATION_UNIT_MILE', 69);
define('SOCIAL_LOCATION_UNIT_KM', 111);
define('SOCIAL_LOCATION_RADIUS_MILE', 3959);
define('SOCIAL_LOCATION_RADIUS_KM', 6371);

define('SOCIAL_STREAM_LAST_ACTION_LIKE', 'like');
define('SOCIAL_STREAM_LAST_ACTION_COMMENT', 'comment');

define('SOCIAL_VIDEO_PUBLISHED', 1);
define('SOCIAL_VIDEO_UNPUBLISHED', 0);
define('SOCIAL_VIDEO_PENDING', 2);
define('SOCIAL_VIDEO_PROCESSING', 3);
define('SOCIAL_VIDEO_FEATURED', 1);
define('SOCIAL_VIDEO_UPLOAD', 'upload');
define('SOCIAL_VIDEO_LINK', 'link');
define('SOCIAL_VIDEO_SCHEDULED', 1);
define('SOCIAL_VIDEO_UNSCHEDULED', 0);

define('SOCIAL_AUDIO_PUBLISHED', 1);
define('SOCIAL_AUDIO_UNPUBLISHED', 0);
define('SOCIAL_AUDIO_PENDING', 2);
define('SOCIAL_AUDIO_PROCESSING', 3);
define('SOCIAL_AUDIO_FEATURED', 1);
define('SOCIAL_AUDIO_UPLOAD', 'upload');
define('SOCIAL_AUDIO_LINK', 'link');
define('SOCIAL_AUDIO_SCHEDULED', 1);
define('SOCIAL_AUDIO_UNSCHEDULED', 0);

define('SOCIAL_DIGEST_DEFAULT', 1);
define('SOCIAL_DIGEST_DAILY', 2);
define('SOCIAL_DIGEST_WEEKLY', 3);
define('SOCIAL_DIGEST_MONTHLY', 4);
define('SOCIAL_DIGEST_MAX_COUNT', 20);

define('SOCIAL_ACCESS_LIMIT_INTERVAL_NO', 0);
define('SOCIAL_ACCESS_LIMIT_INTERVAL_DAILY', 1);
define('SOCIAL_ACCESS_LIMIT_INTERVAL_WEEKLY', 2);
define('SOCIAL_ACCESS_LIMIT_INTERVAL_MONTHLY', 3);
define('SOCIAL_ACCESS_LIMIT_INTERVAL_YEARLY', 4);
define('ES_NOTIFIER_POLLING_INTERVAL', 60);
define('ES_VERIFICATION_REQUEST', 0);
define('ES_VERIFICATION_APPROVED', 1);
define('ES_VERIFICATION_REJECTED', 2);

// GDPR download request state
define('ES_DOWNLOAD_REQ_NEW', 0);
define('ES_DOWNLOAD_REQ_LOCKED', 1);
define('ES_DOWNLOAD_REQ_PROCESS', 2);
define('ES_DOWNLOAD_REQ_READY', 3);

define('ES_FILE_SUB_PREFIX_CONVERSATION', 'm');

// Show other video section
define('SOCIAL_VIDEO_OTHER_NONE', 0);
define('SOCIAL_VIDEO_OTHER_RECENT', 1);
define('SOCIAL_VIDEO_OTHER_CATEGORY', 2);

// SEF urls cache limit
define('SOCIAL_SEF_LIMIT', 5000);
define('SOCIAL_SEF_FILESIZE', 500); // in KB

// media sef format
define('SOCIAL_MEDIA_SEF_DEFAULT', 'default');
define('SOCIAL_MEDIA_SEF_WITHUSER', 'withuser');

// user profile indexing value
define('SOCIAL_PROFILE_ROBOTS_INDEX', 'index');
define('SOCIAL_PROFILE_ROBOTS_NOINDEX', 'noindex');
define('SOCIAL_PROFILE_ROBOTS_INHERIT', 'inherit');

// Storage usage status
define('SOCIAL_STORAGE_STATUS_NOT_FULL', 'success');
define('SOCIAL_STORAGE_STATUS_ALMOST_FULL', 'warning');
define('SOCIAL_STORAGE_STATUS_FULL', 'danger');

// the type of mobile app identifier
define('SOCIAL_MOBILE_APP_TYPE_ESMOBILE', 'es-mobile');
define('SOCIAL_MOBILE_APP_TYPE_ESCONVERSATION', 'es-conversation');

// Adverts
define('SOCIAL_ADS_DRAFT', -1);
define('SOCIAL_ADS_PUBLISHED', 1);
define('SOCIAL_ADS_UNPUBLISHED', 0);
define('SOCIAL_ADS_MODERATION', 2);
define('SOCIAL_ADS_PRIORITY_LOW', 1);
define('SOCIAL_ADS_PRIORITY_MED', 2);
define('SOCIAL_ADS_PRIORITY_HIGH', 3);
define('SOCIAL_ADS_PRIORITY_HIGHEST', 4);

define('SOCIAL_ROUTER_MODE_SEF', 1);

// Show other marketplace section
define('SOCIAL_MARKETPLACE_OTHER_NONE', 0);
define('SOCIAL_MARKETPLACE_OTHER_RECENT', 1);
define('SOCIAL_MARKETPLACE_OTHER_CATEGORY', 2);
define('SOCIAL_MARKETPLACE_PENDING', 2);
define('SOCIAL_MARKETPLACE_DRAFT', 3);
define('SOCIAL_MARKETPLACE_FEATURED', 1);
define('SOCIAL_MARKETPLACE_UPDATE_PENDING', 4);
define('SOCIAL_MARKETPLACE_SOLD', 5);
define('SOCIAL_MARKETPLACE_CONDITION_NEW', 1);
define('SOCIAL_MARKETPLACE_CONDITION_USED', 2);
define('SOCIAL_MARKETPLACE_SCHEDULED', 1);
define('SOCIAL_MARKETPLACE_UNSCHEDULED', 0);
define('SOCIAL_FIELDS_GROUP_MARKETPLACE', 'marketplace');
define('SOCIAL_TYPE_MARKETPLACE', 'marketplace');
define('SOCIAL_TYPE_MARKETPLACES', 'marketplaces');

define('ES_EMPTY_DATE', '0000-00-00 00:00:00');
