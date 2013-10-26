var LastWidth = 1600;
$(function () {

//    $('.menuBar').delay(500).animate({top: 0, opacity: 1});
    LoadShow();

    FixSize();

    $(window).resize(function () {
        FixSize();
    });



//    $(element).css('background','#000');
});

$('a.login').click(function(){

});

var topScroll = new Array();
function FixSize() {
    var mWidth = $(window).width();
    var mHeight = $(window).height();

    $('.Step').height(mHeight);
    $('.Network').height(mHeight - 73);

    if (mWidth > 600) {
        if (mWidth != LastWidth) {
            ResizeConnect(mWidth / LastWidth);
            LastWidth = mWidth;
        }
    }
    var x = (mWidth / 100)*90;


    if(!$('.S_5').hasClass('done'))
    {
        $('.S_5 .news > div').css('margin-left',x + 'px')
    }

    x /= 4 ;

    $('.S_5 .news > div > div').width(x-10);

}

function ResizeConnect(value) {

    var mw = $('.mainConnect').width();
    var mh = $('.mainConnect').height();
    mw *= value;
    mh *= value;
    $('.mainConnect').width(mw).height(mh);

    var l0 = $('.mainConnect').offset().left;
    var t0 = $('.mainConnect').offset().top;

    $('.mainConnect > div').each(function () {
        var x = $(this).width();
        var y = $(this).height();
        var l = $(this).offset().left - l0;
        var t = $(this).offset().top - t0;


        x *= value;
        y *= value;
        l *= value;
        t *= value;


        $(this).width(x);
        $(this).height(y);
        $(this).css('top', t + 'px');
        $(this).css('border-radius', x + 'px');
        $(this).css('left', l + 'px');


    });
}

//Animates
function LoadShow() {
    $('span.mainCycle').delay(500).animate({opacity: 1}, 2000).delay(1000).fadeOut('slow', function () {
        $('.cycle.c2').delay(500).animate({opacity: 1}, function () {
            $('.cycle.c1').delay(500).animate({opacity: 1}, function () {
                $('.cycle.c1 span').animate({opacity: 1}, function () {

                    for (var i = 1; i < 16; i++) {
                        $('.min.m' + i + ' ').delay(i * 100).animate({opacity: 1}, 200);
                    }
                    $('.cycle.c2 span').delay((16 * 100) + 200).animate({opacity: '1'}, function () {

                        $('.cycle.c3').animate({opacity: 1}, 'fast', function () {

                            for (var i = 17; i < 23; i++) {
                                $('.min.m' + i + ' ').delay((i - 16) * 100).animate({opacity: 1}, 200);
                            }
                            $('.cycle.c3 span').delay((6 * 100) + 200).animate({opacity: '1'}, function () {

                                $('.cycle.c4').animate({opacity: 1});
                                $('.cycle.c5').animate({opacity: 1}, function () {
                                    for (var i = 23; i < 39; i++) {
                                        $('.min.m' + i + ' ').delay((i - 23) * 70).animate({opacity: 1}, 140, function () {
                                            if ($(this).hasClass('last')) {
                                                $('.cycle.c4 span , .cycle.c5 span').animate({opacity:1},300);
                                                $('.menuBar').delay(500).animate({top: 0, opacity: 1},function(){
                                                          $('.l1').slideDown();
                                                }).animate({opacity:1},300,function(){
                                                        tTabs($('.bottom .option .t1'));
                                                    });


                                            }
                                        });

                                    }
                                });

                            });

                        });

                    });
                });
            });
        });
    });
}

var SlideOption = { loop : 0 , active : false } ;
var slideShowTimer ;
function tTabs(element) {

    $('.title a', element).animate({marginLeft: '5%', opacity: 1}, function () {
        var x = $('.title div', element).width();
        $('.title div', element).css({'width': '0%', 'display': 'inline-block'});
        $('.text', element).animate({opacity: 1}, 500);
        $('.title div', element).animate({width: x}, 4000, function () {
            if ($(element).hasClass('t1')) {
                tTabs('.bottom .option .t2');
            }
            else {
                if ($(element).hasClass('t2')) {
                    tTabs('.bottom .option .t3');
                }
                else {

                    $('.bottom .option .t2 , .bottom .option .t3').removeClass('active').animate({opacity:0.2});
                    $('.S_1 .bottom .slider').animate({opacity: 1},function(){



                        slideShowTimer = new (function() {
                            var slideTime = 5000;
                            var transitionSpeed = 500;

                            // Setup timer
                            $(function() {

                                slideShowTimer.Timer = $.timer(updateTimer, slideTime, true).once();
                            });

                            // Change slides
                            function updateTimer() {

                                if(!SlideOption.active) SlideOption.active = true ; else SlideLoop();
                            }

                            this.resetStopwatch = function() {
                                SlideOption.active = false;
                                slideShowTimer.Timer.stop().once();
                            };
                        });

                    });
                }
            }
        });
    });

}

