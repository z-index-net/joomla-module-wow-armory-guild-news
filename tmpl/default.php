<?php

/**
 * @version		$Id$
 * @author              Branko Wilhelm, mediahof, Kiel-Germany
 * @package		mod_simple_marquee
 * @copyright           Copyright (C) 2011 mediahof. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL version 3
 */
// no direct access
defined('_JEXEC') or die;

?>
<div class="mod_simple_marquee">
<a name="marqueeconfig" rel="speed=<?php echo $speed; ?>|direction=<?php echo $direction; ?>|pauseOnOver=<?php echo $pauseOnOver; ?>"></a>
<div class="mod_simple_marquee_content"><?php echo $marquee; ?></div>
</div>
