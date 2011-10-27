var checkFailedStatuses = function () {
  $('.p-feature-list.failed').each(function(index) {
    var tr =  $(this);
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'check',
      id:tr.attr('id')
    };

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

  });
}

var checkAllStatuses = function () {
  $('.p-feature-list').each(function(index) {
    var tr =  $(this);
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'check',
      id:tr.attr('id')
    };

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

  });
}

$(function() {
  $('abbr.timeago').timeago();

  $('.run-feature').bind('click',function(){

    var link = $(this);
    var tr =  $(this).parents('tr');
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'run',
      id:tr.attr('id')
    };

    link.html('<img src="static/default/img/ajax.gif" alt="loading..."/>');

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
        tr.children('.p-feature-last_message').html('<pre>'+data.last_message+'</pre>');
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

    jqxhr.complete(function(){ link.html('Запустить'); });
  });

  setInterval(function() {checkFailedStatuses();}, 3000);
  setInterval(function() {checkAllStatuses();}, 60000);

});

var updateFeature = function (tr,tr_class,data){
  tr.toggleClass(tr_class).toggleClass(data.status_description);
  tr.children('.p-feature-last_run').children('abbr').html(jQuery.timeago(data.last_run));
};
