<?php if (!defined('APPLICATION')) exit();
$Stream = $this->Data('Stream');

echo Wrap($this->Title, 'h1');

echo '<object type="application/x-shockwave-flash" height="378" width="620" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel='
        . $Stream->AccountID
        . '" bgcolor="#000000">'
        . '<param name="allowFullScreen" value="true" />'
        . '<param name="allowScriptAccess" value="always" />'
        . '<param name="allowNetworking" value="all" />'
        . '<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />'
        . '<param name="flashvars" value="hostname=www.twitch.tv&channel=' . $Stream->AccountID . '&auto_play=true&start_volume=25" />'
        . '</object>';
