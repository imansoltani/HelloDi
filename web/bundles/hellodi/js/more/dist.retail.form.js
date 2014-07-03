$(function(){
    $('.step_number').val(1);
    $('.stepContainer').height($('#step-1').height());

    $(window).resize(function(){
        ChangeSize(Now_Step(0));
    });

    $('* input:text').blur(function(){
        if($(this).val() != '')
            $(this).removeAttr('style');
    });
});

function ChangeSize(x)
{
    $('.stepContainer').height($('#step-'+x).height());
}

function Now_Step(x)
{
    if(x > 0) $('.step_number').val(x);
    return $('.step_number').val();
}

function go(y)
{
    if((y == 1 && $('#step1').attr('isdone') == 1)
        || (y == 2 && $('#step2').attr('isdone') == 1)
        || (y == 3 && $('#step3').attr('isdone') == 1)
        )
    {
        var x = Now_Step(0);
        if( x != y)
        {
            if(!Page(x,1)) return;
            Page(y,0);
            Now_Step(y);
        }
    }
}


function Next()
{
    var x = Math.round(Now_Step(0));
    if( x < 3 )
    {
        if(!Page(x,1)) return;
        Page(x+1,0);
        Now_Step(x+1);
    }
}

function Prev()
{
    var x = Math.round(Now_Step(0));
    if( x > 1 )
    {
        if(!Page(x,1)) return;
        Page(x-1,0);
        Now_Step(x-1);
    }
}

/**
 * @return {boolean}
 */
function Done()
{
    if(!Validation(3)) return false;
    $('form.form-step').submit();
    return true;
}

/**
 * @return {boolean}
 */
function Page(x , y)
{
    if(y == 1)
    {
        if(!Validation(x)) return false ;
    }

    if(y == 1)  $('.form-actions').fadeOut("fast");
    else        $('.form-actions').fadeIn("slow");

    if(y == 0)
    {
        $(document).scrollTop($('.widget-header').offset().top);
    }

    if(x == 1)
    {

        if(y == 0)
        {
            ChangeSize(1);
            $('.content').fadeOut();
            $('#step-1').fadeIn();
            $('#step1').removeAttr('class');
            $('#step1').attr('class','selected');
            if($('#step1').attr('isdone') == 0 )$('#step1').attr('isdone','1');
            //$('.buttonPrevious').attr('style','display:none');
            return true ;
        }
        $('#step1').removeAttr('class');
        $('#step1').attr('class','done');
        //$('.buttonPrevious').removeAttr('style');

        return true ;
    }


    if(x == 2)
    {

        if(y == 0)
        {
            ChangeSize(2);
            $('.content').fadeOut();
            $('#step-2').fadeIn();
            $('#step2').removeAttr('class');
            $('#step2').attr('class','selected');
            if($('#step2').attr('isdone') == 0 )$('#step2').attr('isdone','1');


            return true ;
        }
        $('#step2').removeAttr('class');
        $('#step2').attr('class','done');
        return true ;
    }

    if(x == 3)
    {

        if(y == 0)
        {
            ChangeSize(3);
            $('.content').fadeOut();
            $('#step-3').fadeIn();
            $('#step3').removeAttr('class');
            $('#step3').attr('class','selected');
            if($('#step3').attr('isdone') == 0 )$('#step3').attr('isdone','1');
            $('.buttonNext').attr('style','display:none');
            $('.finalsubmit').removeAttr('style');
            return true ;
        }
        $('#step3').removeAttr('class');
        $('#step3').attr('class','done');
        $('.buttonNext').removeAttr('style');
        $('.finalsubmit').attr('style','display:none');
        return true ;
    }
    return false ;
}

/**
 * @return {boolean}
 */
function Validation(x)
{
    var y = true;
    $("#step-"+x).find("input:required").each(function() {
        if ($(this).val() == '') {
            $(this).attr('style','border:thin #FF0000 solid');
            if(y) $(this).focus();
            y = false;
        }
    });
    return y;
}