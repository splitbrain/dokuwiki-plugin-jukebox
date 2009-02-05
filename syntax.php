<?php
/**
 * Embed a flash Jukebox
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_jukebox extends DokuWiki_Syntax_Plugin {
    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 301;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{jukebox>[^}]*\}\}',$mode,'plugin_jukebox');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        global $ID;
        $match = substr($match,10,-2); //strip markup from start and end

        $data = array();

        // extract params
        list($ns,$params) = explode(' ',$match,2);
        $ns = trim($ns);

        // namespace (including resolving relatives)
        $data['ns'] = resolve_id(getNS($ID),$ns);
        $data['skin'] = 'original';

        $data['skin'] = $params;


        list($data['skin'],$data['width'],$data['height']) = $this->_skininfo($data['skin']);

        return $data;
    }

    /**
     * Create output
     */
    function render($mode, &$R, $data) {
        if($mode != 'xhtml') return false;

        $att = array();

        $params = array(
            'skin_url'     => DOKU_REL.'lib/plugins/jukebox/skins/'.$data['skin'].'/',
            'playlist_url' => DOKU_REL.'lib/plugins/jukebox/list.php?ns='.$data['ns'].'&t=',
            'mainurl'      => 'http://www.dokuwiki.org/plugin:jukebox',
            'infourl'      => 'http://www.dokuwiki.org/plugin:jukebox',
            'autoload'     => 'true',
            'findImage'    => 'true',
            'useId3'       => 'true'
        );

        $swf = DOKU_REL.'lib/plugins/jukebox/xspf_jukebox.swf';

        $R->doc .= html_flashobject($swf,$data['width'],$data['height'],null,$params,$att);
        return true;
    }

    function _skininfo($skin){
        $skin = strtolower($skin);
        $skin = preg_replace('/[^a-z]+/','',$skin);
        if(!$skin) $skin = 'original';

        $data = @file_get_contents(dirname(__FILE__).'/skins/'.$skin.'/skin.xml');
        if(preg_match('/<width>(\d+)<\/width>/',$data,$match)){
            $width = $match[1];
        }else{
            $width = 400;
        }
        if(preg_match('/<height>(\d+)<\/height>/',$data,$match)){
            $height = $match[1];
        }else{
            $height = 170;
        }

        return array($skin,$width,$height);
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
