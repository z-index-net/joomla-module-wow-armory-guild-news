<?php

/**
 * WoW Armory Guild News
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2011 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.cache.cache');

class mod_wow_armory_guild_news {

    public static function onload(&$params) {

        // all required paramters set?
        if (!$params->get('lang') || !$params->get('realm') || !$params->get('guild')) {
            return JText::_('please configure Module') . ' - ' . __CLASS__;
        }

        // if curl installed?
        if (!function_exists('curl_init')) {
            return JText::_('php-curl extension not found');
        }

        $scheme = JURI::getInstance()->getScheme();
        $realm = urlencode(strtolower($params->get('realm')));
        $guild = urlencode(strtolower($params->get('guild')));
        $lang = $params->get('lang');
        $region = $params->get('region');
        $wowhead_lang = $params->get('wowhead_lang');
        $url = 'http://' . $region . '.battle.net/wow/' . $lang . '/guild/' . $realm . '/' . $guild . '/news';

        // wowhead script integration if wanted
        if ($params->get('wowhead')) {
            JFactory::getDocument()->addScript($scheme . '://static.wowhead.com/widgets/power.js');
        }

        $cache = & JFactory::getCache(); // get cache obj
        $cache->setCaching(1); // enable cache for this module
        $cache->setLifeTime($params->get('cachetime') * 60); // time to cache

        $result = $cache->call(array(__CLASS__, 'curl'), $url, $params->get('timeout', 10)); // Joomla has nice functions ;)

        $cache->setCaching(JFactory::getConfig()->getValue('config.caching')); // restore default cache mode

        if (!strpos($result['body'], '<div id="news-list">')) { // check if guild data exists
            return JText::_('no guild data found');
        }

        // remove unneeded attributes
        $content = str_replace(array("\t", "\r", "\n"), ' ', $result['body']);

        // get only news data
        preg_match('#<div id="news-list">(.*?)<ul class="(.*?)">(.*?)</ul>(.*?)</div>#', $content, $data);

        // remove unnecessary whitespaces
        $search[] = '#\s{2,10}#';
        $replace[] = '';

        // remove any attributes from links
        $search[] = '#<a(.*?)href="(.*?)"(.*?)>(.*?)</a>#';
        $replace[] = '<a href="$2">$4</a>';

        // replace item icon with img tag
        $search[] = '#<span(.*?)style=\'background-image: url\("(.*?)"\);\'(.*?)>(.*?)</span>#';
        $replace[] = $params->get('icons') ? '<img src="$2" width="18" height="18" alt="" />' : '';

        // wowhead: player achievement // /wow/de/character/blackhand/Sneakz/achievement#92:a7
        $search[] = '#/wow/' . $lang . '/character/' . $realm . '/(\S\w+)/achievement\#(\w+):a(\w+)#';
        $replace[] = $scheme . '://' . $wowhead_lang . '.wowhead.com/achievement=$3';

        // armory: player link
        $search[] = '#/wow/' . $lang . '/character/' . $realm . '/(\S\w+)#';
        $replace[] = $scheme . '://' . $region . '.battle.net/wow/' . $lang . 'character/' . $realm . '/$1';

        // wowhead: guild achievement
        $search[] = '#/wow/' . $params->get('lang') . '/guild/' . $realm . '/' . $guild . '/achievement\#(\d+):a(\d+)#';
        $replace[] = $scheme . '://' . $wowhead_lang . '.wowhead.com/achievement=$2';

        // wowhead: item link
        $search[] = '#/wow/' . $lang . '/item/(\d+)#';
        $replace[] = $scheme . '://' . $wowhead_lang . '.wowhead.com/item=$1';

        $data[3] = preg_replace($search, $replace, $data[3]);

        // find all <li>
        preg_match_all('#<li(.*?)>(.*?)<\/li>#', $data[3], $result, PREG_PATTERN_ORDER);

        // display only X results - max 25
        $rows = ($params->get('rows') >= 25) ? 25 : $params->get('rows', 5);
        for ($i = 0; $i < $rows; $i++) {
            $newsItem[] = trim($result[2][$i]);
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