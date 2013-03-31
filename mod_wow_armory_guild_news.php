<?php
/**
 * WoW Armory Guild News Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2013 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU General Public License v3
 * @version    $Id$
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$news = mod_wow_armory_guild_news::onload($params, $module);

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
