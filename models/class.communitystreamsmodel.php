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
  
  public function GetAll() {
    return $this->SQL
            ->Select('s.*, u.Name as Username')
            ->From('Stream s')
            ->LeftJoin('User u', 's.UserID = u.UserID')
            ->OrderBy('s.Online', 'desc')
            ->Get()
            ->Result(DATASET_TYPE_OBJECT);
  }
  
  public function UpdateStream($UserID, $Status = 0, $Photo = NULL) {
    $this->SQL
            ->Update('Stream')
            ->Set('Online', $Status);
    if($Photo) {
      $this->SQL->Set('Photo', $Photo);
    }
    $Result = $this->SQL
            ->Set('DateUpdated', date(DATE_ISO8601))
            ->Where('UserID', $UserID)
            ->Put();
    return $Result;
  }

}
