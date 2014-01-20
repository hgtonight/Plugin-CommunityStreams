/* Copyright 2014 Zachary Doll */
jQuery(document).ready(function($) {
  Twitch.init({clientId: 'p3sjg1826u06kkilciu3dq0arszem1h'}, function(error, status) {
  if (error) {
    // error encountered while loading
    console.log(error);
  }
  
  console.log(status);
    // the sdk is now loaded
  if (status.authenticated) {
    // user is currently logged in
  }
  });
});
