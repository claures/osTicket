$(document).on('click', '#mxvp_departmentselect li', function (evt) {
    $('.depSelected').removeClass('depSelected');
    $(this).addClass('depSelected');
});

$(document).on('click', '#nav li, #sub_nav li', function (evt) {
    $('.depSelected').removeClass('depSelected');
});

$(document).on('click', '.quickCloseTicket', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    console.log(tid + ' - close');
    $.ajax({
        method: 'POST',
        url: 'ajax.php/tickets/' + tid + '/status',
        data: 'status_id=3&comments=&undefined=Close'
    }).success(function (evt) {
        window.location.href = '../scp';
    });
});

$(document).on('click', '.quickClaimTicket', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    console.log(tid + ' - claim');
    $.ajax({
        method: 'POST',
        url: 'ajax.php/tickets/' + tid + '/direct_claim',
        data: '11f4956ed4c24c8c%5B%5D=s1&f47a6bc1ef579f80=&undefined=Yes%2C%20Claim'
    }).success(function (evt) {
        window.location.href = '../scp/tickets.php?id=' + tid;
    });
});

$(document).on('click', '.quickBombTicket', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    var tno = $(this).attr('data-ticketno');
    var mail = encodeURI($(this).attr('data-owener'));
    var bomber = encodeURI($(this).attr('data-bomber'));
    console.log(tid + ' - bomb');
    var reason = prompt("Bomb Reason:", '');
    if (reason == null) reason = '';
    $.ajax({
        method: 'GET',
        url: '../scripts/bomb.php?tid=' + tid + '&tno=' + tno + '&mail=' + mail + '&bomber=' + bomber + '&reason=' + reason
    }).success(function (data) {
        alert(data);
        window.location.href = '../scp';
    });
});

$(document).on('click', '.quickMarkTicket', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    var tno = $(this).attr('data-ticketno');
    console.log(markData);
    $.ajax({
        method: 'POST',
        url: '../scripts/mark.php?tid=' + tid + '&tno=' + tno,
        data: JSON.stringify(markData)
    }).success(function (data) {
        alert("User Marked");
        //window.location.href ='../scp';
    });
});

$(document).on('click', '.billSupportButton', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    // var tid = $(this).attr('data-ticketid');
    // var tno = $(this).attr('data-ticketno');
    // console.log(markData);
    $.ajax({
        method: 'GET',
        url: '../scripts/billSupport.php',
        dataType: 'json'
    }).success(function (data) {

        if (data.success) {
            $('#billSupportForm').attr('action', data.url);

            setTimeout(function () {
                $('#submitBillSupportFom').trigger('click');
                $('.quickCloseTicket').trigger('click');
            }, 200);
        }

        // alert("User Marked");
        //window.location.href ='../scp';
    });
});

$(document).on('click', '.save.pending', function () {
    var selectValue = $('select[name=reply_status_id]').val();

    if (selectValue == '8') {
        $.ajax({
            method: 'GET',
            url: '../scripts/billSupport.php',
            dataType: 'json'
        }).success(function (data) {

            if (data.success) {
                $('#billSupportForm').attr('action', data.url);

                setTimeout(function () {
                    $('#submitBillSupportFom').trigger('click');
                }, 100);
            }
        });
    }
});

//Shortcuts

function isTicketView(){
    return $('.quickCloseTicket').length > 0;
}

$(document).on('keydown', function (evt) {
    //Help: ctrl+alt+h
    if(isTicketView()) {
        if ((evt.metaKey || evt.ctrlKey) && evt.altKey && evt.key == 'h')
            alert("ctrl+alt+h : Help\nctrl+alt+t : Transfer\nctrl+alt+x : Close Ticket\nctrl+alt+c : Claim Ticket\nctrl+alt+u : Scroll to top");
        if ((evt.metaKey || evt.ctrlKey) && evt.altKey && evt.key == 't') {
            //Transfer Popup
            console.log('XFER');
            $('#ticket-transfer').click();
        }
        if ((evt.metaKey || evt.ctrlKey) && evt.altKey && evt.key == 'x') {
            //Close Ticket
            console.log('Close');
            $('.quickCloseTicket').click();
        }
        if ((evt.metaKey || evt.ctrlKey) && evt.altKey && evt.key == 'c') {
            //Claim Ticket
            console.log('Claim');
            $('.quickClaimTicket').click();
        }
        if ((evt.metaKey || evt.ctrlKey) && evt.altKey && evt.key == 'u') {
            //Scroll to top
            console.log('Scroll UP');
            $('a.only.sticky.scroll-up').click();
        }
    }
});


//add collapse feature

function hideAllEntry(){

    let count = $('.thread-entry').length;
    if (count > 5) {
        $('.thread-entry').find('.thread-body').each(function () {
            count--;
            if (count > 0) {
                $(this).addClass('hidden');
            }
        });
    }
}



hideAllEntry();



$("#pjax-container").ajaxSuccess(function(){
    hideAllEntry();
});


$('body').on('click', '.thread-entry .header', function () {
    let th = $(this).parent().find('.thread-body');
    if (th.hasClass('hidden')) th.removeClass('hidden');
    else th.addClass('hidden');
})
