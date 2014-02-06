<?php if (!defined('APPLICATION')) exit();
$Stream = $this->Data('Stream');

echo Wrap($this->Title, 'h1');

echo '<object type="application/x-shockwave-flash" data="http://www.justin.tv/swflibs/JustinPlayer.swf?channel='
        . $Stream->AccountID
        . '" id="live_embed_player_flash" height="378" width="620" bgcolor="#000000">'
        . '<param name="allowFullScreen" value="true"/>'
        . '<param name="allowScriptAccess" value="always" />'
        . '<param name="allowNetworking" value="all" />'
        . '<param name="movie" value="http://www.justin.tv/swflibs/JustinPlayer.swf" />'
        . '<param name="flashvars" value="hostname=www.justin.tv&channel=' . $Stream->AccountID . '&auto_play=true&start_volume=25" />'
      . '</object>';