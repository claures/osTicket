$(document).on('click','#mxvp_departmentselect li',function (evt) {
    $('.depSelected').removeClass('depSelected');
    $(this).addClass('depSelected');
});

$(document).on('click','#nav li, #sub_nav li',function (evt) {
    $('.depSelected').removeClass('depSelected');
});