<?php if(!defined('APPLICATION')) exit();
/* Copyright 2014 Zachary Doll */
class CommunityStreamsModel extends VanillaModel {

  public function __construct() {
    parent::__construct('Stream');
  }
  
  public function GetByUserID($UserID, $DatasetType = FALSE) {
    $Result = $this->GetWhere(array("UserID" => $UserID))->FirstRow($DatasetType);
    return $Result;
  }
  
  public function UpdateStatus($UserID, $Status = 0) {
    $Result = $this->SQL
            ->Update('Stream')
            ->Set('Online', $Status)
            ->Set('DateUpdated', date(DATE_ISO8601))
            ->Where('UserID', $UserID)
            ->Put();
    return $Result;
  }

}
