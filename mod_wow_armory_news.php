<?php

/**
 * WoW Armory Guild News
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
 
// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once(dirname(__FILE__) . '/helper.php');

mod_wow_armory_news::onload();


require JModuleHelper::getLayoutPath('mod_wow_armory_news', $params->get('layout', 'default'));
