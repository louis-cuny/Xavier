$(function () {

  //This is for hidding the flash messages
  $('.message').each(function () {
    $(this).delay(3000).animate({'margin-bottom': 0, 'margin-top': 0}, 2000);
    $('body').delay(3000).animate({'padding-top': '-=50px'}, 2000);
  });
});

 function stringToColour(str) {
  var hash = 0;
  for (var i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  var colour = '#';
  for (var i = 0; i < 3; i++) {
    var value = (hash >> (i * 8)) & 0xFF;
    colour += ('00' + value.toString(16)).substr(-2);
  }
  return colour;
}
