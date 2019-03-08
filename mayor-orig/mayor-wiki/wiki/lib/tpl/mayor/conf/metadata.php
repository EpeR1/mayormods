<?php
/**
 * configuration-manager metadata for the battlehorse template
 * 
 * @author:     Riccardo "battlehorse" Govoni <battlehorse@gmail.com>
 */

$meta['btl_sidebar_position'] = array('multichoice', '_choices' => array('left','right'));
$meta['btl_sidebar_name'] = array('string', '_pattern' => '#^[a-z]*#' ) ; 
$meta['btl_action_palette'] = array('onoff'); 
$meta['btl_default_user_actions_status'] = array('multichoice','_choices' => array('open','closed'));
$meta['btl_default_page_actions_status'] = array('multichoice','_choices' => array('open','closed'));
$meta['btl_default_wiki_actions_status'] = array('multichoice','_choices' => array('open','closed'));
$meta['btl_default_submit_actions_status'] = array('multichoice','_choices' => array('open','closed'));
$meta['btl_language'] = array('multichoice', '_choices' => array('en','hu','it')); 

?>
