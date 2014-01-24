/* Copyright 2014 Zachary Doll */
jQuery(document).ready(function($) {
  
  UpdateTwitches();
  
});

function UpdateTwitches(CacheMinutes) {
  CacheMinutes = typeof CacheMinutes !== 'undefined' ? CacheMinutes : 15;
  var Twitches = [];
  // Find all the streamers that need to be updated
  $("li[data-service='twitch']").each(function() {
    var CurrentDate = new Date();
    var TargetUpdate = new Date(CurrentDate.getTime() - 15 * 60000);
    var DateUpdated = $(this).attr('data-date').split(/[- :]/);
    var CachedDate = new Date(DateUpdated[0], DateUpdated[1] - 1, DateUpdated[2], DateUpdated[3], DateUpdated[4], DateUpdated[5]);

    if (CachedDate < TargetUpdate) {
      var Account = $(this).children('a').html();
      var UID = $(this).attr('data-uid');
      Twitches.push({UserID: UID, TwitchID: Account});
    }
  });

  // Update the status of twitch feeds that are older than the cache time (15 minutes)
  if (Twitches.length > 0) {
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
          var Status, Photo;
          if (list.stream) {
            Status = 1;
            Photo = list.stream.preview.medium;
            
            var DataObj = {userid: Streamer.UserID, photo: Photo, online: Status};
            UpdateDB(DataObj);
            UpdateList(DataObj);
          }
          else {
            // get photo from the channel
            Twitch.api({method: 'channels/' + Streamer.TwitchID}, function(error, list) {
              Status = 0;
              Photo = list.logo;
              if (!Photo) {
                Photo = list.video_banner;
              }
              
              var DataObj = {userid: Streamer.UserID, photo: Photo, online: Status};
              UpdateDB(DataObj);
              UpdateList(DataObj);
            });
          }
        });
      }

      if (status.authenticated) {
        // user is currently logged in
      }
    });
  }
  
  // Queue another update in 15 minutes
  setTimeout(UpdateTwitches, 15 * 60 * 1000);
}

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

function UpdateList(info) {
  var CSSClass = 'Offline';
  if(info.online) {
    CSSClass = 'Online';
  }
  // Update the class
  $("li[data-uid='" + infor.userid + "']").removeClass().addClass(CSSClass);
  
  // Update the photo
  $("li[data-uid='" + infor.userid + "'] a img").append('<img src="' + info.photo + '"></img>');
}