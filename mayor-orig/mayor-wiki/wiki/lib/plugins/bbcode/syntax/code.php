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
class syntax_plugin_bbcode_code extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'protected'; }
    function getPType() { return 'block'; }
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('\[code\](?=.*?\x5B/code\x5D)',$mode,'preformatted'); }
    function postConnect() { $this->Lexer->addExitPattern('\[/code\]','preformatted'); }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
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
