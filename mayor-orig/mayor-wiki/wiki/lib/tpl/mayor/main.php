<?php
/**
 * DokuWiki Default Template
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Riccardo Govoni <battlehorse@gmail.com>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/* Creates the URL of the current page, used for Digg, delicious and google bookmarks */
function selfURL() { 
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
} 

function strleft($s1, $s2) { 
	return substr($s1, 0, strpos($s1, $s2)); 
}

// verify if the given action is enabled
function is_action_enabled($type) {	
	$ctype = $type;
	if($type == 'history') $ctype='revisions';
	return actionOK($ctype);
}

// changes the display style of the given action group, depending on the config file
function action_group_status($groupname) {
	if (tpl_getConf('btl_default_' . $groupname . '_actions_status') == "closed") {
		echo " style='display:none;'" ; 
	}
}

// include functions that provide sidebar functionality
@require_once(dirname(__FILE__).'/tplfn_sidebar.php');

// include translations of the template strings
@require_once(dirname(__FILE__).'/lang/'.tpl_getConf('btl_language').'/settings.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    <?php tpl_pagetitle()?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />

  <?php /*old includehook*/ @include(dirname(__FILE__).'/meta.html')?>

	<script src="<?php echo DOKU_TPL ?>js/prototype.js" type="text/javascript"></script>
	<script src="<?php echo DOKU_TPL ?>js/scriptaculous.js" type="text/javascript"></script>
</head>

<body>
<?php /*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>
<div class="dokuwiki">
  <?php html_msgarea()?>

  <div class="stylehead">

    <div class="header">
<!--      <div class="pagename">
        [[<?php tpl_link(wl($ID,'do=backlink'),tpl_pagetitle($ID,true))?>]]
      </div> -->
      <div class="logo">
        <?php tpl_link(wl(),$conf['title'],'name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[ALT+H]"')?>
      </div>

      <div class="clearer"></div>
    </div>

    <?php /*old includehook*/ @include(dirname(__FILE__).'/header.html')?>

    <?php if($conf['breadcrumbs']){?>
    <div class="bread_upper_dark"></div>
    <div class="breadcrumbs">
      <?php tpl_breadcrumbs()?>
      <?php //tpl_youarehere() //(some people prefer this)?>
    </div>
    <div class="bread_lower_dark"></div>
    <div class="bread_lower_medium"></div>
    <div class="bread_lower_light">&nbsp;</div>
    <?php }?>

    <?php if($conf['youarehere']){?>
    <div class="bread_upper_dark"></div>
    <div class="breadcrumbs">
      <?php tpl_youarehere() ?>
    </div>
    <div class="bread_lower_dark"></div>
    <div class="bread_lower_medium"></div>
    <div class="bread_lower_light"></div>
    <?php }?>

  </div>
  <?php flush()?>

  <?php /*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>

<div class="sideandpage" >
	<?php if (tpl_getConf('btl_sidebar_position') == "right") { ?>
	      <div class="mainleft" >
	        <div class="page">
	          <!-- wikipage start -->
	          <?php tpl_content()?>
	          <!-- wikipage stop -->
	        </div>
	        <div class="page_lower_dark"></div>
	        <div class="page_lower_medium"></div>
	        <div class="page_lower_light"></div> 
	     </div>
	<?php } ?>

	<?php if (tpl_getConf('btl_sidebar_position') == "right") { ?>
		<div class="sideright">
	<?php } else { ?>
		<div class="sideleft">
	<?php } ?>
		
	<div class="sidebarandshadows" >
		<table cellspacing="0" cellpadding="0" border="0" width="100%" >
			<tr>
				<td>
					<table  cellspacing="0" cellpadding="0" border="0" width="100%">
						<tr>
						<td valign="top">
							<div class="sidebar">
							<?php tpl_sidebar()?>
							</div>
						</td>
						</tr>
					</table>
				</td>
				<td class="page_lower_dark" style="width: 1px"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_dark" style="height: 1px" colspan="2"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_medium" style="height: 1px" colspan="3"></td>        
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_light" style="height: 1px" colspan="4"></td>        
			</tr>						
		</table>
	</div> 

	<?php  if (is_action_enabled('search')) { ?>
	<div class="searchbarandshadows" >
		<table cellspacing="0" cellpadding="0" border="0" width="100%" >
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" width="100%" >
						<tr>
						<td valign="top">
						   <div class="searchbar">
						   <div class="centeralign"><?php tpl_searchform()?></div>
						   </div>
						</td>
						</tr>
					</table>
				</td>
				<td class="page_lower_dark" style="width: 1px"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_dark" style="height: 1px" colspan="2"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_medium" style="height: 1px" colspan="3"></td>        
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_light" style="height: 1px" colspan="4"></td>        
			</tr>						
		</table>
	</div>
	<?php } ?>

	<div class="userbarandshadows" >
		<table cellspacing="0" cellpadding="0" border="0" width="100%" >
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0" width="100%">
						<tr>
						<td valign="top">
						  <div class="userbar" >
		<?php if (tpl_getConf('btl_action_palette') && (is_action_enabled('history') || is_action_enabled('backlink') || (is_action_enabled('edit') && (!$conf['useacl'] || $ACT != 'show' || ($conf['useacl'] && $_SERVER['REMOTE_USER']))))) { ?>
		<div class="userbarstrip" onclick="Effect.toggle('pageActionTableId','slide')"><?php echo $lang['btl_strip_page_actions']; ?></div>
		<?php } ?>
		<div id="pageActionTableId" <?php action_group_status('page'); ?> ><div>
                <table cellspacing="0" cellpadding="2" border="0" width="100%" >
                  <tr><td>
							<?php if (is_action_enabled('history')) { ?>
						        <div class="smallpadding"><?php tpl_actionlink('history')?></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('backlink')) { ?>
      							<div class="smallpadding"><?php tpl_actionlink('backlink')?></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('edit')) { ?>
      							<div class="smallpadding"><?php tpl_actionlink('edit')?></div></td></tr><tr><td>
							<?php } ?>
		</td></tr></table></div></div>
		<?php if (tpl_getConf('btl_action_palette') && (is_action_enabled('index') || is_action_enabled('recent') || (is_action_enabled('admin') && $INFO['perm'] == 255 ))) { ?>
		<div class="userbarstrip" onclick="Effect.toggle('wikiActionTableId','slide')"><?php echo $lang['btl_strip_wiki_actions']; ?></div>
		<?php } ?>
		<div id="wikiActionTableId" <?php action_group_status('wiki'); ?> ><div>
                <table cellspacing="0" cellpadding="2" border="0" width="100%" >
			<tr><td>
							<?php if (is_action_enabled('index')) { ?>
						        <div class="smallpadding"><?php tpl_actionlink('index')?></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('recent')) { ?>
      							<div class="smallpadding"><?php tpl_actionlink('recent')?></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('admin') && $INFO['perm'] == 255) { ?>
								<div class="smallpadding"><?php tpl_actionlink('admin') ?></div></td></tr><tr><td>
							<?php } ?>
		</td></tr></table></div></div>
		<?php if (tpl_getConf('btl_action_palette') && (is_action_enabled('login') || (is_action_enabled('profile') && $_SERVER['REMOTE_USER'] ) || (is_action_enabled('subscribe') && $conf['useacl'] && $ACT == 'show' && $conf['subscribers'] == 1 && $_SERVER['REMOTE_USER']))) { ?>
		<div class="userbarstrip" onclick="Effect.toggle('userActionTableId','slide')"><?php echo $lang['btl_strip_user_actions']; ?></div>
		<?php } ?>
		<div id="userActionTableId" <?php action_group_status('user'); ?> ><div>
                <table cellspacing="0" cellpadding="2" border="0" width="100%" >
			<tr><td>
							<?php if (is_action_enabled('login')) { ?>
				                <div class="smallpadding"><?php tpl_actionlink('login')?>
								<?php 
									if ($_SERVER['REMOTE_USER']){
										echo $INFO['userinfo']['name'] ; 
									}
								?>
								</div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('profile') && $_SERVER['REMOTE_USER']) { ?>
								<div class="smallpadding"><?php tpl_actionlink('profile') ?></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('subscribe')) { ?>
								<?php if($conf['useacl'] && $ACT == 'show' && $conf['subscribers'] == 1 && $_SERVER['REMOTE_USER']){ ?>
									<div class="smallpadding"><?php tpl_actionlink('subscribe')?></div></td></tr><tr><td>
								<?php } ?>
							<?php } ?>
		</td></tr></table></div></div>
		<?php if (tpl_getConf('btl_action_palette') && (is_action_enabled('digg') || is_action_enabled('delicious') || is_action_enabled('googlebookmark'))) { ?>
		<div class="userbarstrip" onclick="Effect.toggle('submitActionTableId','slide')"><?php echo $lang['btl_strip_submit_actions']; ?></div>
		<?php } ?>
		<div id="submitActionTableId" <?php action_group_status('submit'); ?> ><div>
                <table cellspacing="0" cellpadding="2" border="0" width="100%" >
			<tr><td>
							<?php if (is_action_enabled('digg')) { ?>
							    <div class="smallpadding"><a class="digg" href="<?php echo 'http://digg.com/submit?phase=2&amp;url='.urlencode(selfURL()).'&amp;title='?><?php echo urlencode(tpl_pagetitle())?>">Digg this!</a></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('delicious')) { ?>
							    <div class="smallpadding"><a class="delicious" href="<?php echo 'http://del.icio.us/post?url='.urlencode(selfURL()).'&amp;title='?><?php echo urlencode(tpl_pagetitle())?>">Del.Icio.Us</a></div></td></tr><tr><td>
							<?php } ?>
							<?php if (is_action_enabled('googlebookmark')) { ?>
							    <div class="smallpadding"><a class="googlebookmark" href="<?php echo 'http://www.google.com/bookmarks/mark?op=add&amp;bkmk='.urlencode(selfURL()).'&amp;title='?><?php echo urlencode(tpl_pagetitle())?>">Google bookmark</a></div></td></tr><tr><td>
							<?php } ?>
							</td></tr>
                </table></div></div>

						  </div>
						</td>
						</tr>
					</table>
				</td>
				<td class="page_lower_dark" style="width: 1px"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_dark" style="height: 1px" colspan="2"></td>
				<td class="page_lower_medium" style="width: 1px"></td>
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_medium" style="height: 1px" colspan="3"></td>        
				<td class="page_lower_light" style="width: 1px"></td>
			</tr>
			<tr>
				<td class="page_lower_light" style="height: 1px" colspan="4"></td>        
			</tr>						
		</table>
	</div> 

      </div>
	<?php if (tpl_getConf('btl_sidebar_position') == "left") { ?>
	      <div class="mainright" >
	        <div class="page">
        	  <!-- wikipage start -->
	          <?php tpl_content()?>
	          <!-- wikipage stop -->
        	</div>
	        <div class="page_lower_dark"></div>
	        <div class="page_lower_medium"></div>
	        <div class="page_lower_light"></div> 
	     </div>
	<?php } ?> 
      <div class="clearer">&nbsp;</div>
</div>

 <?php flush()?>

  <div class="stylefoot">

    <div class="meta">
      <div class="user">
        <?php tpl_userinfo()?>
      </div>
      <div class="doc">
        <?php tpl_pageinfo()?> &nbsp;
		<span class="doclink">
			&nbsp;
	        <?php tpl_actionlink('top') ?>
		</span>
      </div>
    </div>

   <?php /*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

    <div class="bar" id="bar__bottom">

     <?php /*old includehook*/ @include(dirname(__FILE__).'/footer.html')?>
     
     <div class="clearer"></div>
    </div>
    
  </div>

</div>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>
</body>
</html>
