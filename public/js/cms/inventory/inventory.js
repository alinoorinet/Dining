$(function () {
    $('form#checkInventory #searchUidOrStd').on('click',function () {
        var identify = $('#identify').val();
        if(identify == '')
            return false;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.ui.dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/inventory/search',
            data: JSON.stringify({identify:identify}),
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                $('.ui.dimmer').dimmer('hide');
                if (data.status == 101 || data.status == 102) {
                    $('#processBox').show();
                    $('#processBox h2').text(data.res);
                    $('#processBox #sec1').empty();
                }
                if (data.status == 103 || data.status == 104) {
                    $('#addCard').slideDown();
                    $('#subCard').slideDown();
                    $('#processBox h2').text('');
                    $('#processBox #sec1').html(data.res);
                    $('#addWalletAmount #userId').val(data.userId);
                    $('#subWalletAmount #userId').val(data.userId);
                    if(data.status == 104) {
                        $('#paygates').removeClass('d-none');
                        $('#paygates-body').html(data.trans);
                    }
                    else {
                        $('#paygates-body').empty();
                        $('#paygates,#inventories').addClass('d-none');
                    }
                }
                if(data.status == 105)
                    alert(data.res);
            },
            error: function (error) {
                $('.ui.dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
                //console.log(error);
            }
        });
    });
    $('#billCheckbox').on('change',function () {
        var ch = $(this);
        if(ch.is(':checked'))
            $('#billId').prop('disabled',false);
        else
            $('#billId').prop('disabled',true).val('');
    });
    $('#trCheckbox').on('change',function () {
        var ch = $(this);
        if(ch.is(':checked'))
            $('#trackCode,#tId').prop('disabled', false);
        else
            $('#trackCode,#tId').prop('disabled', true).val('');
    });
    $('#addWalletAmount').on('click','#addWalletBtn',function () {
        var addWalletForm = '#addWalletAmount ';
        var amount = $(addWalletForm+'#amount').val();
        if(amount == '') {
            alert('مبلغ را وارد کنید');
            return false;
        }
        var billId = '';
        if($('#billCheckbox').is(':checked')) {
            billId = $(addWalletForm+'#billId').val();
            if(billId == '') {
                alert('شماره قبض را وارد کنید');
                return false;
            }
        }
        var trackCode = '';
        if($('#trCheckbox').is(':checked')) {
            trackCode = $(addWalletForm+'#trackCode').val();
            if(trackCode == '') {
                alert('کد پیگیری تراکنش اینترنتی را وارد کنید');
                return false;
            }
        }
        var tId = '';
        if($('#trCheckbox').is(':checked')) {
            tId = $(addWalletForm+'#tId').val();
            if(tId == '') {
                alert('آی دی تراکنش اینترنتی را وارد کنید');
                return false;
            }
        }
        var desc = $(addWalletForm+'#description').val();
        if(desc == ''){
            alert('توضیحات و دلیل افزایش را وارد کنید');
            return false;
        }

        var userId = $(addWalletForm+'#userId').val();
        if(userId == ''){
            alert('فرایند جست و جو کاربر را دوباره انجام دهید');
            return false;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.ui.dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/inventory/add-wallet-amount',
            data: JSON.stringify({
                amount:amount,
                billId:billId,
                trackCode:trackCode,
                tId:tId,
                desc:desc,
                userId:userId
            }),
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                $('.ui.dimmer').dimmer('hide');
                if (data.status == 101) {
                    $.each(data.errors,function (key,value) {
                        if(key == "userId")
                            alert('اطلاعات ورودی نامعتبر است');
                        if (key == "billId")
                            $(addWalletForm+'#billId').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        if (key == "trackCode")
                            $(addWalletForm+'#trackCode').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        if (key == "tId")
                            $(addWalletForm+'#tId').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        if (key == "amount")
                            $(addWalletForm+'#amount').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        if (key == "desc")
                            $(addWalletForm+'#desc').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                    });
                }
                else if(data.status == 102) {
                    alert(data.res);
                }
                else if(data.status == 200) {
                    var tblAmount = $('#processBox #sec1').find('#tblAmount');
                    $(tblAmount).text(data.amount);
                    alert(data.res);
                }
                if(data.status == 105)
                    alert(data.res);
            },
            error: function (error) {
                $('.ui.dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
                //console.log(error);
            }
        });
    });
    $('#subWalletAmount').on('click','#subWalletBtn',function () {
        var subWalletForm = '#subWalletAmount ';
        var amount = $(subWalletForm+'#amount').val();
        if(amount == '') {
            alert('مبلغ را وارد کنید');
            return false;
        }

        var desc = $(subWalletForm+'#description').val();
        if(desc == ''){
            alert('توضیحات و دلیل افزایش را وارد کنید');
            return false;
        }

        var userId = $(subWalletForm+'#userId').val();
        if(userId == ''){
            alert('فرایند جست و جو کاربر را دوباره انجام دهید');
            return false;
        }
        //console.log(amount+' '+ billId+ ' '+ trackCode+ ' '+ desc + ' '+ userId);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.ui.dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/inventory/sub-wallet-amount',
            data: JSON.stringify({
                amount:amount,
                desc:desc,
                userId:userId
            }),
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                $('.ui.dimmer').dimmer('hide');
                if (data.status == 101) {
                    $.each(data.errors,function (key,value) {
                        if(key == "userId")
                            alert('اطلاعات ورودی نامعتبر است');
                        if (key == "amount"){
                            $(subWalletForm+'#amount').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        }
                        if (key == "desc"){
                            $(subWalletForm+'#desc').addClass('is-invalid').after('<div class="invalid-feedback">'+value+'</div>');
                        }
                    })
                }
                else if(data.status == 102) {
                    alert(data.res);
                }
                else if(data.status == 200) {
                    var tblAmount = $('#processBox #sec1').find('#tblAmount');
                    $(tblAmount).text(data.amount);
                    alert(data.res);
                }
                if(data.status == 105)
                    alert(data.res);
            },
            error: function (error) {
                $('.ui.dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
                // console.log(error);
            }
        });
    });
    $('#print').on('click',function () {
        $('#forPrint').print({
            globalStyles : true,
            mediaPrint : true,
            stylesheet : "{{asset('/css/assets/bootstrap.css')}}"
        });
    });
});
