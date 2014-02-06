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
   * Create a settings page to input API keys
   * 
   * @param type $Sender
   */
  public function SettingsController_CommunityStreams_Create($Sender) {
    if (property_exists($Sender, 'Menu')) {
      $Sender->Menu->HighlightRoute('/settings/communitystreams');
    }
    $Sender->AddSideMenu('settings/communitystreams');

    $Sender->Permission('Garden.Settings.Manage');

    $ConfigModule = new ConfigurationModule($Sender);

    $ConfigModule->Initialize(array(
      'Plugins.CommunityStreams.TwitchAPIKey' => array(
        'LabelCode' => 'Twitch.tv API Key',
        'Control'   => 'Textbox'
      ),
      'Plugins.CommunityStreams.JustinAPIKey' => array(
        'LabelCode' => 'Justin.tv API Key',
        'Control'   => 'Textbox'
      )
    ));
    $Sender->Title(T('Plugins.CommunityStreams.Settings'));
    $Sender->ConfigurationModule = $ConfigModule;

    $ConfigModule->RenderAll();
  }
  
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
	$Session = Gdn::Session();
	//decho($Session);
	
    $UserReference = GetValue(0, $Args, 0);
    $Username = GetValue(1, $Args, ' ');
	
	// default to the signed in user
	if($UserReference == 0 && $Username == ' ') {
		$UserReference = $Session->UserID;;
		$Username = $Session->User->Name;
	}

    $Sender->Permission('Garden.SignIn.Allow');
    $Sender->GetUserInfo($UserReference, $Username);

    $StreamModel = new CommunityStreamsModel();
    // Set the model on the form.
    $Sender->Form->SetModel($StreamModel);

    $ViewingUserID = $Session->UserID;
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
    $Sender->MasterView = '';
    $Sender->RemoveCssFile('admin.css');
    $Sender->AddCssFile('style.css');
    $this->_AddResources($Sender);
    
    $Sender->AddDefinition('TwitchAPIKey', C('Plugins.CommunityStreams.TwitchAPIKey', FALSE));
    $Sender->AddDefinition('JustinAPIKey', C('Plugins.CommunityStreams.JustinAPIKey', FALSE));
    
    // Use this to reconcile any timezone differences
    $Sender->AddDefinition('CurrentServerDateTime', date(DATE_ISO8601));
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

    $Streams = $StreamModel->Get('Online', 'desc')->Result();

    $Sender->SetData('CommunityStreams', $Streams);
    $Sender->Title = T('Community Streams');
    $Sender->Render($this->GetView('community-streams.php'));
  }

  public function Controller_Details($Sender) {
    $StreamID = GetValue(1, $Sender->RequestArgs, FALSE);

    if(!$StreamID) {
      throw NotFoundException('Stream');
    }
    
    // Show a specific stream details
    $StreamModel = new CommunityStreamsModel();
    $Stream = $StreamModel->GetID($StreamID);

    if(!$Stream->StreamID) {
      throw NotFoundException('Stream');
    }
    $UserModel = new UserModel();
    $User = $UserModel->GetID($Stream->UserID);
    $Sender->SetData('Stream', $Stream);
    $Sender->Title = $User->Name . ' ' . T('\'s Stream');

    // Add the chat module
    $Module = new StreamChatModule($Stream);
    $Sender->AddModule($Module);
        
    switch($Stream->Service) {
      case 'twitch':
        $Sender->Render($this->GetView('twitch-details.php'));
        break;
      case 'justin':
        $Sender->Render($this->GetView('justin-details.php'));
        break;
      default:
        throw NotFoundException('Stream Service');
    }
  }

  /**
   * Called by JS to update the cache time and status of users
   *
   * @param type $Sender
   * @param type $Args
   */
  public function Controller_Update($Sender) {
    $Targs = $Sender->RequestArgs;
    $ForeignKey = GetValue(1, $Targs, FALSE);
    $Session = Gdn::Session();
    if(!$Session->ValidateTransientKey($ForeignKey, FALSE)) {
      throw new Gdn_UserException(T('Invalid Session'));
    }
    
    $Args = $Sender->Request;
    $UserID = $Args->GetValue('userid', -1);
    $Status = $Args->GetValue('online', NULL);
    $Photo = $Args->GetValue('photo', NULL);
    
    $Result = FALSE;
    if(!is_null($Status) && is_numeric($UserID) && $UserID > 0) {
      $StreamModel = new CommunityStreamsModel();
      $StreamModel->UpdateStream($UserID, $Status, $Photo);
      $Result = TRUE;
    }
    
    // Pass the result and the server's time back to keep everything in sync
    $Sender->RenderData(array('Result' => $Result, 'DateTime' => date(DATE_ISO8601)));
  }

  private function _AddResources($Sender) {
    $Sender->AddJsFile($this->GetResource('js/twitch.js', FALSE, FALSE));
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
            ->Column('Online', 'tinyint(1)', 0)
            ->Column('Photo', 'varchar(255)', TRUE)
            ->Column('DateUpdated', 'datetime', '1970-01-01 00:00:01')
            ->Column('Sort', 'int', TRUE)
            ->Set();
  }

  public function OnDisable() {
    // RemoveFromConfig('Plugins.CommunityStreams.EnableAdvancedMode');
  }

}
