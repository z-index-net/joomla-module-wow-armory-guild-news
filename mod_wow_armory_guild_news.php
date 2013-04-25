<?php

/**
 * WoW Armory Guild News Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2013 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version    $Id$
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

$news = new mod_wow_armory_guild_news($params);

$news = $news->data();

if(!is_array($news)) {
	echo $news;
	return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));