function SlideLoop(){

    SlideOption.active = false ;


    $('.bottom .option .active').removeClass('active').animate({opacity:0.2},300,function(){
        SlideOption.loop = (SlideOption.loop + 1) % 3 ;
        $('.bottom .slider .active').removeClass('active');
        $('.bottom .option .t'+(SlideOption.loop + 1) +'').delay(100).addClass('active').animate({opacity:1},function(){
            $('.bottom .slider .t'+(SlideOption.loop + 1) +'').addClass('active');
            SlideOption.active = true ;
        });
    });

}

$('.bottom .slider .slide , .S_1 .bottom .option li').click(function(){
    if(!$(this).hasClass('active'))
    {
        if(SlideOption.active)
        {
            SlideOption.loop = ($(this).attr('slide') == 0)? 2 : $(this).attr('slide') - 1 ;
            SlideLoop();
            slideShowTimer.resetStopwatch();
        }

    }
});



//Scroll

var ScrollOption = { active : 0 , allow : true };

$('a.lGo').click(function(){


    var element = $(this);

    if(!$(element).hasClass('active'))
    {


        topScroll[0] = $('.t1.l1').offset().top ;
        topScroll[1] = $('.t2.l1').offset().top ;
        topScroll[2] = $('.t3.l1').offset().top ;
        topScroll[3] = $('.t4.l1').offset().top ;
        topScroll[4] = $('.t5.l1').offset().top ;
        topScroll[5] = $('.t6.l1').offset().top ;
        topScroll[6] = $('.t7.l1').offset().top ;


        ScrollOption.allow = false;

        var id = $(element).attr('id');
        ScrollOption.active = parseInt(id) ;
        $('a.lGo.active').removeClass('active');

        $('.Step').addClass('wait').css('z-index',100);
        $('.Step.active').removeClass('wait').css('z-index',4500);
        $('.Step.S_'+(ScrollOption.active + 1)+ '').removeClass('wait').css({'z-index':3500,'display':'block'});




        $('.Step.active').delay(100).fadeOut(function(){
            $(this).removeClass('active').addClass('wait');
            $('.Step.S_'+(ScrollOption.active + 1)+ '').addClass('active');
            $('.Step.wait').css({'z-index':0,'opacity':0});

            $('.Step.active').fadeIn(function(){
                $('a#'+ScrollOption.active).addClass('active');

                $('html,body').animate({
                    scrollTop: topScroll[ScrollOption.active] + 150
                }, 500 , function(){
                    Animate();
                    $('.Step.wait').removeClass('wait').css({'opacity':1,'z-index':100});
                    ScrollOption.allow = true;
                });
            });
        });

        /*
         $('.Step').addClass('wait');

         $('.Step.active').removeClass('active').addClass('EndLoad');
         $('.Step.S_'+(ScrollOption.active + 1)+ '').addClass('active').fadeIn(function(){

         $(this).removeClass('wait').css('opacity',1);
         $('a#'+ScrollOption.active).addClass('active');



         $('.Step.EndLoad').fadeOut(function(){
         Animate();
         $('.Step.wait').css('opacity',0);
         $('.Step.EndLoad').removeClass('EndLoad');

         $('html,body').animate({
         scrollTop: topScroll[ScrollOption.active] + 150
         }, 500 , function(){
         setTimeout(function(){ScrollOption.allow = true; $('.Step').removeClass('wait').css('opacity',1);},2500);
         });

         });

         });*/





//    if(!$(element).hasClass('active'))
//    {
//

//        alert(id);
//        $('a.lGo.active').removeClass('active');
//
//        $('.Step.active').fadeOut().removeClass('active');
//        $('html,body').animate({
//            scrollTop: topScroll[ScrollOption.active]
//        }, 1000,function(){
//            $('.Step.S_'+(ScrollOption.active + 1)+ '').addClass('active').fadeIn(function(){
//                $('a#'+ScrollOption.active).addClass('active');
//                Animate();
//                ScrollOption.allow = true;
//            });
//        });
//    }



    }

});


