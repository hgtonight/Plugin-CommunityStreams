<?php if (!defined('APPLICATION')) exit();
/* Copyright 2014 Zachary Doll */

class StreamChatModule extends Gdn_Module {
   
   protected $_Stream;
   
   public function __construct($Stream = FALSE, $Sender = '') {
      $this->_Stream = $Stream;
      parent::__construct($Sender);
   }
   
   public function AssetTarget() {
      return 'Panel';
   }

   public function ToString() {
      if ($this->_Stream == FALSE) {
         return '';
      }
      else {
        switch($this->_Stream->Service) {
          case 'twitch':
            return '<iframe frameborder="0" scrolling="no" id="chat_embed" src="https://twitch.tv/chat/embed?channel='
                    . $this->_Stream->AccountID
                    . '&popout_chat=true" height="500" width="300"></iframe>';
          case 'justin':
            return '<iframe allowtransparency="true" frameborder="0" scrolling="no" id="chat_embed" src="http://www.justin.tv/chat/embed?channel='
                    . $this->_Stream->AccountID
                    . '&amp;default_chat=jtv&amp;#r=-rid-&amp;s=em"height="500" width="300"></iframe>';
          default:
            return '';
        }
      }
   }
}
