<?php
/**
 * BBCode plugin: allows BBCode markup familiar from forum software
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <esther@kaffeehaus.ch>
 * @author     Luis Machuca Bezzaza <luis.machuca@gulix.cl>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_bbcode_size extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'formatting'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }   
    function getSort() { return 105; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('\[size=.*?\](?=.*?\x5B/size\x5D)',$mode,'plugin_bbcode_size'); }
    function postConnect() { $this->Lexer->addExitPattern('\[/size\]','plugin_bbcode_size'); }
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        switch ($state) {
          case DOKU_LEXER_ENTER :
            $match = substr($match, 6, -1);
            if (preg_match('/".+?"/',$match)) $match = substr($match, 1, -1); // addition #1: unquote
            if (preg_match('/^[0-6]$/',$match)) $match = self::_relsz(intval($match) ); // addition #2: relative size number
            else if (preg_match('/^\d+$/',$match)) $match .= 'px';
            return array($state, $match);
 
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
                $renderer->doc .= '<span style="font-size:'.$renderer->_xmlEntities($match).'">';
                break;
                
              case DOKU_LEXER_UNMATCHED :
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
                
              case DOKU_LEXER_EXIT :
                $renderer->doc .= '</span>';
                break;
                
            }
            return true;
        }
        return false;
    }

    /**
    * @fn      _relsz
    * @brief   Returns a relative-size CSS keyword based on numbering.
    * @author Luis Machuca Bezzaza <luis.machuca@gulix.cl>
    *
    * Provides a mapping to the series of size-related keywords in CSS 2.1
    * (http://www.w3.org/TR/REC-CSS1/#font-size)
    * Valid values are [0-6], with 3 for "medium" (as recommended by standard)
    */
    private function _relsz ($value) {
        switch ($value) {
          case 0:
            return 'xx-small'; break;
          case 1:
            return 'x-small'; break;
          case 2:
            return 'small'; break;
          case 4:
            return 'large'; break;
          case 5:
            return 'x-large'; break;
          case 6:
            return 'xx-large'; break;
          case 3:
            return 'medium'; break;
          default:
            return false; break;
        }
    }

}
// vim:ts=4:sw=4:et:enc=utf-8:     
