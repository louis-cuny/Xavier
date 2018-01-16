$(function () {

  //This is for hidding the flash messages
  $('.message').each(function () {
    $(this).delay(3000).animate({'margin-bottom': 0, 'margin-top': 0}, 2000);
    $('body').delay(3000).animate({'padding-top': '-=50px'}, 2000);
  });

});