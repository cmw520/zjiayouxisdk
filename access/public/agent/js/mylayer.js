///////弹出层layer
(function($){
  $.fn.mylayer = function(){
    var isIE = (document.all) ? true : false; 
    var isIE6 = isIE && !window.XMLHttpRequest; 
    var position = !isIE6 ? "fixed" : "absolute"; 
    var containerBox = $(this); 
    containerBox.css({"z-index": "9999","display": "block","position": position ,"top": "50%","left": "50%","margin-top": -(containerBox.height() / 2) + "px","margin-left": - (containerBox.width() /2 ) + "px"}); 
    var mylayer=$("<div class='layerbg'></div>"); 
    mylayer.css({"width": "100%","height": "100%","position": position,"top": "0px","left": "0px","background-color": "#000","z-index": "9998","opacity": "0.45"}); 
    $("body").append(mylayer); 
    function mylayer_iestyle(){
      var maxWidth = Math.max(document.documentElement.scrollWidth, document.documentElement.clientWidth) + "px"; 
      var maxHeight = Math.max(document.documentElement.scrollHeight, document.documentElement.clientHeight) + "px"; 
      mylayer.css({"width": maxWidth , "height": maxHeight }); 
    }
    function containerBox_iestyle(){ 
      var marginTop = $(document).scrollTop - containerBox.height() / 2 + "px"; 
      var marginLeft = $(document).scrollLeft - containerBox.width() / 2 + "px"; 
      containerBox.css({"margin-top": marginTop , "margin-left": marginLeft }); 
    }
    if(isIE){ 
      mylayer.css("filter", "alpha(opacity=45)"); 
    } 
    if(isIE6){ 
      mylayer_iestyle(); 
      containerBox_iestyle(); 
    } 
    $("window").resize(function(){ 
      mylayer_iestyle(); 
    }); 
    $(".closepop", containerBox).click(function(){
      containerBox.hide(0); 
      $(mylayer).remove();
    });
  }; 
})(jQuery);
///////弹出层layer end