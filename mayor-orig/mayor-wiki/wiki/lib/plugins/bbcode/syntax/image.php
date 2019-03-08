<?php
/**
 * BBCode plugin: allows BBCode markup familiar from forum software
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <esther@kaffeehaus.ch>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_bbcode_image extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'substition'; }
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('\[img.+?\[/img\]',$mode,'plugin_bbcode_image'); }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        $match = trim(substr($match, 5, -6));
        $match = preg_split('/\]/u',$match,2);
        if ( !isset($match[0]) ) {
            $url   = $match[1];
            $title = NULL;
        } else {
            $url   = $match[0];
            $title = $match[1];
        }
        
        // Check whether this is a local or remote image
        if ( preg_match('#^(https?|ftp)#i',$url) ) {
            $call = 'externalmedia';
        } else {
            $call = 'internalmedia';
        }
        
        $handler->_addCall($call,array($url,$title,NULL,NULL,NULL,'cache'),$pos);
        return true;
    }
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        return true;
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:     
