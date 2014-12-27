<?php

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_captchalogin extends DokuWiki_Action_Plugin {
    private $captcha_installed =  true;
    function register(&$controller){
        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE',$this,'handle_login_form',array());
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE',  $this, 'handle_act_preprocess');   
    }
    
      /**
        *      Insert captcha into login form 
        *      chk=captcha_check parameter to identify our login with captcha
      */
    function handle_login_form(&$event, $param) {   
    	$pos = $event->data->findElementByAttribute('type', 'submit');      
        $helper = plugin_load('helper', 'captcha');
         if($helper === null) {
            msg('The captchalogin plugin needs the captcha plugin', -1);
            $this->captcha_installed=false;
            return;
        }
        $out    = $helper->getHTML(); 
        $event->data->_hidden['chk'] = 'captcha_check';  
        $event->data->insertElement($pos+1, $out);
    }     
    
    /**
     *   Redirect with additional parameters if captcha fails
     *         do=logout : to force logout
     *         capt=r : to identify on reload that the captcha has failed
     *  Output captcha plugin's 'testfailed' message if capatcha failed test                
    */
   function handle_act_preprocess(&$event, $param) {         
          if(!$this->captcha_installed) return;
          if(isset($_REQUEST['capt']) && $_REQUEST['capt'] == 'r') {
               $captcha = $this->loadHelper('captcha', true);
                msg($captcha->getLang('testfailed'), -1);
          }
          if(isset($_REQUEST['chk'])) {        
                $captcha = $this->loadHelper('captcha', true);
                 if(!$captcha->check()) {
                  $url = DOKU_URL . 'doku.php?&do=logout&capt=r';            
                 header("Location: $url");
                  exit();
           }
        }
     
   }

}
