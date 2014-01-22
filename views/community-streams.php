<?php if (!defined('APPLICATION')) exit();
echo Wrap($this->Title, 'h1');

$Streams = $this->Data('CommunityStreams');
    $String = '';
    foreach($Streams as $Stream) {
      /* $Users = array(
        'archerv2' => 'twitch',
        'drlegitimate' => 'twitch',
        'quiltedvino' => 'twitch',
        'oliveversiongardentwo' => 'twitch',
        'cherrydoom' => 'justin',
        'blackflag89347' => 'justin',
        'truktruk' => 'twitch',
        'barret80' => 'twitch',
        ); */
      
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
              Anchor($Stream->AccountID, $Url) .
              $Stream->AccountID, 'li', array('data-service' => $Stream->Service, 'data-uid' => $Stream->UserID, 'data-date' => $Stream->DateUpdated, 'class' => $Class));
    }

    $String = Wrap($String, 'ul', array('class' => 'CommunityStreamers'));

echo $String;