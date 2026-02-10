<?php
// this should be set to the proper place (ie. in module's template directory)
// automatically in SmartyResponse, so no need to set
$smarty['templates'] = '';
////////////////

// these two directories must be web server writeable and should not be in web
// server's document root
$smarty['templates_c'] = 'C:/Temp/smarty_cache/templates_c';
$smarty['cache'] = 'C:/Temp/smarty_cache/cache';

// this should be the same as GTFW config files reside
$smarty['configs'] = 'C:/My Projects/Work on GT/Framework/gtfw-166/config';
?>