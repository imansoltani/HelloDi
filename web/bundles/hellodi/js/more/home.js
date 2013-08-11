var $elie = $('.sun>img');

$(document).ready(function(){


    rotate(0);

    $('.sun').delay(500).animate({marginTop:'0'},1500);
    $('.BG').delay(600).fadeTo(1500,1,function(){
//        $('.sun>img').rotate(250);
        $('.menubarhome').fadeIn();
    });

});

function rotate(degree) {

    // For webkit browsers: e.g. Chrome
    $elie.css({ WebkitTransform: 'rotate(' + degree + 'deg)'});
    // For Mozilla browser: e.g. Firefox
    $elie.css({ '-moz-transform': 'rotate(' + degree + 'deg)'});

    // Animate rotation with a recursive call
    setTimeout(function() { rotate(++degree); },15);
}

jQuery.fn.rotate = function(degrees) {
    $(this).css({'-webkit-transform' : 'rotate('+ degrees +'deg)',
        '-moz-transform' : 'rotate('+ degrees +'deg)',
        '-ms-transform' : 'rotate('+ degrees +'deg)',
        'transform' : 'rotate('+ degrees +'deg)'});
};

