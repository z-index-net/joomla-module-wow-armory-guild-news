<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 
defined('_JEXEC') or die;

final class mod_wow_armory_guild_news {
	
	private $params = null;
	
	public function __construct(JRegistry &$params) {
		$this->params = $params;
		$this->params->set('guild', rawurlencode(str_replace(' ', '_', JString::strtolower($this->params->get('guild')))));
		$this->params->set('realm', rawurlencode(preg_replace('#[^- _\pL]#u', '', JString::strtolower($this->params->get('realm')))));
		$this->params->set('region', JString::strtolower($this->params->get('region')));
		$this->params->set('lang', JString::strtolower($this->params->get('lang', 'en')));
		$this->params->set('link', $this->params->get('link', 'battle.net'));
	}

    public function data() {       
        $url = 'http://' . $this->params->get('region') . '.battle.net/wow/' . $this->params->get('lang') . '/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '/news';

        $cache = JFactory::getCache(__CLASS__ , 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($this->params->get('cache_time', 60));

        $key = md5($url);
         
        if(!$result = $cache->get($key)) {
        	try {
        		$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
        		$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; Wow Armory Guild News Module; php/' . phpversion());
        	
        		$result = $http->get($url, null, $this->params->get('timeout', 10));
        	}catch(Exception $e) {
        		return $e->getMessage();
        	}
        	
        	$cache->store($result, $key);
        }

        if($result->code != 200) {
        	return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
        }
        
        if(strpos($result->body, '<div id="news-list">') === false) {
        	return 'no news found';
        }
        
        // get only news data
        preg_match('#<div id="news-list">(.+?)</div>#si', $result->body, $result->body);
        
        $result->body = $result->body[1];
        
        // remove unnecessary whitespaces
        $search[] = '#\s{2,10}#';
        $replace[] = '';
        
        // would disable wowhead tooltips?!
        $search[] = '#rel="np"#';
        $replace[] = '';
        
        // add link target
        $search[] = '#href="#';
        $replace[] = 'target="_blank" href="';

        // replace item icon with img tag
        $search[] = '#<span.*?style=\'background-image: url\("(.*?)"\);\'.*?>.*?</span>#i';
        $replace[] = '<img src="$1" width="18" height="18" alt="" />';
        
        $result->body = preg_replace($search, $replace, $result->body);
        
        $links[] = '#/wow/' . $this->params->get('lang') . '/character/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/(item)/(\d+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/guild/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/(character)/[^/]+/(\S[[:graph:]]+)/"#i';
        
        $result->body = preg_replace_callback($links, array(&$this, 'link'), $result->body);
         
        // at last split data at <li>
        preg_match_all('#<li.*?>(.*?)<\/li>#',  $result->body, $result->body, PREG_PATTERN_ORDER);

        return array_slice($result->body[1], 0, $this->params->get('rows'));
    }
    
    private function link($matches) {
    	$sites['item']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
    	$sites['item']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/item=' . $matches[2];
    	$sites['item']['wowdb.com'] = 'http://www.wowdb.com/items/' . $matches[2];
    	$sites['item']['buffed.de'] = 'http://wowdata.buffed.de/?i=' . $matches[2];
    	
    	if($matches[1] == 'achievement') {
    		$achievement = substr($matches[2], strpos($matches[2], ':a')+2);
    		$sites['achievement']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
	    	$sites['achievement']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/achievement=' . $achievement;
	    	$sites['achievement']['wowdb.com'] = 'http://www.wowdb.com/achievements/' . $achievement;
	    	$sites['achievement']['buffed.de'] = 'http://wowdata.buffed.de/?a=' . $achievement;
    	}
    	
    	$sites['character']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
    	$sites['character']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/profile=' . $this->params->get('region') . '.' . $this->params->get('realm'). '.' . $matches[2];
    	 
    	return isset($sites[$matches[1]][$this->params->get('link')]) ? $sites[$matches[1]][$this->params->get('link')] : $sites[$matches[1]]['battle.net'];
    }
}