$(window).scroll(function(){

    if(ScrollOption.allow)
    {

        topScroll[0] = $('.t1.l1').offset().top ;
        topScroll[1] = $('.t2.l1').offset().top ;
        topScroll[2] = $('.t3.l1').offset().top ;
        topScroll[3] = $('.t4.l1').offset().top ;
        topScroll[4] = $('.t5.l1').offset().top ;
        topScroll[5] = $('.t6.l1').offset().top ;
        topScroll[6] = $('.t7.l1').offset().top ;

        var windowScroll = $(this).scrollTop() ;




        ScrollCheck(windowScroll);
    }



    /*  if(windowScroll < t2l1Scroll)
     {
     if(!$('.t1.l2').hasClass('active'))
     {
     $('.l2.active').fadeOut().removeClass('active');
     $('.l2.t1').fadeIn().addClass('active');
     }
     }*/


});



function ScrollCheck(window)
{

    if(ScrollOption.active != 0 && window < topScroll[ScrollOption.active])
    {

        if(!$('.Step.S_'+(ScrollOption.active)+ '').hasClass('active'))
        {

            $('.Step.active').fadeOut().removeClass('active');
            $('a#'+ScrollOption.active).removeClass('active');
            ScrollOption.active-- ;
            $('.Step.S_'+(ScrollOption.active + 1)+ '').addClass('active').fadeIn(function(){
                $('a#'+ScrollOption.active).addClass('active');
                Animate();
            });


        }

    }
    else if(ScrollOption.active < 6 && window > topScroll[ScrollOption.active+1])
    {
//            alert(1);
        if(!$('.Step.S_'+(ScrollOption.active + 2)+ '').hasClass('active'))
        {
            $('.Step.active').fadeOut().removeClass('active');
            $('a#'+ScrollOption.active).removeClass('active');
            ScrollOption.active++ ;
            $('.Step.S_'+(ScrollOption.active + 1)+ '').addClass('active').fadeIn(function(){
                $('a#'+ScrollOption.active).addClass('active');
                Animate();
            });


        }
    }

}

function Animate()
{
    if(!$('.Step.S_' + (ScrollOption.active + 1) + '').hasClass('done'))
    {
        switch (ScrollOption.active)
        {
            case 1:
                Step2Animate();
                break;
            case 2:
                Step3Animate();
                break;
            case 3:
                Step4Animate();
                break;
            case 4:
                Step5Animate();
                break;
            case 5:
                Step6Animate();
                break;
        }
    }
}

function Step2Animate()
{
    $('.S_2').addClass('done');
    $('.S_2 span').delay(200).animate({opacity:1},500).animate({right:0},500,function(){
        $('.S_2 div div').animate({opacity:1},500);
    });
}

function Step3Animate()
{
    $('.S_3').addClass('done');
    $('.S_3 .textMain').delay(500).animate({opacity:1},function(){
        var i = 1 ;
        $('.S_3 ul.list li div.main').css('top','20%');
        $('.S_3 ul.list li div.main').each(function(){
            $(this).delay(i * 200).animate({top:0,opacity:1},400);
            i++;
            if(i == 4)
            {
                $('.S_3 ul.list li div.main div.text').delay(1500).animate({width:'100%'},function(){
                    $('.S_3 ul.list li div.main div.text>div').animate({opacity:1});
                });
            }
        });
    });
}


function Step4Animate()
{
    $('.S_4').addClass('done');
//    $('.S_4 .info').animate({opacity:1});
}

function Step5Animate()
{
    $('.S_5').addClass('done');
    $('.S_5 h1').delay(500).animate({opacity:1});
    $('.S_5 .news > div').delay(200).animate({marginLeft:0,opacity:1},1000);
}

function Step6Animate()
{
    $('.S_6').addClass('done');
    $('.S_6 .body > div').delay(200).slideDown(function(){
        $('.S_6 ul li.a1').delay(200).animate({opacity:1},250);
        $('.S_6 ul li.a2').delay(500).animate({opacity:1},250);
        $('.S_6 ul li.a3').delay(800).animate({opacity:1});
    });
}
