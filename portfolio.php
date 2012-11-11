<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>nickolas nikolic photography</title>
	<meta name="DESCRIPTION" content="It is at where life is unique that I point my camera." />
	<link href="media/css/styles.css" rel="stylesheet" type="text/css" />
    <!-- jquery / jquery-ui libs 
	**need 1 query, 1 jquery-ui and 1 jquery.bgiframe IN THAT ORDER
	-->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js" type="text/javascript"></script> 
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js" type="text/javascript"></script> 
	<script src="http://jquery-ui.googlecode.com/svn/tags/latest/external/jquery.bgiframe-2.1.2.js" type="text/javascript"></script>

	<!-- jquery fade script-->
	<script src="media/javascript/jqueryFade.js" type="text/javascript"></script> 
</head>

<body>
<!-- START CONTAINER //-->
	<div id="container" class="container">
		<div id="logo" class="logo">
        	<a href="http://nickolasnikolic.com"><img src="/media/images/logo.gif" width="288" height="72" alt="nickolas nikolic logo" /></a>
         </div>
		
<!-- START NAVIGATION //-->
		<div id="navBar" class="navBar">				<a id="nav1" class="navLink" href="http://www.nickolasnikolic.com/">home&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
				<a id="nav2" class="navLink" href="portfolio.php">portfolio&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
				<a id="nav3" class="navLink" href="about.html">about&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                <a id="nav3" class="navLink" href="projects.html">projects&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
				<a id="nav4" class="navLink" href="contact.html">contact&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
</div>
<!-- END NAVIGATION //-->
<!-- START CONTAINER //-->
		<div id="content" class="content">
			<div id="portfolio" class="portfolio">
				<?php include( "./portfolio/index.php" ); ?>
			</div>
			
			<div id="portfolioContactDiv">
                <div id="socialMedia" class="socialMedia">
                	<div id="facebookLike" class="facebookLike">
                        <iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fnickolasnikolic.com%2Fportfolio.php?<?php echo urlencode( $_SERVER['QUERY_STRING'] ); ?>&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=recommend&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:25px;" allowTransparency="true"></iframe>
                    </div>
                    <div id="twitterTweet" class="twitterTweet" >
                        <a href="http://twitter.com/share" class="twitter-share-button" data-text="Nickolas Nikolic Photography Portfolio" data-count="horizontal" data-via="nickolasnikolic">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                    </div>
                </div>
                <div id="portfolioContact" class="portfolioContact">
                	<p><a class="contactLink" href="contact.html">Contact me</a></p>
                </div>
			</div>
		</div>
		<!-- END CONTAINER //-->

<!-- START FOOTER //-->
		<div id="footer" class="footer">All media Creative Commons Attribution License </div>
<!-- END FOOTER //-->

<!-- START EXTRA OFFERS //-->
		<div id="extraOffers" class="extraOffers"></div>
		<div id="extraPromo" class="extraOffers"></div>
        
<!-- END EXTRA OFFERS //-->
	
<!-- END CONTAINER //-->

</body>
</html>
