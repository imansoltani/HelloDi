
var time = 0 ;

$(function(){

    $('* #formwithsubmit').submit(function(e){


            e.preventDefault();

            var element = $(this);



            var body = '<div class="bodypopup poptop"  ><div class="modal-header containerpop">';
            body += '<button type="button" class="close" onclick="PopuptopClose(event)">X</button>' ;
            body += '<h3 id="myModalLabel">Alert</h3></div>' ;
            body += '<form class="form-horizontal" action="" method="POST"><div class="modal-body containerpop">' ;
            body += '<div>Are You Sure</div>';
            body += '<div class="Fields_show" >'+$(".Fields",this).html()+'</div>';
            body += '</div><div class="containerpop form-actions " style="padding-left: 0px">' ;
            body += '<input type="submit" style="margin-right:5px" class="btn btn-primary" value="Yes" onclick="SubmitForm();" >' ;
            body += '<input type="submit" class="btn" value="No" onclick="PopuptopClose(event)" >' ;
            body += '</div></form>' ;
            body += '</div><div class="closedoor poptop" onclick="PopuptopClose(event)" ></div>';
            $('.popupshow').html(body);
            $('.bodypopup').hide();
            $('.popupshow').fadeIn();
            $('.bodypopup').slideDown();


            $('* input',element).each(function(){
                $(this).val();
                var name = ".Fields_show #"+ $(this).attr('name') ;
            });


    });


    $('.messageshow').hide();
    $('.popupshow').hide();
    $('#popuptop').hide();



    $('* .messagetop ,.messageshow ').click(function () {
        $('.messageshow').fadeOut('fast', function () {
            $('.messageshow').html('');
            $('.messageshow').removeAttr('id');
        });
    });

    $('* .messagetop ,.messageshow ').click(function () {
        $('.messageshow').fadeOut('fast', function () {
            $('.messageshow').html('');
            $('.messageshow').removeAttr('id');
        });
    });

    //AcceptedMessage("hi","hoy");



});




function AlertShow(message,fname,event)
{
    var flag = true ;
    var element = document.getElementsByName(fname);
    var form = $(element).attr('id');

    var i = 0 ;
    var name = '* .'+form+' input' ;
    $(name).each(function(){
        if($(this).attr('required') == "required")
        {
            if($(this).val() == "" || $(this).val() == null  )
            {
                flag = false ;
            }
        }
    });

    name = '* .'+form+' select' ;
    $(name).each(function(){
        if($(this).attr('required') == "required")
        {
            if($(this).val() == "" || $(this).val() == null  )
            {
                flag = false ;
            }
        }
    });

    if(flag)
    {
        event.preventDefault();
        var body = '<div class="bodypopup poptop"  ><div class="modal-header containerpop">';
        body += '<button type="button" class="close" onclick="PopuptopClose(event)">X</button>' ;
        body += '<h3 id="myModalLabel">Alert</h3></div>' ;
        body += '<form class="form-horizontal" action="" method="POST"><div class="modal-body containerpop">' ;
        body += '<div>'+message+'</div>';
        body += '</div><div class="containerpop form-actions " style="padding-left: 0px">' ;
        body += '<input type="submit" style="margin-right:5px" class="btn btn-primary" value="Yes" onclick="SubmitForm(\''+fname+'\');" >' ;
        body += '<input type="submit" class="btn" value="No" onclick="PopuptopClose(event)" >' ;
        body += '</div></form>' ;
        body += '</div><div class="closedoor poptop" onclick="PopuptopClose(event)" ></div>';
        $('.popupshow').html(body);
        $('.bodypopup').hide();
        $('.popupshow').fadeIn();
        $('.bodypopup').slideDown();
    }
}

function messagetop(subject, body, type) {
    if (type == "error") $('.messageshow').attr('id', 'messageerror');
    if (type == "success") $('.messageshow').attr('id', 'messagesuccess');
    if (type == "alert") $('.messageshow').attr('id', 'messagealert');

    var x = '<div class="popup messagetop"  >';
    //x += '<i class="icon-';
    //if(type == "error") x +='remove';
    //if(type == "success") x +='ok';
    //if(type == "alert") x +='warning-sign';
    //x += '" ></i>';
    x += '<div class="container" ><h1>' + body + '</h1></div></div><div class="closedoor messagetop" ></div>';
    //x += '<h1>'+subject+'</h1>' ;
    //x += '<hr><div><span>'+body+'</span></div></div><div class="closedoor messagetop" ></div>' ;

    $('.messageshow').html(x);
    $('.messageshow  >.popup').hide();
    $('.messageshow').fadeIn();
    $('.popup').slideDown();


}

function Popuptop(text) {
    var element = document.getElementsByClassName(text);
    var name = $(element).attr('id');
    if (name = "popuptop") {

        var body = '<div class="bodypopup poptop"  >';
        body += $(element).html() + '</div><div class="closedoor poptop" onclick="PopuptopClose(event)" ></div>';

        $('.popupshow').html(body);
        $('.bodypopup').hide();
        $('.popupshow').fadeIn();
        $('.bodypopup').slideDown();
    }
}

function PopuptopClose(e) {
    e.preventDefault();
    $('.popupshow').fadeOut('fast', function () {
        $('.popupshow').html('');
        $('.popupshow').removeAttr('id');
    });
}


function CheckData(x) {
    var y = false;
    $(x).each(function () {
        if ($(this).val() == '') {
            y = true;
            $(this).attr('style', 'border:thin #FF0000 solid');
        }
    });
    if (y) {
        messagetop('Error', 'Fill required inputs', 'error');
        return false
    }
    return true;
}