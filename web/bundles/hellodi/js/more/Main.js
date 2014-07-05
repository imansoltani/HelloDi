var time = 0;

$(function () {


//    $('.messageshow').hide();
//    $('.popupshow').hide();
//    $('#popuptop').hide();


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


function AlertShow(message, fname, event) {
    var flag = true;


    var i = 0;
    var name = '* .' + fname + ' input';
    $(name).each(function () {
        if ($(this).attr('required') == "required") {
            if ($(this).val() == "" || $(this).val() == null) {
                flag = false;
            }
        }
    });

    name = '* .' + fname + ' select';
    $(name).each(function () {
        if ($(this).attr('required') == "required") {
            if ($(this).val() == "" || $(this).val() == null) {
                flag = false;
            }
        }
    });

    if (flag) {
        event.preventDefault();
        var body = '<div class="bodypopup poptop"  ><div class="modal-header containerpop">';
        body += '<button type="button" class="close" onclick="PopuptopClose(event)">X</button>';
        body += '<h3 id="myModalLabel">Alert</h3></div>';
        body += '<form class="form-horizontal" action="" method="POST"><div class="modal-body containerpop">';
        body += '<div>' + message + '</div>';
        body += '</div><div class="containerpop form-actions " style="padding-left: 0px">';
        body += '<input type="submit" style="margin-right:5px" class="btn btn-primary" value="Yes" onclick="SubmitForm(\'.' + fname + '\');" >';
        body += '<input type="submit" class="btn" value="No" onclick="PopuptopClose(event)" >';
        body += '</div></form>';
        body += '</div><div class="closedoor poptop" onclick="PopuptopClose(event)" ></div>';
        $('.popupshow').html(body);
        $('.bodypopup').hide();
        $('.popupshow').fadeIn();
        $('.bodypopup').slideDown();
    }
}

var form_YesNoMessage = null;
$(document).ready(function () {
    $('a.YesNoMessage').click(function (e) {
        e.preventDefault();
        PopuptopOpen($(this).attr('header'), $(this).attr('message'), $(this).attr('href'));
    });
    $('form.YesNoMessage').submit(function (e) {
        if (form_YesNoMessage == null) {
            e.preventDefault();
            form_YesNoMessage = $(this);
            PopuptopOpen($(this).attr('header'), $(this).attr('message'), 'javascript:$(form_YesNoMessage).submit();');
        }
    });
});

function PopuptopOpen(header, message, action) {
    var body = '<div class="bodypopup poptop"  ><div class="modal-header containerpop">';
    body += '<button type="button" class="close" onclick="PopuptopClose(event)">X</button>';
    body += '<h3 id="myModalLabel">' + header + '</h3></div>';
    body += '<form class="form-horizontal" action="" method="POST"><div class="modal-body containerpop">';
    body += '<div>' + message + '</div>';
    body += '</div><div class="containerpop form-actions " style="padding-left: 0px">';
    body += '<a href="' + action + '" style="margin-right:5px" class="btn btn-primary">Yes</a>';
    body += '<a href="javascript:;" class="btn" onclick="PopuptopClose(event);" >No</a>';
    body += '</div></form>';
    body += '</div><div class="closedoor poptop" onclick="PopuptopClose(event)" ></div>';
    $('.popupshow').html(body);
    $('.bodypopup').hide();
    $('.popupshow').fadeIn();
    $('.bodypopup').slideDown();
}

function SubmitForm(name) {
    $(name).focus();
    $(name).submit();
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
        form_YesNoMessage = null;
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





function NotificationCount() {
    var element = $('.Noti>a span');
    var x = $(element).html();
    if (x == 1) {
        $(element).remove();
        $('.Noti').fadeOut('fast',function(){
            $('.Noti').remove();
        });
        $('.NotiSpan').fadeOut('fast',function(){
           $('.NotiSpan').remove();
        });
    }
    else {
        $(element).html(--x);
    }
}