/**
 * Created with JetBrains PhpStorm.
 * User: M.Sajadi
 * Date: 4/26/13
 * Time: 9:31 PM
 * To change this template use File | Settings | File Templates.
 */
$(function(){
    $('.step_number').val(1);
    $('.stepContainer').height($('#step-1').height());

    $(window).resize(function(){
        ChangeSize(Now_Step(0));
    });

    $('#Provider_Entity_Create').on('submit', function (e) {



    });
});

function ChangeSize(x)
{

    if(x == 1) x = $('#step-1').height() ;
    if(x == 2) x = $('#step-2').height() ;
    $('.stepContainer').height(x);
}

function Now_Step(x)
{
    if(x > 0)
    {
        $('.step_number').val(x);
    }
    else
    {
        return $('.step_number').val();
    }

}

function go(y)
{
    if((y == 1 && $('#step1').attr('isdone') == 1)
        || (y == 2 && $('#step2').attr('isdone') == 1)
        || (y == 3 && $('#step3').attr('isdone') == 1))
    {
        var x = Now_Step(0);
        if( x != y)
        {
            Page(x,1);
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
        Page(x,1);

        Page(x+1,0);
        Now_Step(x+1);
    }
}

function Prev()
{
    var x = Math.round(Now_Step(0));
    if( x > 1 )
    {
        Page(x,1);
        Page(x-1,0);
        Now_Step(x-1);
    }
}

function Page(x , y)
{
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
            $('.buttonPrevious').attr('id','buttonDisabled');
            return ;
        }
        $('#step1').removeAttr('class');
        $('#step1').attr('class','done');
        $('.buttonPrevious').removeAttr('id');

        return;
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
            return ;
        }
        $('#step2').removeAttr('class');
        $('#step2').attr('class','done');
        return;
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
            $('.buttonNext').attr('id','buttonDisabled');
            return ;
        }
        $('#step3').removeAttr('class');
        $('#step3').attr('class','done');
        $('.buttonNext').removeAttr('id');
        return;
    }

}

function Validation_Prov(x)
{
    if(x == 1) return Valid_Step_1() ;
    if(x == 2) return Valid_Step_2() ;
    if(x == 3) return Valid_Step_3() ;
}

function Valid_Step_1()
{

    return true ;
}


function Valid_Step_2()
{
    return true ;
}

function Valid_Step_3()
{
    return true ;
}