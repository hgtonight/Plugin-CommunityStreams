/* Copyright 2014 Zachary Doll */
jQuery(document).ready(function($) {
  
  // Only init the twitch api if there are twitch users in the list
  if($("li[data-service='twitch']").length) {
    Twitch.init({clientId: 'p3sjg1826u06kkilciu3dq0arszem1h'}, function(error, status) {
      if (error) {
        // error encountered while loading
        console.debug(error);
      }

      // the sdk is now loaded
      UpdateTwitchAccounts();

      if (status.authenticated) {
        // user is currently logged in
      }
    });
  }
});

function UpdateTwitchAccounts(CacheMinutes) {
    CacheMinutes = typeof CacheMinutes !== 'undefined' ? CacheMinutes : 15;
    
  // Find all the streamers that need to be updated
  $("li[data-service='twitch']").each(function() {
    var CurrentDate = new Date();
    var TargetUpdate = new Date(CurrentDate.getTime() - 15 * 60000);
    var DateUpdated = $(this).attr('data-date').split(/[- :]/);
    var CachedDate = new Date(DateUpdated[0], DateUpdated[1] - 1, DateUpdated[2], DateUpdated[3], DateUpdated[4], DateUpdated[5]);

    if (CachedDate < TargetUpdate) {
      var Account = $(this).attr('data-account');
      var UID = $(this).attr('data-uid');
        Twitch.api({method: 'streams/' + Account}, function(error, list) {
          var Status, Photo;
          if (list.stream) {
            Status = 1;
            Photo = list.stream.preview.medium;
            
            var DataObj = {userid: UID, photo: Photo, online: Status};
            UpdateDB(DataObj);
            UpdateList(DataObj);
          }
          else {
            // get photo from the channel
            Twitch.api({method: 'channels/' + Account}, function(error, list) {
              Status = 0;
              Photo = list.logo;
              if (!Photo) {
                Photo = list.video_banner;
              }
              
              var DataObj = {userid: UID, photo: Photo, online: Status};
              UpdateDB(DataObj);
              UpdateList(DataObj);
            });
          }
        });
      }
    });
  
  // Queue another update in 15 minutes
  setTimeout(UpdateTwitchAccounts, CacheMinutes * 60 * 1000);
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
  $("li[data-uid='" + info.userid + "']").removeClass().addClass(CSSClass);
  
  // Update the photo
  $("li[data-uid='" + info.userid + "'] a img").append('<img src="' + info.photo + '"></img>');
  
  var CurrentDate = new Date().toISOString().slice(0, 19).replace('T', ' ');
  // Update the cached date
  $("li[data-uid='" + info.userid + "']").attr('data-date', CurrentDate);
}