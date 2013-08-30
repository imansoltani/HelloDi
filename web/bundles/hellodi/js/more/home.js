

$(window).bind('load',function(){
    $('.main').hide();
    $('.Section1').show();
    $('.Section1').focus();
    $('header.main').delay(500).fadeIn('slow',function(){
        $('a.main').slideDown(function(){
            $('.Other').delay(500).slideDown();

        });
    });
});

function autoScroll() {
    var div = document.getElementById("mydiv");
    div.style.display = '';
    var top = div.offsetTop;
    if(window.scrollTop != top)
        window.scrollTo(0, top);
}
function loadAutoScroll() {
    autoScroll();
    window.onload = null;
    return false;
}
function scrollAutoScroll() {
    autoScroll();
    window.setTimeout(function(){ window.onscroll = null; }, 100);
    return false;
}



var SectionShow = 0 ;
var scrollValue = 0 ;
var NowSection = 0 ;
var ActiveSection = 0 ;
$(window).scroll(function(){
    //var x = $('.Section1').scrollTop() ;
    var Sec1 = $('.Section2').offset().top ;
    var Sec4 = $('.Section4').offset().top ;

    var Sec41 = $('.Section4 .f2>div').offset().top ;
    var Sec42 = $('.Section4 .f3>div').offset().top ;
    var Sec43 = $('.Section4 .f4>div').offset().top ;
    var Sec6 = $('.Section6').offset().top ;
    var mHeight = $(window).height();
    var mWidth = $(window).width();

    var y = parseFloat($(this).scrollTop());
    $('.Section1>div').css('top',-(y * 0.2) + "px");





    if( $(this).scrollTop() > ( Sec1 - 400 ) && SectionShow == 0 )
    {
        SectionShow = 1 ;
        $('.S1Header').animate({opacity:'1'},function(){
            $('.S1Text').delay(0).animate({opacity:"1"});
        });
    }

    if( $(this).scrollTop() > ( Sec4 - 500 ) && SectionShow == 1 )
    {
        SectionShow = 2 ;
        $('.Section4').animate({paddingTop:'40px'},500,function(){
//            $('.Section4').animate({paddingTop:'40px'},'slow');
        });
        $('.Section4>div>div').animate({opacity:'1'},700);
    }

    if( $(this).scrollTop() > ( Sec41 - 600 ) && SectionShow == 2 )
    {
        SectionShow = 3 ;
       $('.Section4 .f2 header').animate({opacity:'1'},'slow',function(){
            $('.Section4 .f2 p').animate({opacity:'1'},'slow');
        });
    }

    if( $(this).scrollTop() > ( Sec42 - 600 ) && SectionShow == 3 )
    {
        SectionShow = 4 ;
        $('.Section4 .f3 header').animate({opacity:'1'},'slow',function(){
            $('.Section4 .f3 p').animate({opacity:'1'},'slow');
        });
    }


    if( $(this).scrollTop() > ( Sec43 - 600 ) && SectionShow == 4 )
    {
        SectionShow = 5 ;
        $('.Section4 .f4 header').animate({opacity:'1'},'slow',function(){
            $('.Section4 .f4 p').animate({opacity:'1'},'slow');
        });
    }

    if( $(this).scrollTop() > ( Sec6 - 400 ) && SectionShow == 5 )
    {
        SectionShow = 6 ;
        var speed = 0 ;
        $('* .Section6 > div > div a').each(function(){
            $(this).delay(speed * 200).animate({width:'90%'});
            speed++ ;
        });
    }

    var Sec2 = $('.Section2').offset().top - 5 ;
    var Sec3 = $('.Section3').offset().top - 5 ;
    var Sec4 = $('.Section5').offset().top - 5 ;
    var Sec5 = $('.Section6').offset().top - 105 ;
    var Sec6 = $('.Section7').offset().top - 5  ;
    var Sec7 = $('.Section8').offset().top - 405 ;


    if($(this).scrollTop() < Sec2)
    {
        if(NowSection != 1)
        {
            NowSection = 1 ;
            $('.showHome').removeClass('showHome');
            $('.S1').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec2 && $(this).scrollTop() < Sec3   )
    {
        if(NowSection != 2)
        {
            NowSection = 2 ;
            $('.showHome').removeClass('showHome');
            $('.S2').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec3 && $(this).scrollTop() < Sec4   )
    {
        if(NowSection != 3)
        {
            NowSection = 3 ;
            $('.showHome').removeClass('showHome');
            $('.S3').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec4 && $(this).scrollTop() < Sec5   )
    {
        if(NowSection != 4)
        {
            NowSection = 4 ;
            $('.showHome').removeClass('showHome');
            $('.S4').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec5 && $(this).scrollTop() < Sec6   )
    {
        if(NowSection != 5)
        {
            NowSection = 5 ;
            $('.showHome').removeClass('showHome');
            $('.S5').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec6  && $(this).scrollTop() < Sec7   )
    {
        if(NowSection != 6)
        {
            NowSection = 6 ;
            $('.showHome').removeClass('showHome');
            $('.S6').addClass('showHome');
        }
    }
    else if($(this).scrollTop() >= Sec7  )
    {
        if(NowSection != 7)
        {
            NowSection = 7 ;
            $('.showHome').removeClass('showHome');
            $('.S7').addClass('showHome');
        }
    }

});



$(document).ready(function(){



    $('* .nav .dropdown > a').click(function()
    {
        if($(this).attr('go'))
        {

//            $(this).addClass('showHome1');
            var x = $(this).attr('go');
            NowSection = 1 ;

            if(x == 1) x = $('.Section1').offset().top;
            else if(x == 2)  x = $('.Section2').offset().top;
            else if(x == 3)  x = $('.Section3').offset().top;
            else if(x == 4)  x = $('.Section5').offset().top;
            else if(x == 5)  x = $('.Section6').offset().top - 100;
            else if(x == 6)  x = $('.Section7').offset().top;
            else if(x == 7)  x = $('.Section8').offset().top;



            $('body,html').stop().animate({
                scrollTop: x
            }, 1800);
        }
    })


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

