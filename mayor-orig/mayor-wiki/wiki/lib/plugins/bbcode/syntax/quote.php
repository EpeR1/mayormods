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
class syntax_plugin_bbcode_quote extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'container'; }
    function getPType() { return 'block'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled', 'protected'); }   
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('\[quote.*?\](?=.*?\x5B/quote\x5D)',$mode,'plugin_bbcode_quote'); }
    function postConnect() { $this->Lexer->addExitPattern('\[/quote\]','plugin_bbcode_quote'); }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {        
        switch ($state) {
          case DOKU_LEXER_ENTER :
            $match = explode('"',substr($match, 6, -1));
            return array($state, $match[1]);
 
          case DOKU_LEXER_UNMATCHED :
            return array($state, $match);
            
          case DOKU_LEXER_EXIT :
            return array($state, '');
            
        }
        return array();
    }
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml') {
            list($state, $match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :
                if ($match !== '') $renderer->doc .= '<p><sub>'.$match.':</sub></p>';
                $renderer->doc .= '<blockquote>';
                break;
                
              case DOKU_LEXER_UNMATCHED :
                $match = $renderer->_xmlEntities($match);
                $renderer->doc .= str_replace("\n",'<br />',$match);
                break;
                
              case DOKU_LEXER_EXIT :
                $renderer->doc .= '</blockquote>';
                break;
                
            }
            return true;
        }
        return false;
    }
    
}
// vim:ts=4:sw=4:et:enc=utf-8:     
