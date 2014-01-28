/* Copyright 2014 Zachary Doll */
jQuery(document).ready(function($) {
  // Only init the twitch api if there are twitch users in the list
  if ($("li[data-service='twitch']").length) {
    Twitch.init({clientId: 'p3sjg1826u06kkilciu3dq0arszem1h'}, function(error, status) {
      if (error) {
        // error encountered while loading
        console.debug(error);
      }

      // the sdk is now loaded
      UpdateTwitchAccounts(15);

      if (status.authenticated) {
        // user is currently logged in
      }
    });
  }
});

function UpdateTwitchAccounts(CacheMinutes) {
  CacheMinutes = typeof CacheMinutes !== 'undefined' ? CacheMinutes : 15;
  var ServerDate = gdn.definition('CurrentServerDateTime');
  var CurrServerDate = new Date(Date.parse(ServerDate));
  // Find all the streamers that need to be updated
  $("li[data-service='twitch']").each(function() {
    var TargetDate = new Date(Date.parse(ServerDate) - CacheMinutes * 60000);
    var CachedDate = new Date(Date.parse($(this).attr('data-cache-date')));

    // only update if the cache date is older than the requested update interval
    if (CachedDate.getTime() < TargetDate.getTime()) {
      var Account = $(this).attr('data-account');
      var UID = $(this).attr('data-uid');
      console.log('Updating ' + Account + ' from user ' + UID + ' for Twitch...');
      Twitch.api({method: 'streams/' + Account}, function(error, list) {
        var Status, Photo;
        if (list.stream) {
          Status = 1;
          Photo = list.stream.preview.medium;

          var DataObj = {userid: UID, photo: Photo, online: Status};
          UpdateDBAndList(DataObj);
        }
        else {
          // get photo from the channel
          Twitch.api({method: 'channels/' + Account}, function(error, list) {
            Status = 0;
            Photo = list.logo;
            if (!Photo) {
              Photo = list.video_banner;
            }
            
            if(Photo === null) {
              // use a default avatar instead
              Photo = 'plugins/communitystreams/design/default.png';
            }

            var DataObj = {userid: UID, photo: Photo, online: Status};
            UpdateDBAndList(DataObj);
          });
        }
      });
    }
  });

  // Queue another update when the cache should be expired
  setTimeout(UpdateTwitchAccounts, CacheMinutes * 60000);
}

function UpdateDBAndList(info) {
  // Update the db by passing the data back
  $.ajax({
    url: gdn.url('/plugin/communitystreams/update/' + gdn.definition('TransientKey')),
    global: false,
    type: 'POST',
    data: info,
    dataType: 'json',
    success: function(Data) {
      if (Data.Result == true) {
        // Add the current server date to the info object and store it in the definitions for the next run
        info.Date = Data.DateTime;
        gdn.definition('CurrentServerDateTime', Data.DateTime, true);
        UpdateList(info);
      }
    }
  });
}

function UpdateList(info) {
  var CSSClass = 'Offline';
  if (info.online) {
    CSSClass = 'Online';
  }
  // Update the class
  $("li[data-uid='" + info.userid + "']").removeClass().addClass(CSSClass);

  // Update the photo
  $("li[data-uid='" + info.userid + "'] a img").append('<img src="' + info.photo + '"></img>');

  var CurrentDate = new Date().toISOString().slice(0, 19).replace('T', ' ');
  // Update the cached date
  $("li[data-uid='" + info.userid + "']").attr('data-cache-date', CurrentDate);
}