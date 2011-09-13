<?php

/**
 * WoW Armory Guild News
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2011 Branko Wilhelm
 * @package    mod_wow_armory_news
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 * @link       www.z-index.net
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.cache.cache');

class mod_wow_armory_news {

    public static function onload(&$params) {

        if (!$params->get('lang') || !$params->get('realm') || !$params->get('guild')) {
            return array(JText::_('please configure Module') . ' - ' . __CLASS__);
        }

        if (!function_exists('curl_init')) {
            return array(JText::_('php-curl extension not found'));
        }

        $config = & JFactory::getConfig();

        $url = "http://eu.battle.net/wow/" . $params->get('lang') . "/guild/" . urlencode($params->get('realm')) . "/" . urlencode($params->get('guild')) . "/news";
        $timeout = $params->get('timeout', 10);

        $cache = & JFactory::getCache(); // get cache obj
        $cache->setCaching(1); // enable cache for this module
        $cache->setLifeTime($params->get('cachetime', $config->getValue('config.cachetime')) * 60); // time to cache

        $result = $cache->call(array(__CLASS__, 'curl'), $url, $timeout);

        $cache->setCaching($config->getValue('config.caching')); // restore default cache mode

        if (!strpos($result['body'], '<div id="news-list">')) {
            return array(JText::_('no guild data found'));
        }

        $content = str_replace(array("\t", "\r", "\n"), ' ', $result['body']);

        preg_match('#<div id="news-list">(.*?)<ul class="(.*?)">(.*?)<\/ul>(.*?)<\/div>#', $content, $data);


// Links werden von rel, style, mouseover und co bereinigt
        $clearLinkSearch = "/<a(.*?)href=\"(.*?)\"(.*?)>(.*?)<\/a>/";
        $clearLinkReplace = '<a href="$2">$4</a>';

// der link von Carakter Achievments wird so geändert, damit die wowhead tooltips funktionieren
        $charakterAchievmentSearch = "/\/wow\/" . $realmLang . "\/character\/" . str_replace(" ", "%20", $realmName) . "\/(\S\w+)\/achievement#(\d+):a(\d+)/";
        $charakterAchievmentReplace = 'http://de.wowhead.com/achievement=$3';

// der link von Charakteren wird zum Arsenal geleitet
        $charakterLinkSearch = "/\/wow\/" . $realmLang . "\/character\/" . str_replace(" ", "%20", $realmName) . "\/(\S\w+)/";
        $charakterLinkReplace = 'http://eu.battle.net/wow/".$realmLang."/character/' . str_replace(" ", "%20", $guildName) . '/$1';

// der link von Gilden Achievments wird so geändert, damit die wowhead tooltips funktionieren
        $guildAchievmentSearch = "/\/wow\/" . $realmLang . "\/guild\/" . str_replace(" ", "%20", $realmName) . "\/" . str_replace(" ", "%20", $guildName) . "\/achievement#(\d+):a(\d+)/";
        $guildAchievmentReplace = 'http://de.wowhead.com/achievement=$2';

// der link von Items werden so geändert, dass die wowhead tooltips funktionieren
        $itemSearch = "/\/wow\/de\/item\/(\d+)/";
        $itemReplace = 'http://de.wowhead.com/item=$1';

// Array für das suchen und ersetzten
        $suchmuster = array($clearLinkSearch, $charakterAchievmentSearch, $charakterLinkSearch, $guildAchievmentSearch, $itemSearch);
        $ersetzen = array($clearLinkReplace, $charakterAchievmentReplace, $charakterLinkReplace, $guildAchievmentReplace, $itemReplace);

        $baz = preg_replace($suchmuster, $ersetzen, $data[3]);

// Finde lis und packe sie in ein array
        preg_match_all("/<li(.*?)>(.*?)<\/li>/", $baz, $newsList, PREG_PATTERN_ORDER);

// finde die ersten 5 einträge
        $newsItem = array();
        for ($i = 0; $i < 5; $i++) {
            $newsItem[] = array($newsList[0][$i]);
        }

        return $newsItem;
    }

    public static function curl($url, $timeout) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla! Wow Armory Guild News Module; php ' . phpversion());
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);

        curl_close($curl);

        return array('info' => $info, 'errno' => $errno, 'error' => $error, 'body' => $body);
    }

}