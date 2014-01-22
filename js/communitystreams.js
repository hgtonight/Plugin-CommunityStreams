/* Copyright 2014 Zachary Doll */
jQuery(document).ready(function($) {
  var Twitches = [];
  // Find all the streamers that need to be updated
  $("li[data-service='twitch']").each(function() {
    var CurrentDate = new Date();
    var TargetUpdate = new Date(CurrentDate.getTime() - 15 * 60000);
    var DateUpdated = $(this).attr('data-date').split(/[- :]/);
    var CachedDate = new Date(DateUpdated[0], DateUpdated[1] - 1, DateUpdated[2], DateUpdated[3], DateUpdated[4], DateUpdated[5]);

    //if (CachedDate < TargetUpdate) {
      var Account = $(this).children('a').html();
      var UID = $(this).attr('data-uid');
      Twitches.push({UserID: UID, TwitchID: Account});
    //}
  });

  Twitch.init({clientId: 'p3sjg1826u06kkilciu3dq0arszem1h'}, function(error, status) {
    if (error) {
      // error encountered while loading
      console.debug(error);
    }
    //console.debug(Twitches);

    // the sdk is now loaded
    for (var i = 0; i < Twitches.length; i++)
    {
      var Streamer = Twitches[i];
      Twitch.api({method: 'streams/' + Streamer.TwitchID}, function(error, list) {
        var Status = 0;
        var Photo = null;
        if(list.stream) {
          Status = 1;
          Photo = list.stream.preview.medium;
          UpdateDB({userid: Streamer.UserID, photo: Photo, online: Status});
        }
        else {
          // get photo from the channel
          Twitch.api({method: 'channels/' + Streamer.TwitchID}, function(error, list) {
            Photo = list.logo;
            if(!Photo) {
              Photo = list.video_banner;
            }
            
            UpdateDB({userid: Streamer.UserID, photo: Photo, online: Status});
          });
        }
      });
    }

    if (status.authenticated) {
      // user is currently logged in
    }
  });
});

function UpdateDB(info) {
  // Update the db by passing the data back
  $.ajax({
    url: gdn.url('/plugin/communitystreams/update/' + gdn.definition('TransientKey')),
    global: false,
    type: 'POST',
    data: info,
    dataType: 'json',
    success: function(Data) {
      console.debug(Data);
    }
  });
}
