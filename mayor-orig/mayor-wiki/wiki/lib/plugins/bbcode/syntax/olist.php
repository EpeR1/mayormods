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
class syntax_plugin_bbcode_olist extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'container'; }
    function getPType() { return 'block'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled', 'protected'); }
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('\[list=.*?\]\s*?\[\*\](?=.*?\x5B/list\x5D)', $mode, 'plugin_bbcode_olist'); }
    function postConnect() { $this->Lexer->addExitPattern('\[/list\]', 'plugin_bbcode_olist'); }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {        
        switch ($state) {
          case DOKU_LEXER_ENTER :
            // get the list type
            $match = substr($match, 6, -4);
            $match = preg_split('/\]/u',$match,2);
            return array($state, $match[0]);
             
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
                switch ($match) {
                  case 'i':
                    $type = 'lower-roman';
                    break;
                  case 'I':
                    $type = 'upper-roman';
                    break;
                  case 'a':
                    $type = 'lower-alpha';
                    break;
                  case 'A':
                    $type = 'upper-alpha';
                    break;
                  default:
                    $type = 'decimal';
                }
                $renderer->doc .= '<ol style="list-style-type:'.$type.'"><li class="level1"><div class="li">';
                break;
                                
              case DOKU_LEXER_UNMATCHED :
                $match = $renderer->_xmlEntities($match);
                $renderer->doc .= str_replace('[*]', '</div></li><li class="level1"><div class="li">', $match);
                break;
                
              case DOKU_LEXER_EXIT :
                $renderer->doc .= '</div></li></ol>';
                break;
                
            }
            return true;
        }
        return false;
    }
    
}
// vim:ts=4:sw=4:et:enc=utf-8:     
