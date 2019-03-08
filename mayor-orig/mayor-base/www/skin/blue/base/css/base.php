<?php header('Content-type: text/css'); 

    require('../color_schemes.php'); // keretrendszeren kívül fut le!!
    if (!isset($_GET['scheme']) || !is_array($_COLOR_SCHEMES[$_GET['scheme']]))
        $_COLORS = $_COLOR_SCHEMES['blue'];
    else
        $_COLORS = $_COLOR_SCHEMES[$_GET['scheme']];

?>/*
    Module: base

    A rétegek sorrendje: nav2(1), head(2), poz(2), nav1(3), nav(3), logo(3), logobadge(4)
*/
@media screen {

    html { height: 100%; }
    html body { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; margin:0px; padding:0px; height: 100%; overflow: auto; }
    #debug { 
	padding: 20px; background-color: rgba(0,0,0,0.7); color: yellow; border: 0px red solid;
	position: absolute; z-index: 1000; top: 0px; left: 0px; right: 0px; 
    }
    #debug pre { background-color: rgba(240,240,240,0.9); color: #880000; padding: 10px; margin: 0px;}
    #help { box-shadow: 3px 3px 15px 0px rgba(100,100,100,5.0); 
	position: absolute; top: 10px; left: 50%; height: 600px; width: 1000px; z-index: 500; margin: 0 -500px; 
	border: 1px solid rgb(0,0,0); background-color: white; }
    #help iframe { width: 1000px; height: 558px; margin: 0; border: none; }
    #helpHeader { text-align: center; font-size: 16px; width: 980px; height: 20px; margin: 0px; background-color: #194a5b; cursor: pointer; color: white; font-weight: bold; padding: 10px; }
    #hideHelp { 
	position: absolute; top:6px; right: 4px; 
	cursor: pointer; background-color: #822; color: white; padding: 2px 5px; font-size: 16px; 
	border: #822 1px solid; border-radius: 3px; text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.5);
	box-shadow: inset 0px -3px 1px rgba(0, 0, 0, 0.45), 0px 2px 2px rgba(0, 0, 0, 0.25);
    }

    a { text-decoration:none; }
    .onClickHideShow, .onClickHide, .onClickShow { cursor: pointer; }

    div.errorMsg { margin: 20px 20%; background-color: #fee; border: solid 1px red; border-radius: 2px; padding: 15px; box-shadow: 0px 1px 10px rgba(0,0,0,0.5); }
    div.errorMsg span { font-weight: bold; color: #f00; }
    div.infoMsg { margin: 20px 20%; background-color: #eef; border: groove 1px #0000ff; border-radius: 2px; padding: 15px; box-shadow: 0px 1px 10px rgba(0,0,0,0.5); }
    div.infoMsg span { font-weight: bold; color: #00a; }
    div.errorMsg span.alertParam, div.infoMsg span.alertParam { font-weight: normal; font-style: italic; color: green; }

    #logo { z-index: 3; height: 40px; position: absolute; top: 8px; left: 14px; border: 0px none; }
    #logobadge { z-index: 4; height: 25px; position: fixed; top: 28px; left: 90px; border: 0px none; }

    #head { z-index: 2; position: absolute; width: 100%; height: 60px; background: <?php echo $_COLORS['head']; ?> no-repeat right center; }

    #nav, #nav ul { list-style: none; }
    #nav { z-index: 3; margin: 0; height: 60px; padding-left: 140px; }
    #nav li { position: relative; }
/*!*/
    #nav > li { float: left; z-index: 20; text-align: center; padding: 0; height: 60px; background-color: <?php echo $_COLORS['nav']; ?>; border-bottom: 1px solid <?php echo $_COLORS['nav2']; ?>; }
    #nav > li.active { max-width: 160px; }
    #nav > li:hover { z-index: 22; }
    #nav > li > ul { top: 50px; left: -1px; }
    #nav > li > ul > li > a { margin-left: 10px; }

    #nav li:hover { background-color: <?php echo $_COLORS['hover']; ?>;}
/*!*/
    #nav li.active { background-color: <?php echo $_COLORS['active']; ?>; }
    #nav li a { display: block; padding: 3px 10px; color: white; }
    #nav > li > a { padding: 0px 20px; height: 60px; vertical-align: middle; display: table-cell; min-width: 100px; }
    #nav li a:hover { color: <?php echo $_COLORS['hover-color']; ?>; }
    #nav li:hover > ul { display: block; }

    #nav li.nav1szin { background-color: <?php echo $_COLORS['head']; ?>; border-bottom: 1px solid white; }
    #nav ul.sub { background-color: <?php echo $_COLORS['active']; ?>; }


    #nav ul { display: none; position: absolute; width: 160px; padding: 0px; }
    #nav ul span { float: left; margin: 6px 5px; color: white; }
/*!*/
    #nav > li > ul { top: 61px; }
    #nav ul > li { background-color: <?php echo $_COLORS['head']; ?>; }
    #nav ul li { text-align: left; margin-bottom: 1px; }
    #nav ul li a { padding: 6px 10px; }
    #nav ul li.active > a { color: <?php echo $_COLORS['hover-color']; ?>; }

    #nav ul ul { top: 0px; left: -145px; z-index: 30; background-color: <?php echo $_COLORS['head']; ?>; }
    #nav ul ul a { background-color: <?php echo $_COLORS['active']; ?>; }
    #nav ul ul a:hover { background-color: <?php echo $_COLORS['hover']; ?>; }

    #nav li.start { 
	height: 60px; min-width: 0; padding-right: 0px; padding-left: 6px;
	background-color: <?php echo $_COLORS['nav2']; ?>; border-right: 4px solid <?php echo $_COLORS['nav2']; ?>; border-radius: 0px 30px 30px 0px; border-bottom: 0px;
	z-index: 20;
    }

    #poz { z-index: 2; padding: 0; list-style: none; background-color: rgb(230,230,230); margin: 0; height: 20px; border-bottom: #f3f3f3 solid 4px; text-shadow: 0px 1px rgba(52, 150, 185, 0.1); }
    #poz > li { padding: 2px 6px; }
    #poz > li div.nev { float: right; padding: 2px; width: auto; color: <?php echo $_COLORS['head']; ?>; text-align: right; }
    #poz > li div.nev span { margin-right: 0px; font-size:12px;  }
    #poz > li div.nev span:hover { color:<?php echo $_COLORS['login']; ?>; }
    #poz > li a { margin-right:5px; margin-left:15px; padding:0px; margin-top:0px; vertical-align: middle;}
    
    #settings { width: 300px;
	margin-top: 5px; position:relative; right: 20px;
	background-color: white; 
	box-shadow: rgba(0, 0, 0, 0.117188) 0px 2px 4px 0px;
	border: 1px solid rgb(230,230,230);
	border-top-width: 0px; 
    }
    #settings div { padding: 20px 10px; box-shadow: rgba(0, 0, 0, 0.117188) 0px 2px 4px 0px;
	border-bottom: 1px solid rgb(230,230,230); margin-bottom: 2px;}
    #settings div p { margin: 0; padding: 0; text-align: center; }
    #settings div p.name { font-weight: bold; }
    #settings table { width: 100%; background-color: rgb(245,245,245); }
    #settings td { width: 50%; text-align: center; padding: 10px; }
    #settings td a { 
	display: inline-block; 
	vertical-align: baseline; line-height: 29px;
	position: static;
	padding: 0px 8px; height: 29px; margin: 0px 8px;
	text-align: center; text-decoration: none; font-weight: bold; font-size: 10px;
	background-color: white; color: #444;
	border: solid 1px rgba(0, 0, 0, 0.0976563); border-radius: 2px; 
    }
    #settings td a:hover { border: #c6c6c6 1px solid; }
    #poz > li span.school {
	    font-variant: small-caps;
	    font-size: 14px;
	    color: white;
	    letter-spacing: 0.4em;
	    overflow: hidden;
    }

    #nav1 { z-index: 3; position: absolute; margin-left: 310px; margin-right: 0px; width: 100%;}
    #nav1 div { background-color: <?php echo colorToRGBA($_COLORS['head'], 0.6); ?>; }
    #nav1 div a { padding: 22px 12px 23px 12px; display: inline-block; text-decoration: none; color: #fff; background-color: <?php echo $_COLORS['head']; ?>; margin-top: 1px; line-height: 14px; }
    #nav1 div a:hover { color: <?php echo $_COLORS['hover-color']; ?>; }
    #nav1 div a.aktiv { color: <?php echo $_COLORS['hover-color']; ?>; }
    #nav1 div span.onClickHideShow { cursor: pointer; color: white; background-color: rgba(255,255,255,0.2); padding: 23px 12px 23px 12px; float: right; margin-right: 310px/* annyi, amennyi a #nav1 bal margója!*/; }

    #nav2.vertical { z-index: 1; width: 140px; margin: 0; height: 100%; 
	background-color: <? echo $_COLORS['nav2']; ?>;

	background: -webkit-linear-gradient(top, <? echo $_COLORS['nav2']; ?> 0%,<? echo $_COLORS['nav2']; ?> 50%,#ffffff 100%); 
	background: -moz-linear-gradient(top, <? echo $_COLORS['nav2']; ?> 0%,<? echo $_COLORS['nav2']; ?> 50%,#ffffff 100%); 
	background: linear-gradient(top, <? echo $_COLORS['nav2']; ?> 0%,<? echo $_COLORS['nav2']; ?> 50%,#ffffff 100%); 
	margin-top: -25px;
	padding-top: 25px;
	position: fixed;
	overflow: auto;
	top:83px;
    }
    #nav2.vertical ul { margin: 0; padding: 0; list-style: none; }
    #nav2.vertical ul li { border-bottom: solid 1px rgba(100,100,100,0.4); }
    #nav2.vertical ul li:first-child { border-top: solid 1px rgba(100,100,100,0.4); }
    #nav2.vertical ul li a { display: block; color: black; font-size: 11px; width: 128px; padding: 6px 6px; text-decoration: none; }
    #nav2.vertical ul li a.aktiv { color: #ee7f00; font-size: 12px; }
    #nav2.vertical ul li a:hover { color: white; background-color: #9abcd8; }

    #nav2.horizontal { z-index: 1; width: 100%; 
	margin-top: 0px;
	top: 83px; 
	text-align: center;
	padding-top: 5px; padding-bottom: 5px;
	position: fixed;
	overflow: none;
	border-bottom: solid 3px #eeeeee;
	background-color: #82bfd5;
	color: white;
	font-size:x-small;
    }
    #nav2.horizontal ul { margin: 0; padding: 0; list-style: none; }
    #nav2.horizontal ul li { display: inline; border-right: solid 1px #888888;  }
    #nav2.horizontal ul li:last-child { display: inline; border-right: solid 0px #888888;  }
    #nav2.horizontal ul li a {display: inline; color: white; width: 128px; padding: 0px 6px; text-decoration: none; }
    #nav2.horizontal ul li a.aktiv { color: white; background-color: orange;}
    #nav2.horizontal ul li a:hover { color: white; background-color: orange; }
    #nav2.horizontal:hover { 
	
    }

    div.mayorfoot { text-align: center; font-size:smaller; color: #aaa; }
    div.mayorfoot a { color: rgba(52, 150, 185, 0.6);}
    div.mayorfoot:hover a { color: rgba(52, 150, 185, 1);}
    #mayorfoot.leftpad { margin-top:2em; margin-left: 141px; }
    #mayorfoot.toppad { margin-top: 40px; margin-left: 0px; }

    #takaro { z-index:150;position: fixed;top:0px; left:0px; right:0px; bottom:0px; background-color: rgba(0,0,0,0.7); }
    #updateWindow { z-index: 152; position: fixed; background-color: rgb(255,255,255); min-height: 100px; min-width: 200px; border: <?php echo $_COLORS['head']; ?> 3px solid; border-radius: 10px;}
    #updateWindow #updateHeader { min-height: 20px; min-width: 100px; background-color: #1a4c5c; color: white; text-align: center; line-height: 20px; position: relative; top: -13px; border-radius: 20px; margin: 0 50px; border: solid 3px <?php echo $_COLORS['head']; ?>; }
    #updateWindow #updateCloseButton { position: absolute; right: -20px; top: -20px;  z-index: 0; background-color: white; border: solid 3px <?php echo $_COLORS['head']; ?>; border-radius: 20px;}
    #updateWindow #updateCloseButton:hover {  background-color: #eeeeee;}
    #keyHelp { z-index:151;position: absolute; top:200px; left: 200px; right:200px; margin: auto; padding: 20px; width: 400px; 
	border: solid 1px yellow; border-radius: 20px; border-spacing: 15px; background-color: rgba(0,0,0,0.4);
    }
    #keyHelp th { color: white; text-align: center; border-bottom: yellow 2px solid; font-size: 20px; font-weight: bold; }
    #keyHelp td.key { color: yellow; text-align: right; width: 100px; }
    #keyHelp td.desc { color: white; text-align: left; }

    #logo, #head, #nav1, #nav, #poz { position: fixed; }
    #head { top: 0; left: 0; }
    #nav { top: 0; left: 0; }
    #nav1 { top: 0; }
    #poz { top: 60px; width: 100%; }
    
    #mayorbody { padding-top: 1px; padding-right: 0px; 
		/*margin-left:0px;*/ margin-top: 83px;
    }
    #mayorbody.leftpad { 
		margin-left: 141px; }
    #mayorbody.toppad  { 
		margin-left: 0px; top:33px; position: relative;
    }

}
@media print {
    #nav, #nav1, #nav2, #poz, #head, #logo, #mayorfoot, #logobadge { display: none; }
}
