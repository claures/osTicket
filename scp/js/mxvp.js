$(document).on('click','#mxvp_departmentselect li',function (evt) {
    $('.depSelected').removeClass('depSelected');
    $(this).addClass('depSelected');
});

$(document).on('click','#nav li, #sub_nav li',function (evt) {
    $('.depSelected').removeClass('depSelected');
});

$(document).on('click', '.quickCloseTicket',function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    console.log(tid + ' - close');
    $.ajax({
        method: 'POST',
        url: 'ajax.php/tickets/'+tid+'/status',
        data: 'status_id=3&comments=&undefined=Close'
    }).success(function(evt) {
        window.location.href ='../scp';
    });
});

$(document).on('click', '.quickClaimTicket',function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    console.log(tid + ' - claim');
    $.ajax({
        method: 'POST',
        url: 'ajax.php/tickets/'+tid+'/claim',
        data: '11f4956ed4c24c8c%5B%5D=s1&f47a6bc1ef579f80=&undefined=Yes%2C%20Claim'
    }).success(function(evt) {
        window.location.href ='../scp/tickets.php?id='+tid;
    });
});

$(document).on('click', '.quickBombTicket',function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    var tno = $(this).attr('data-ticketno');
    var mail = encodeURI($(this).attr('data-owener'));
    var bomber = encodeURI($(this).attr('data-bomber'));
    console.log(tid + ' - bomb');
    var reason = prompt("Bomb Reason:",'');
    if(reason == null) reason = '';
    $.ajax({
        method: 'GET',
        url: '../scripts/bomb.php?tid='+tid+'&tno='+tno+'&mail='+mail+'&bomber='+bomber+'&reason='+reason
    }).success(function(data) {
        alert(data);
        window.location.href ='../scp';
    });
});

$(document).on('click', '.quickMarkTicket',function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var tid = $(this).attr('data-ticketid');
    var tno = $(this).attr('data-ticketno');
    console.log(markData);
    $.ajax({
        method: 'POST',
        url: '../scripts/mark.php?tid='+tid+'&tno='+tno,
        data: JSON.stringify(markData)
    }).success(function(data) {
        alert(data);
        //window.location.href ='../scp';
    });
});