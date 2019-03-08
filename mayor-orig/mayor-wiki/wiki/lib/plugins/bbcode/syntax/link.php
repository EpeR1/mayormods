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
class syntax_plugin_bbcode_link extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'substition'; }
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('\[url.+?\[/url\]',$mode,'plugin_bbcode_link'); }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        $match = substr($match, 5, -6);
        if (preg_match('/".+?"/',$match)) $match = substr($match, 1, -1); // addition #1: unquote
        $match = preg_split('/\]/u',$match,2);
        if ( !isset($match[0]) ) {
            $url   = $match[1];
            $title = NULL;
        } else {
            $url   = $match[0];
            $title = $match[1];
        }
        
        // external link (accepts all protocols)
        if ( preg_match('#^([a-z0-9\-\.+]+?)://#i',$url) ) {
            $handler->_addCall('externallink',array($url,$title),$pos);
            
        // local link
        } elseif ( preg_match('!^#.+!',$url) ) {
            $handler->_addCall('locallink',array(substr($url,1),$title),$pos);
                
        // internal link
        } else {
            $handler->_addCall('internallink',array($url,$title),$pos);
        }
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
