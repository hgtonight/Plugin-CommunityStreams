<?php if (!defined('APPLICATION')) exit(); ?>
<h2><?php echo T('Stream Settings'); ?></h2>
<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<ul>
   <?php
      if (isset($this->Data['Plugin-CommunityStreams-ForceEditing']) && $this->Data['Plugin-CommunityStreams-ForceEditing'] != FALSE) {
   ?>
         <div class="Warning"><?php echo sprintf(T("You are editing %s's stream preferences"),$this->Data['Plugin-CommunityStreams-ForceEditing']); ?></div>
   <?php
      }
   ?>
   <li>
      <?php
         echo $this->Form->Label('Streaming Service', 'Service');
         echo $this->Form->Dropdown('Service', array('justin' => 'Justin.tv', 'twitch' => 'Twitch.tv'));
      ?>
   </li>
   <li>
      <?php
         echo $this->Form->Label('Streaming Account ID', 'AccountID');
         echo $this->Form->TextBox('AccountID');
      ?>
   </li>
</ul>
<?php echo $this->Form->Close('Save');