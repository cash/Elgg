<?php
/**
 * Colorbox lightbox CSS.
 *
 * Used as a view because we need to pass a full URL to AlphaImageLoader.
 *
 * @package    Elgg.Core
 * @subpackage UI
 */

$jquery_path = elgg_get_site_url() . 'vendors/jquery/';
?>

/*
 * ColorBox - jQuery Plugin
 * A lightweight customizable lightbox plugin for jQuery
 *
 */

 /*
    ColorBox Core Style:
    The following CSS is consistent between example themes and should not be altered.
*/
#colorbox, #cboxOverlay, #cboxWrapper{position:absolute; top:0; left:0; z-index:9999; overflow:hidden;}
#cboxOverlay{position:fixed; width:100%; height:100%;}
#cboxMiddleLeft, #cboxBottomLeft{clear:left;}
#cboxContent{position:relative;}
#cboxLoadedContent{overflow:auto;}
#cboxTitle{margin:0;}
#cboxLoadingOverlay, #cboxLoadingGraphic{position:absolute; top:0; left:0; width:100%; height:100%;}
#cboxPrevious, #cboxNext, #cboxClose, #cboxSlideshow{cursor:pointer;}
.cboxPhoto{float:left; margin:auto; border:0; display:block; max-width: none;}
.cboxIframe{width:100%; height:100%; display:block; border:0;}
#colorbox, #cboxContent, #cboxLoadedContent{box-sizing:content-box;}

/* 
    User Style:
    Change the following styles to modify the appearance of ColorBox.  They are
    ordered & tabbed in a way that represents the nesting of the generated HTML.
*/
#cboxOverlay{background:#000;}
#colorbox{}
    #cboxContent{margin-top:20px;}
        .cboxIframe{background:#fff;}
        #cboxError{padding:50px; border:1px solid #ccc;}
        #cboxLoadedContent{border:5px solid #000; background:#fff;}
        #cboxTitle{position:absolute; top:-20px; left:0; color:#ccc;}
        #cboxCurrent{position:absolute; top:-20px; right:0px; color:#ccc;}
        #cboxSlideshow{position:absolute; top:-20px; right:90px; color:#fff;}
        #cboxPrevious{position:absolute; top:50%; left:5px; margin-top:-32px; background:url(<?php echo $jquery_path; ?>colorbox/example3/images/controls.png) no-repeat top left; width:28px; height:65px; text-indent:-9999px;}
        #cboxPrevious:hover{background-position:bottom left;}
        #cboxNext{position:absolute; top:50%; right:5px; margin-top:-32px; background:url(<?php echo $jquery_path; ?>colorbox/example3/images/controls.png) no-repeat top right; width:28px; height:65px; text-indent:-9999px;}
        #cboxNext:hover{background-position:bottom right;}
        #cboxLoadingOverlay{background:#000;}
        #cboxLoadingGraphic{background:url(<?php echo $jquery_path; ?>colorbox/example3/images/loading.gif) no-repeat center center;}
        #cboxClose{position:absolute; top:5px; right:5px; display:block; background:url(<?php echo $jquery_path; ?>colorbox/example3/images/controls.png) no-repeat top center; width:38px; height:19px; text-indent:-9999px;}
        #cboxClose:hover{background-position:bottom center;}
