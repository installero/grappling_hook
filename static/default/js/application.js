default_error_message = 'Ошибка связи!';

var showMessage=function(text){
  $('.l-message .form-error').html(text).show();
};

$(function() {
  $('.l-message .form-error').hide();
  $('.l-message .form-error').click(function(){
    $(this).hide();
  });
});
