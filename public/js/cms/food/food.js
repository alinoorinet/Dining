$(function () {
    "use strict";
    $('.del-food-link').on('click',function (e) {
        e.preventDefault();
        const btn = $(this);
        const href = btn.attr('href');
        const confirmation = confirm("حذف غذا باعث پاک شدن همه ی داده های مربوط به این غذا از جمله برنامه غذایی و رزرو ها می شود.ادامه می دهید؟");
        if(confirmation)
            window.location.href = href;
    });

    $('#food-search-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn  = form.find('#search-food-btn');
        const data = new FormData(this);
        const statusBtn = form.find('#search-food-status');

        $('#food-search-result').html('');
        const formGroup = $(btn).closest('.form-group');
        const inF = $(formGroup).find('.invalid-feedback');
        $(inF).empty();

        $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/foods/search',
            data: data,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (data) {
                $(btn).html('<i class="fa fa-search"></i>');
                if (data.status === 200) {
                    $(statusBtn).html('<i class="fa fa-check text-success"></i>');
                    $('#food-search-result').html(data.res);
                }
                else if(data.status === 102) {
                    $(statusBtn).html('<i class="fa fa-question text-warning"></i>');

                    $(inF).html('<strong>'+data.res+'</strong>');
                }
                else
                    alert(data.res);
            },
            error: function (error) {
                $(btn).html('<i class="fa fa-search"></i>');
                $(statusBtn).html('<i class="fa fa-times text-danger"></i>');
                alert('خطای اتصال به شبکه');
                //location.reload();
                console.log(error);
            }
        });
    });

    $('#food-search-result').on('click', '.edit-food-link',function () {
        const fDimmer = $('#food-dimmer');
        const pDimmer = $('#price-dimmer');
        fDimmer.dimmer('show');

        const btn = $(this);
        const fId = btn.attr('data-id');
        if(!jQuery.isNumeric(fId))
            return false;

        const row       = btn.closest('tr');
        const tbody     = $(row).closest('tbody');
        const title     = row[0].cells[2].innerText;
        const type      = row[0].cells[3].innerText;
        const caption   = row[0].cells[4].innerText;
        const swfCode   = row[0].cells[5].innerText;
        const typeVal   = type === 'غذا' ? 0 : 1;
        const foodStuffRows = $(tbody).find('.stuff-row');


        const form        = $('#food-submit-form');
        const foodType    = form.find('input[name=food_type][value='+typeVal+']');
        const foodTitle   = form.find('input[name=title]');
        const foodCaption = form.find('input[name=caption]');
        const foodSwfCode = form.find('input[name=swf_code]');
        $(foodType).attr('checked','checked');
        $(foodTitle).val(title);
        $(foodCaption).val(caption);
        $(foodSwfCode).val(swfCode);

        const stuffTbl     = $('#food-stuff-tbl');
        const stuffTblBody = stuffTbl.find('tbody');
        const stuffTblTrs  = $(stuffTblBody).find('tr');
        let checkedTrs = [];
        let otherTrs   = [];

        foodStuffRows.map((k,foodStuffRow) => {
            stuffTblTrs.map(function(index,stuffTblTr) {
                const sr = $(foodStuffRow);
                const stuffName = sr[0].cells[1].innerText;
                if(!checkedTrs.find(data => data.id === $(stuffTblTr).attr('id'))) {
                    if ($(stuffTblTr).attr('data-title') === stuffName)
                        checkedTrs.push({
                            'id': $(stuffTblTr).attr('id'),
                            'chosen': true,
                            'tr': $(stuffTblTr),
                            'name': stuffName,
                            'value': sr[0].cells[2].innerText,
                            'unit': sr[0].cells[3].innerText
                        });
                    else {
                        if (!otherTrs.find(data => data.id === $(stuffTblTr).attr('id')) )
                            otherTrs.push({
                                'id': $(stuffTblTr).attr('id'),
                                'chosen': false,
                                'tr': $(stuffTblTr),
                            });
                    }
                }
            });
        });

        if(otherTrs.length !== 0)
            $(stuffTblBody).empty();
        checkedTrs.map((checkedTr) => {
            if(checkedTr.chosen === true) {
                const unitAmount = checkedTr.tr[0].cells[3].children[0];
                const unitValue = checkedTr.tr[0].cells[2].children[0];
                const unitCheck = checkedTr.tr[0].cells[0].children[0];
                $(unitAmount).find('option[value="' + checkedTr.unit + '"]').attr('selected', 'selected');
                $(unitValue).attr('value', parseInt(checkedTr.value));
                $(unitCheck).attr('checked', 'checked');
            }
            $(stuffTblBody).append(checkedTr.tr[0].outerHTML);
        });
        otherTrs.map((otherTr) => {
            if(!checkedTrs.find(data => data.id === otherTr.id ))
                $(stuffTblBody).append(otherTr.tr[0].outerHTML);
        });


        $('#collection-rest').removeAttr('disabled').css('opacity',1);
        $('#meal-separate').removeAttr('disabled').css('opacity',1);
        const priceForm = $('#price-form');
        const fIdInp = priceForm.find('input[name="food_id"]');
        if(fIdInp.length !== 0)
            $(fIdInp).val(fId);
        else
            priceForm.prepend('<input type="hidden" name="food_id" value="'+fId+'">');

        fDimmer.dimmer('hide');
        pDimmer.dimmer('show');

        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/foods/price/edit',
            data: JSON.stringify({food_id: fId}),
            dataType: 'json',
            contentType: 'application/json',
            processData: false,
            success: function (data) {
                pDimmer.dimmer('hide');
                if (data.status === 200) {
                    const pWrapper = priceForm.find('#price-wrapper');
                    $(pWrapper).html(data.res);
                }
                else if(data.status === 101) {

                }
                else {
                    alert(data.res);
                }
            },
            error: function (error) {
                pDimmer.dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                // location.reload();
                console.log(error)
            }
        });
    });

    $('#food-submit-form')
        .on('submit',function (e) {
            e.preventDefault();
            const btn      = $(this);
            const data = new FormData(this);
            $('#food-dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/foods/store',
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#food-dimmer').dimmer('hide');
                    if (data.status === 200) {
                        $('#collection-rest').removeAttr('disabled').css('opacity',1);
                        $('#meal-separate').removeAttr('disabled').css('opacity',1);
                        const priceForm = $('#price-form');
                        const fIdInp = priceForm.find('input[name="food_id"]');
                        if(fIdInp.length !== 0)
                            $(fIdInp).val(data.id);
                        else
                            priceForm.prepend('<input type="hidden" name="food_id" value="'+data.id+'">');
                        alert(data.res);
                    }
                    else {
                        alert(data.res);
                    }
                },
                error: function (error) {
                    $('#food-dimmer').dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    // location.reload();
                    console.log(error)
                }
            });
        });

    $('#price-form')
        .on('change','.collection-check',function () {
            const ch    = $(this);
            const idAttr = ch.attr('id');
            const id     = idAttr.split('-')[1];
            const div    = $('#restlist-'+id);
            if(ch.is(':checked'))
                div.removeClass('d-none');
            else
                div.addClass('d-none');
        })
        .on('change','.rest-check',function () {
            const ch    = $(this);
            const tr   = ch.closest('tr');
            const btn  = $(tr).find('button.get-price-rest');
            if(ch.is(':checked'))
                $(btn).removeClass('d-none');
            else
                $(btn).addClass('d-none');
        })
        .on('click','.set-event-price',function () {
            const btn = $(this);
            const idAttr = btn.attr('id');
            const id = idAttr.split('-')[3];
            const tbl = btn.closest('table');
            const tr = $(tbl).find('tr#tr-set-event-price-'+id);
            if($(tr).hasClass('d-none'))
                $(tr).removeClass('d-none');
            else
                $(tr).addClass('d-none');
        })
        .on('click','#meal-separate .remove-saleday-time',function () {
            $(this).closest('tr').remove();
        })
        .on('click','.add-saleday-time',function () {
            const btn = $(this);
            const tr = btn.closest('tr');
            const cell0 = tr[0].cells[0].innerHTML;
            const cell1 = tr[0].cells[1].innerHTML;
            const cell2 = tr[0].cells[2].innerHTML;
            const cell3 = tr[0].cells[3].innerHTML;
            const cell4 = '<button type="button" class="btn btn-info btn-sm remove-saleday-time"><i class="fa fa-minus"></i></button>';
            const newTr = '<tr>\n' +
                '    <td class="align-middle">'+ cell0 + '</td>\n' +
                '    <td>'+ cell1 + '</td>\n' +
                '    <td>'+ cell2 + '</td>\n' +
                '    <td>'+ cell3 + '</td>\n' +
                '    <td>'+ cell4 + '</td>\n' +
                '</tr>';
            $(tr).after(newTr);
        })
        .on('change','.ramezan-label',function () {
            const closestFormGroup = $(this).closest('.form-group');
            const input = $(closestFormGroup).find('.ramezan-input');
            if($(this).is(':checked'))
                $(input).removeAttr('disabled');
            else
                $(input).attr('disabled','disabled');
        })
        .on('change','input[name="meal_has_same_price"]',function () {
            const input = $(this);
            const fieldset = $('#meal-separate');
            const luTab   = fieldset.find('a[href="#lu"]');
            const dnTab   = fieldset.find('a[href="#dn"]');
            const shTab   = fieldset.find('a[href="#sh"]');
            const ftTab   = fieldset.find('a[href="#ft"]');
            const mvTab   = fieldset.find('a[href="#mv"]');
            const arTab   = fieldset.find('a[href="#ar"]');
            const luPanel = fieldset.find('#lu');
            const dnPanel = fieldset.find('#dn');
            const shPanel = fieldset.find('#sh');
            const ftPanel = fieldset.find('#ft');
            const mvPanel = fieldset.find('#mv');
            const arPanel = fieldset.find('#ar');
            if(parseInt(input.val()) === 1) {
                $(luTab).attr('disabled','disabled').addClass('text-light');
                $(dnTab).attr('disabled','disabled').addClass('text-light');
                $(shTab).attr('disabled','disabled').addClass('text-light');
                $(ftTab).attr('disabled','disabled').addClass('text-light');
                $(mvTab).attr('disabled','disabled').addClass('text-light');
                $(arTab).attr('disabled','disabled').addClass('text-light');
                $(luPanel).attr('disabled','disabled');
                $(dnPanel).attr('disabled','disabled');
                $(shPanel).attr('disabled','disabled');
                $(ftPanel).attr('disabled','disabled');
                $(mvPanel).attr('disabled','disabled');
                $(arPanel).attr('disabled','disabled');
            }
            else {
                $(luTab).removeAttr('disabled').removeClass('text-light');
                $(dnTab).removeAttr('disabled').removeClass('text-light');
                $(shTab).removeAttr('disabled').removeClass('text-light');
                $(ftTab).removeAttr('disabled').removeClass('text-light');
                $(mvTab).removeAttr('disabled').removeClass('text-light');
                $(arTab).removeAttr('disabled').removeClass('text-light');
                $(luPanel).removeAttr('disabled');
                $(dnPanel).removeAttr('disabled');
                $(shPanel).removeAttr('disabled');
                $(ftPanel).removeAttr('disabled');
                $(mvPanel).removeAttr('disabled');
                $(arPanel).removeAttr('disabled');
            }
        })
        .on('submit',function (e) {
            e.preventDefault();
            const btn      = $(this);
            const form = new FormData(this);
            $('#price-dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/foods/price/store',
                data: form,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#price-dimmer').dimmer('hide');
                    if (data.status === 200) {
                        alert(data.res);
                    }
                    else {
                        alert(data.res);
                    }
                },
                error: function (error) {
                    $('#price-dimmer').dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    // location.reload();
                    console.log(error)
                }
            });
        })
        .on('click','.get-price-rest',function () {
            const btn = $(this);
            const idMix  = btn.attr('id');
            const id = idMix.split('-')[4];

            if(!jQuery.isNumeric(id))
                return false;

            const priceForm = $('#price-form');
            const fInp = priceForm.find('input[name=food_id]');
            if (fInp.length === 0)
                alert('برای مشاهده قیمت ها باید از طریق فرم جست و جو، غذای مورد نظر را پیدا و روی دکمه ویرایش کلیک کنید');
            const fId = $(fInp).val();
            if(!jQuery.isNumeric(fId))
                return false;

            const pDimmer = $('#price-dimmer');
            pDimmer.dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/foods/price/get-rest-price',
                data: JSON.stringify({r_id: id,f_id:fId}),
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                success: function (data) {
                    pDimmer.dimmer('hide');

                    if (data.status === 200) {
                        const pWrapper = priceForm.find('#price-wrapper');
                        $(pWrapper).html(data.res);
                    }
                    else if(data.status === 101) {

                    }
                    else {
                        alert(data.res);
                    }
                },
                error: function (error) {
                    pDimmer.dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    // location.reload();
                    console.log(error)
                }
            });
        });
});
