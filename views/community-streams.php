<?php if (!defined('APPLICATION')) exit();
echo Wrap($this->Title, 'h1');

$Streams = $this->Data('CommunityStreams');
    $String = '';
    foreach($Streams as $Stream) {
      
      switch($Stream->Service) {
        case 'twitch':
          $Url = 'http://www.twitch.tv/' . $Stream->AccountID;
          break;
        case 'justin':
          $Url = 'http://www.justin.tv/' . $Stream->AccountID;
          break;
        default:
          $Url = '/';
          break;
      }
      
      
      $Class = 'Offline';
      if($Stream->Online) {
        $Class = 'Online';
      }

      $String .= Wrap(
              Anchor(
                      Img($Stream->Photo, array('title' => ' ')), '/plugin/communitystreams/details/' . $Stream->StreamID, array('title' => $Stream->Username)), 'li', array(
                          'data-account' => $Stream->AccountID,
                          'data-service' => $Stream->Service,
                          'data-uid' => $Stream->UserID,
                          'data-cache-date' => gmdate('c', strtotime($Stream->DateUpdated)),
                          'class' => $Class));
    }

    $String = Wrap($String, 'ul', array('id' => 'CommunityStreamers'));

echo $String;