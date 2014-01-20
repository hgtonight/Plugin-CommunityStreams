<?php if (!defined('APPLICATION')) exit(); ?>
<h2><?php echo T('Stream Settings'); ?></h2>

<?php
$Streams = $this->Data('StreamList');

echo $Streams;