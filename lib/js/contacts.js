(function($){
  $(document).ready(function() {
    //Activate select2
    $("#contact-selector").select2();
    //Show first one
    $(".contact-list > .0").fadeIn();
    //Hide rest and show selected
    $("#contact-selector").on("select2-selecting", function(e) {
      //console.log("selected val="+ e.val);
      $('.contact-list > .contact-box').hide();
      $(".contact-list > ."+e.val).fadeIn();
    });
  });
})(jQuery);
