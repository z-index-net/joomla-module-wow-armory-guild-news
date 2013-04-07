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

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';


$params->set('guild', rawurlencode(str_replace(' ', '_', strtolower($params->get('guild')))));
$params->set('realm', rawurlencode(strtolower($params->get('realm'))));
$params->set('region', strtolower($params->get('region')));
$params->set('lang', strtolower($params->get('lang', 'en')));
$params->set('link', $params->get('link', 'battle.net'));

$news = new mod_wow_armory_guild_news($params);

$news = $news->data();

if(!is_array($news)) {
	echo $news;
	return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));