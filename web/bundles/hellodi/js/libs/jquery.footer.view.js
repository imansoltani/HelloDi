/**
 * Created with JetBrains PhpStorm.
 * To change this template use File | Settings | File Templates.
 */
$(function(){
    var mobile_v = 0 ; //mobile size
    if($(window).width() > 747 ) mobile_v = 1 ; // desktop size


    $(window).resize(function(){

        var x = $(window).width();

        if(x > 747)
        {
            if(mobile_v == 0)
            {
                $('.tab_body').show();
                $('.row0 > .span3').removeAttr('style');
                $('.row0 > .span3').removeAttr('disabled');
                mobile_v = 1 ;
            }
        }
        else
        {
            if(mobile_v == 1)
            {
                $('.tab1').attr('style','color:#FFF;border-bottom:solid 2px #FFF;cursor:default');
                $('.tab1').attr('disabled','disabled');
                $('.tab02').hide();
                $('.tab03').hide();
                $('.tab04').hide();
                mobile_v = 0 ;
            }
        }
    });

});


function footer_manage(x)
{
    $('.row0 > .span3').removeAttr('style');
    $('.tab_body').fadeOut();
    if(x == 1)
    {
        $('.tab1').attr('style','color:#FFF;border-bottom:solid 2px #FFF;cursor:default')
        $('.tab1').attr('disabled','disabled')
        $('.tab01').fadeIn();
    }
    if(x == 2)
    {
        $('.tab2').attr('style','color:#FFF;border-bottom:solid 2px #FFF;cursor:default')
        $('.tab2').attr('disabled','disabled')
        $('.tab02').fadeIn();
    }
    if(x == 3)
    {
        $('.tab3').attr('style','color:#FFF;border-bottom:solid 2px #FFF;cursor:default')
        $('.tab3').attr('disabled','disabled')
        $('.tab03').fadeIn();
    }
    if(x == 4)
    {
        $('.tab4').attr('style','color:#FFF;border-bottom:solid 2px #FFF;cursor:default')
        $('.tab4').attr('disabled','disabled')
        $('.tab04').fadeIn();
    }
}
