<?php if(!defined('APPLICATION')) exit();
/* 	Copyright 2014 Zachary Doll
 * 	This program is free software: you can redistribute it and/or modify
 * 	it under the terms of the GNU General Public License as published by
 * 	the Free Software Foundation, either version 3 of the License, or
 * 	(at your option) any later version.
 *
 * 	This program is distributed in the hope that it will be useful,
 * 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * 	GNU General Public License for more details.
 *
 * 	You should have received a copy of the GNU General Public License
 * 	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$PluginInfo['CommunityStreams'] = array(
    'Name' => 'Community Streams',
    'Description' => 'Adds a community streams page showcasing your communities streaming content from Twitch.tv and Justin.tv. It allows users to add their streaming account via their profile.',
    'Version' => '0.1',
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => FALSE,
    'MobileFriendly' => TRUE,
    'HasLocale' => TRUE,
    'RegisterPermissions' => FALSE,
    'SettingsUrl' => '/settings/communitystreams',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'Author' => 'Zachary Doll',
    'AuthorEmail' => 'hgtonight@daklutz.com',
    'AuthorUrl' => 'http://www.daklutz.com',
    'License' => 'GPLv3'
);

class CommunityStreams extends Gdn_Plugin {

  /**
   * Adds a link to the streams settings page on the profile
   * 
   * @param object $Sender
   */
  public function ProfileController_AfterAddSideMenu_Handler($Sender) {
    $SideMenu = $Sender->EventArguments['SideMenu'];
    $Session = Gdn::Session();
    $ViewingUserID = $Session->UserID;

    if($Sender->User->UserID == $ViewingUserID) {
      $SideMenu->AddLink('Options', T('Stream Settings'), '/profile/communitystreams', FALSE, array('class' => 'Popup'));
    }
    else {
      $SideMenu->AddLink('Options', T('Stream Settings'), '/profile/communitystreams/' . $Sender->User->UserID . '/' . Gdn_Format::Url($Sender->User->Name), 'Garden.Users.Edit', array('class' => 'Popup'));
    }
  }

  /**
   * Renders the profile settings page on the profile
   * 
   * @param object $Sender
   * @param array $Args
   */
  public function ProfileController_CommunityStreams_Create($Sender, $Args) {
    $Args = $Sender->RequestArgs;
    $UserReference = GetValue(0, $Args, 0);
    $Username = GetValue(1, $Args, ' ');

    $Sender->Permission('Garden.SignIn.Allow');
    $Sender->GetUserInfo($UserReference, $Username);

    $StreamModel = new CommunityStreamsModel();
    // Set the model on the form.
    $Sender->Form->SetModel($StreamModel);

    $ViewingUserID = Gdn::Session()->UserID;
    $EditingUserID = $Sender->User->UserID;
    if($EditingUserID != $ViewingUserID) {
      $Sender->Permission('Garden.Users.Edit');
      $UserID = $Sender->User->UserID;
    }
    else {
      $UserID = $ViewingUserID;
    }

    // Add the data needed by the view and form
    $Sender->SetData('Plugin-CommunityStreams-ForceEditing', ($UserID == $ViewingUserID) ? FALSE : $Sender->User->Name);
    $Sender->Form->AddHidden('UserID', $UserID);
    
    // Get any existing stream data and add the stream id to the form
    $Stream = $StreamModel->GetByUserID($UserID);
    if($Stream) {
      $Sender->Form->AddHidden('StreamID', $Stream->StreamID);
    }
    
    // If seeing the form for the first time...
    if($Sender->Form->AuthenticatedPostBack() === FALSE) {
      // Apply the config settings to the form.
      $Sender->Form->SetData($Stream);
    }
    else {
      if($Sender->Form->Save()) {
        $Sender->StatusMessage = T('Your changes have been saved.');
      }
    }

    $Sender->Render($this->GetView('profile-settings.php'));
  }

  /**
   * Create a 'controller' on the plugin controller
   * 
   * @param type $Sender
   */
  public function PluginController_CommunityStreams_Create($Sender) {
    $this->_AddResources($Sender);
    // Makes it act like a mini controller
    $this->Dispatch($Sender, $Sender->RequestArgs);
  }

  /**
   * This outputs the stored stated of the db and uses ajax calls to update the
   * stored state
   * 
   * @param object $Sender
   */
  public function Controller_Index($Sender) {
    // Get All the stream info all the streams
    $StreamModel = new CommunityStreamsModel();
    
    $Streams = $StreamModel->Get()->Result();
    //decho($Streams);
    
    $String = '';
    foreach($Streams as $Stream) {
      /*$Users = array(
		'archerv2' => 'twitch',
		'drlegitimate' => 'twitch',
		'quiltedvino' => 'twitch',
		'oliveversiongardentwo' => 'twitch',
		'cherrydoom' => 'justin',
		'blackflag89347' => 'justin',
		'truktruk' => 'twitch',
		'barret80' => 'twitch',
	);*/
      switch($Stream->Service) {
        case 'twitch':
          $Link = 'http://www.twitch.tv/' . $Stream->AccountID;
          break;
        case 'justin':
          $Link = 'http://www.justin.tv/' . $Stream->AccountID;
          break;
        default:
          $Link = ltrim(GetValue('Destination', Gdn::Router()->GetRoute('DefaultController'), ''), '/');
          break;
      }

        $String .= Wrap(
                Anchor($Stream->UserID, $Link) .
                $Stream->AccountID, 'li', array('data-service' => $Stream->Service, 'class' => $Stream->Online));
	}
  
  $String = Wrap($String, 'ul', array('class' => 'CommunityStreamers'));
  
  $Sender->SetData('StreamList', $String);
  $Sender->Render($this->GetView('community-streams.php'));
	
    }

  public function Controller_Details($Sender, $Args) {
    // Show a specific stream details
  }

  public function Base_Render_Before($Sender) {
    $this->_AddResources($Sender);
  }

  private function _AddResources($Sender) {
    $Sender->Head->AddScript('https://ttv-api.s3.amazonaws.com/twitch.min.js');
    $Sender->AddJsFile($this->GetResource('js/communitystreams.js', FALSE, FALSE));
    $Sender->AddCssFile($this->GetResource('design/communitystreams.css', FALSE, FALSE));
  }

  public function Setup() {
    SaveToConfig('Plugins.CommunityStreams.TwitchAPIClientID', FALSE);
    $this->Structure();
  }

  public function Structure() {
    $Database = Gdn::Database();
    $Construct = $Database->Structure();

    $Construct->Table('Stream');
    $Construct
            ->PrimaryKey('StreamID')
            ->Column('UserID', 'int', FALSE)
            ->Column('Service', array('justin', 'twitch'), TRUE)
            ->Column('AccountID', 'varchar(255)', TRUE)
            ->Column('Online', 'tinyint(1)', FALSE)
            ->Column('Photo', 'varchar(255)', TRUE)
            ->Set();
  }
  
  public function OnDisable() {
    // RemoveFromConfig('Plugins.CommunityStreams.EnableAdvancedMode');
  }

}
