<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 

<!-- Metadata -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<meta name="distribution" content="Global" /> 
<meta name="robots" content="index,follow" /> 
<meta name="author" content="Zhou Yuan" /> 
<meta name="copyright" content="Copyright (C) Zhou Yuan" /> 
<meta name="rating" content="General" /> 
<meta name="revisit-after" content="14 Days" /> 
<meta name="description" content="InfoPotato" /> 
<meta name="keywords" content="InfoPotato" /> 

<title><?php echo $page_title; ?> | InfoPotato</title>

<!-- favicon -->  
<link rel="icon" href="<?php echo STATIC_URI_BASE; ?>images/shared/favicon.ico" type="image/x-icon" /> 

<link rel="image_src" href="<?php echo STATIC_URI_BASE; ?>images/shared/fb_like_logo.jpg" />

<!-- CSS Style --> 
<link type="text/css" rel="stylesheet" href="<?php echo APP_URI_BASE; ?>css/index/main.css:fb-buttons.css<?php if(isset($stylesheets)) { echo ':'.implode(':', $stylesheets);  } ?>" media="all" charset="utf-8" /> 
<link type="text/css" rel="stylesheet" href="<?php echo APP_URI_BASE; ?>css/index/print.css" media="print" charset="utf-8" /> 

<!-- JavaScript -->
<script type="text/javascript" language="javascript" src="<?php echo APP_URI_BASE; ?>js/index/sweet_titles.js"></script>
	
<?php if(isset($javascripts)) : ?>
<script type="text/javascript" language="javascript" src="<?php echo APP_URI_BASE; ?>js/index/<?php echo implode(':', $javascripts); ?>"></script>
<?php endif; ?>
</head> 

<body>	

<!-- begin header -->
<div class="header"> 

<div class="topnav">

<div class="logo">
<a href="<?php echo APP_URI_BASE; ?>home/" title="Return to frontpage"><img src="<?php echo STATIC_URI_BASE; ?>images/shared/logo.jpg" alt="InfoPotato" /></a>
</div>

<ul> 
<li><a href="<?php echo APP_URI_BASE; ?>about/" title="About">About</a></li>
<li><a href="<?php echo APP_URI_BASE; ?>code/" title="Code">Code</a></li>
<li><a href="<?php echo APP_URI_BASE; ?>documentation/" title="Documentation">Documentation</a></li>
<li><a href="<?php echo APP_URI_BASE; ?>contact/" title="Contact">Contact</a></li>
</ul>

</div>	

<div class="clear"></div>

</div>  
<!-- end header --> 

<!-- begin main content --> 
<div class="content"> 
<?php echo $content; ?>
<div class="clear"></div>
<div class="hide">
Page URI: <?php echo 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];?>
</div>
</div> 
<!-- end main content --> 

<!-- begin footer --> 
<div class="footer">
<div class="copyright">
Powered by InfoPotato PHP5 Framework &copy; Zhou Yuan 2009-2011
</div>
</div>
<!-- end footer -->

</body> 

</html> 
