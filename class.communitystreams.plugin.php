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

  public function ProfileController_CommunityStreams_Create($Sender, $Args) {
    $Args = $Sender->RequestArgs;
    if(sizeof($Args) < 2)
      $Args = array_merge($Args, array(0, 0));
    elseif(sizeof($Args) > 2)
      $Args = array_slice($Args, 0, 2);

    list($UserReference, $Username) = $Args;
    $Sender->Permission('Garden.SignIn.Allow');
    $Sender->GetUserInfo($UserReference, $Username);
    $UserPrefs = Gdn_Format::Unserialize($Sender->User->Preferences);
    if(!is_array($UserPrefs))
      $UserPrefs = array();

    $Validation = new Gdn_Validation();
    $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
    $ConfigArray = array(
        'Plugin.Signatures.Sig' => NULL,
        'Plugin.Signatures.HideAll' => NULL,
        'Plugin.Signatures.HideImages' => NULL,
        'Plugin.Signatures.ShowFirst' => NULL
    );
    $SigUserID = $ViewingUserID = Gdn::Session()->UserID;

    if($Sender->User->UserID != $ViewingUserID) {
      $Sender->Permission('Garden.Users.Edit');
      $SigUserID = $Sender->User->UserID;
    }

    $Sender->SetData('Plugin-Signatures-ForceEditing', ($SigUserID == Gdn::Session()->UserID) ? FALSE : $Sender->User->Name);

    $UserMeta = $this->GetUserMeta($SigUserID, '%');

    if($Sender->Form->AuthenticatedPostBack() === FALSE && is_array($UserMeta))
      $ConfigArray = array_merge($ConfigArray, $UserMeta);

    $ConfigurationModel->SetField($ConfigArray);

    // Set the model on the form.
    $Sender->Form->SetModel($ConfigurationModel);

    // If seeing the form for the first time...
    if($Sender->Form->AuthenticatedPostBack() === FALSE) {
      // Apply the config settings to the form.
      $Sender->Form->SetData($ConfigurationModel->Data);
    }
    else {
      $Values = $Sender->Form->FormValues();
      $FrmValues = array_intersect_key($Values, $ConfigArray);
      if(sizeof($FrmValues)) {
        foreach($FrmValues as $UserMetaKey => $UserMetaValue) {
          $this->SetUserMeta($SigUserID, $this->TrimMetaKey($UserMetaKey), $UserMetaValue);
        }
      }

      $Sender->StatusMessage = T("Your changes have been saved.");
    }

    $Sender->Render($this->GetView('settings.php'));
  }

  public function PluginController_CommunityStreams_Create($Sender) {
    // Makes it act like a mini controller
    $this->Dispatch($Sender, $Sender->RequestArgs);
  }

  public function Controller_Index($Sender) {
    // Display all the streams
    echo T('Plugins.CommunityStreams.SadTruth');
    echo "\nPlugin Index: " . $this->GetPluginIndex();
    echo "\nPlugin Folder: " . $this->GetPluginFolder();
  }

  public function Controller_Details($Sender, $Args) {
    // Show a specific stream details
  }

  public function Base_Render_Before($Sender) {
    $this->_AddResources($Sender);
  }

  private function _AddResources($Sender) {
    $Sender->AddJsFile($this->GetResource('js/communitystreams.js', FALSE, FALSE));
    $Sender->AddCssFile($this->GetResource('design/communitystreams.css', FALSE, FALSE));
  }

  public function Setup() {
    // SaveToConfig('Plugins.CommunityStreams.EnableAdvancedMode', TRUE);
  }

  public function OnDisable() {
    // RemoveFromConfig('Plugins.CommunityStreams.EnableAdvancedMode');
  }

}
