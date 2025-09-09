$(function () {
    "use strict";
    const get_menus = function(data) {
        const modalDimmer = $('#modal-dimmer');
        modalDimmer.dimmer('show');

        const menuModal = '#menu-modal';
        $(menuModal).modal();
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/define-day-food/get-menus',
            data: data,
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                modalDimmer.dimmer('hide');
                if (data.status === 200) {
                    $('.accordion#bf-chosen-foods').html(data.res.bf);
                    $('.accordion#sh-chosen-foods').html(data.res.sh);
                    $('.accordion#lu-chosen-foods').html(data.res.lu);
                    $('.accordion#ft-chosen-foods').html(data.res.ft);
                    $('.accordion#as-chosen-foods').html(data.res.as);
                    $('.accordion#dn-chosen-foods').html(data.res.dn);
                    $('.accordion#mv-chosen-foods').html(data.res.midd);
                }
                else
                    alert(data.res);
            },
            error: function (error) {
                modalDimmer.dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                //location.reload();
                console.log(error)
            }
        });
    };

    $('#week-box').on('click','#week-tbl .menu-btn',function () {
        const btn       = $(this);
        const date      = btn.attr('data-date');
        const menuModal = '#menu-modal';

        const foodsClone = $('#foods-wrapper')[0].cloneNode(true);
        $('.foods-box').map((i,foodBox) => {
            $(foodBox).html(foodsClone.innerHTML);
        });

        const ddfDate   = $(menuModal).find('form#ddf-form input#ddf-date');
        $(ddfDate).val(date);

        const data = JSON.stringify({date: date,type : 'on-show-modal'});
        get_menus(data);
    });

    $('#menu-modal')
        .on('keyup','.search-food-input',function () {
            const input = $(this);
            const txt = input.val();
            const menuModal = $('#menu-modal');
            const activePanel = menuModal.find('.tab-pane.active');
            const foodsTbl = $(activePanel).find('.foods-tbl');
            //let arr = [];
            let rows = $(foodsTbl).find('tr');
            rows.filter((i,row) => {
                let foodTitle = row.cells[0].innerText;
                const td = row.cells[0];

                if(foodTitle.includes(txt)) {
                    // arr.push(foodTitle);
                    $(row).removeClass('d-none');
                    let regStr   = "("+ txt +")";
                    let regExp   = new RegExp(regStr,'g');
                    foodTitle    = foodTitle.replace(regExp,"<strong class='bg-success' style='border-radius: 3px'>"+txt+"</strong>");
                    td.innerHTML = foodTitle;
                }
                else {
                    $(row).addClass('d-none');
                }
            });
        })
        .on('keyup','.search-desert-input',function () {
            const input = $(this);
            const txt = input.val();
            const menuModal = $('#menu-modal');
            const activePanel = menuModal.find('.tab-pane.active');
            const dessertTbl = input.closest('table');
            //let arr = [];
            let rows = $(dessertTbl).find('tbody tr');
            rows.filter((i,row) => {
                let title = row.cells[1].innerText;
                const td = row.cells[1];

                if(title.includes(txt)) {
                    // arr.push(title);
                    $(row).removeClass('d-none');
                    let regStr   = "("+ txt +")";
                    let regExp   = new RegExp(regStr,'g');
                    title        = title.replace(regExp,"<strong class='bg-success' style='border-radius: 3px'>"+txt+"</strong>");
                    td.innerHTML = title;
                }
                else {
                    $(row).addClass('d-none');
                }
            });
        })
        .on('click','.remove-chosen-food',function () {
            const btn     = $(this);
            const foodId  = btn.attr('data-id');
            // const panelId = btn.attr('data-parent');
            const tabPane = btn.closest('.tab-pane');
            const addBtn  = $(tabPane).find('.foods-tbl button[data-id='+foodId+'][class="btn btn-sm add-to-list-btn btn-warning"]');

            const menuId = btn.attr('data-menu');
            if(jQuery.isNumeric(menuId)) {
                const confirm1 = confirm('هشدار !!!: با حذف غذا از منو تمامی رزرو های صورت گرفته این منو حذف می گردد و قابل بازگشت نیست. ادامه می دهید؟');
                if(confirm1) {
                    let confirm2 = confirm('مبالغ رزرو به حساب کاربران بازگشت داده شود؟ ok=بله cancel=خیر');
                    let confirm3 = confirm('با حذف غذا، منو دسرهای آزاد در صورت وجود حذف شود؟ (در صورتی که غذای دیگری در منو وجود ندارد «بله = ok» گزینه مناسبی است) ok=بله cancel=خیر');
                    if(!confirm2)
                        confirm2 = "0";
                    if(!confirm3)
                        confirm3 = "0";

                    const modalDimmer = $('#modal-dimmer');
                    modalDimmer.dimmer('show');

                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/define-day-food/cancel-menu',
                        data: JSON.stringify({m_id: menuId,pay_back:confirm2,del_dessert:confirm3}),
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            modalDimmer.dimmer('hide');
                            console.log(data);
                            if(data.status === 200) {
                                btn.closest('.card').remove();
                                $(addBtn).removeAttr('disabled').removeClass('btn-warning').addClass('btn-info').html('افزودن به لیست');
                            }
                            alert(data.res);
                        },
                        error: function (error) {
                            modalDimmer.dimmer('hide');
                            alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                            //location.reload();
                            console.log(error)
                        }
                    });
                }
            }
            else {
                btn.closest('.card').remove();
                $(addBtn).removeAttr('disabled').removeClass('btn-warning').addClass('btn-info').html('افزودن به لیست');
            }
        })
        .on('click','.get-prices',function () {
            const btn = $(this);
            const mixId = btn.attr('id');
            const rId = mixId.split('-')[3];

            const modal   = $('#menu-modal');
            const tabPane = modal.find('.tab-content .tab-pane.active');
            const panelId = $(tabPane).attr('id');

            const chosenFoodAccord = modal.find('#'+panelId+'-chosen-foods');
            const rows = $(chosenFoodAccord).find('.card-header.food-title');
            let rowIds = [];
            rows.map((k, row) => {
                let mixId = $(row).attr('id');
                rowIds.push(mixId.split('-')[2])
            });
            if(rowIds.length === 0) {
                alert("منو غذایی خالی است!");
                return false;
            }

            let meal = "صبحانه";
            if (panelId === "lu")
                meal = "نهار";
            else if (panelId === "dn")
                meal = "شام";
            else if (panelId === "midd")
                meal = "میان وعده";

            $('#modal-dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/define-day-food/get-prices',
                data: JSON.stringify({
                    meal: meal,
                    r_id: rId,
                    food_id: rowIds,
                }),
                dataType: 'json',
                dataContent: 'application/json',
                processData: false,
                success: function (data) {
                    $('#modal-dimmer').dimmer('hide');
                    if (data.status === 200)
                        $('#price-list').html(data.res);
                    else
                        alert(data.res);
                },
                error: function (data) {
                    $('#modal-dimmer').dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    location.reload();
                }
            });
        })
        .on('click','.get-ddf',function () {
            const btn   = $(this);
            const mixId = btn.attr('id');
            const rId   = mixId.split('-')[3];

            const form       = $('#ddf-form');
            const ddfDateInp = form.find('input#ddf-date');
            const ddfDate    = $(ddfDateInp).val();
            const data       = JSON.stringify({date: ddfDate, r_id: rId ,type: 'on-rest-btn-click'});
            get_menus(data);
        })
        .on('change','.collection-check',function () {
            const ch    = $(this);
            const idAttr = ch.attr('id');
            const id     = idAttr.split('-')[1];
            const div    = $('#restlist-'+id);
            if(ch.is(':checked'))
                div.slideDown();
            else
                div.css('display','none');
        })
        .on('submit','#ddf-form',function (e) {
            e.preventDefault();
            const form      = $(this);
            const tabPane  = form.find('.tab-pane.active');
            const panelId  = $(tabPane).attr('id');
            const data = new FormData(this);
            $('#modal-dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/define-day-food/store',
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#modal-dimmer').dimmer('hide');
                    console.log(data);
                    if (data.status === 200) {
                        let menuIds = data.id;
                        menuIds.map( (item,k) => {
                            const rBtn = $('#menu-modal #'+panelId+'-chosen-foods').find('button.remove-chosen-food[data-id="'+item.food_id+'"]');
                            $(rBtn).attr('data-menu',item.menu_id);
                        })
                    }
                    alert(data.res);
                },
                error: function (error) {
                    $('#modal-dimmer').dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    // location.reload();
                    console.log(error)
                }
            });
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
        .on('click','.check-all',function () {
            const ch  = $(this);
            const tbl = ch.closest('table');
            const rows  = $(tbl).find('tbody tr');
            rows.map((k,row) => {
                let tr = $(row);
                tr[0].cells[0].children[0].defaultChecked = !tr[0].cells[0].children[0].defaultChecked;
            });
        })
        .on('click','.close-btn',function () {
            const btn = $(this);
            const row = btn.closest('.row');
            $(row).remove();
        })
        .on('focus','.close-at-time',function () {
            $(this).persianDatepicker({
                initialValue: false,
                autoClose:true,
                format: 'YYYY-MM-DD HH:mm:ss',
                formatter: function (unixDate) {
                    var self = this;
                    var pdate = new persianDate(unixDate);
                    pdate.formatPersian = false;
                    return pdate.format(self.format);
                }
            });
        });

    $('.foods-box').on('click','.add-to-list-btn',function () {
        const btn       = $(this);
        const row       = btn.closest('tr');
        const foodTitle = row[0].cells[0].innerText;
        const foodId    = btn.attr('data-id');
        const tabPane   = btn.closest('.tab-pane');
        const panelId   = $(tabPane).attr('id');
        const accord    = $(tabPane).find('#'+panelId+'-chosen-foods');
        const form      = $('#ddf-form');
        const ddfDateInp = form.find('input#ddf-date');
        const ddfDate   = $(ddfDateInp).val();
        btn.removeClass('btn-info').addClass('btn-warning').html('<i class="fa fa-check"></i>').attr('disabled','disabled');

        $(accord).find('.card.undefined-food-alert').remove();

        const commonChoose = '<input type="checkbox" class="align-middle" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'common'+']" checked>';
        const deActive     = '<input type="checkbox" class="align-middle" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'active'+']" checked>';
        const halfRes      = '<input type="checkbox" class="align-middle" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'half_res'+']">';

        const ugw = $('#user-group-wrapper');
        const ugwCopy = ugw[0].children;
        const rowsLength = $(ugwCopy)[0].children[0].rows.length;
        for (let i = 0; i < rowsLength; i++) {
            const userGroupId = $(ugwCopy)[0].children[0].rows[i].cells[0].children[0].value;
            $(ugwCopy)[0].children[0].rows[i].cells[0].children[0].name = "ddf["+ddfDate+"]["+panelId+"]["+foodId+"][user_group]["+userGroupId+"][is]";
            $(ugwCopy)[0].children[0].rows[i].cells[3].children[0].name = "ddf["+ddfDate+"]["+panelId+"]["+foodId+"][user_group]["+userGroupId+"][count]";
        }
        $(ugwCopy).removeClass('d-none');
        const userGroups = ugwCopy[0].outerHTML;
        const groupWarning = "<div class='card card-body bg-warning text-dark p-1'>\n"+
            "    <p class='mb-0'><i class='fa fa-warning'></i> در صورتی که گروه انتخاب شده ای را اکنون از حالت انتخاب خارج کنید و منو را ذخیره کنید رزرو های صورت گرفته آن گروه حذف نخواهند شد. </p>\n"+
            "</div>";

        const ew = $('#event-wrapper');
        const ewCopy = ew[0].children;
        const ewRowsLength = $(ewCopy)[0].children[1].rows.length;
        for (let i = 0; i < ewRowsLength; i++) {
            const eventId = $(ewCopy)[0].children[1].rows[i].cells[0].children[0].value;
            $(ewCopy)[0].children[1].rows[i].cells[0].children[0].name = "ddf["+ddfDate+"]["+panelId+"]["+foodId+"][event]["+eventId+"]";
        }
        $(ewCopy).removeClass('d-none');
        const events = ewCopy[0].outerHTML;
        const eventWarning = "<div class='card card-body bg-warning text-dark p-1'>\n"+
            "    <p class='mb-0'><i class='fa fa-warning'></i> در صورتی که رویداد انتخاب شده ای را اکنون از حالت انتخاب خارج کنید و منو را ذخیره کنید رزرو های صورت گرفته آن رویداد حذف نخواهند شد. </p>\n"+
            "</div>";

        const maxResEveryOne = '<input type="number" class="form-control" style="width: 80px; text-align: center" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'max_res'+']" value="1">';
        const maxResTotal    = '<input type="number" class="form-control" style="width: 80px; text-align: center" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'max_res_total'+']" value="0">';

        const dessertSelectType = '<label class="d-block">\n' +
            '   <input type="radio" class="align-middle" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'dessert_select'+']" value="0" checked> انتخاب دسر با انتخاب کاربر(سلف سرویس) \n' +
            '</label> \n' +
            '<label class="d-block">\n' +
            '   <input type="radio" class="align-middle" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'dessert_select'+']" value="1"> دسر ثابت همراه غذا \n' +
            '</label> \n';

        const dsw = $('#desserts-wrapper');
        const dswCopy = dsw[0].children;
        const dswRowsLength = $(dswCopy)[0].children[1].rows.length;
        for (let i = 0; i < dswRowsLength; i++) {
            const dessertId = $(dswCopy)[0].children[1].rows[i].cells[0].children[0].value;
            $(dswCopy)[0].children[1].rows[i].cells[0].children[0].name = "ddf["+ddfDate+"]["+panelId+"]["+foodId+"][dessert]["+dessertId+"]";
        }
        $(dswCopy).removeClass('d-none');
        const desserts = dswCopy[0].outerHTML;

        const hasGarnish = '<select class="form-control" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'has_garnish'+']">\n' +
                           '     <option value="1">دارد - رایگان</option>\n' +
                           '     <option value="2">دارد - اخذ بخشی از قیمت تمام شده</option>\n' +
                           '     <option value="3">دارد - اخذ تمام قیمت تمام شده</option>\n' +
                           '     <option value="4">ندارد</option>\n' +
                           '</select>';

        const closeAt = '<input type="text" class="form-control close-at-time ltr text-left" style="width: 250px" name="ddf['+ddfDate+']['+panelId+']['+foodId+']['+'close_at'+']" value="" placeholder="0000-00-00 00:00:00 فرمت" autocomplete="off">';

        const accordContent = $(accord).html();
        let showAccord = '';
        let ariaExpandAccord = 'false';
        if(accordContent === '') {
            showAccord = 'show';
            ariaExpandAccord = 'true';
        }

        $(accord).append('<div class="card">\n' +
            '    <div class="card-header food-title pt-1 pb-1" id="chosen-accord-'+foodId+'" style="background-color: #2dde98">\n' +
            '       <button class="btn btn-link btn-sm text-white" type="button" data-toggle="collapse" data-target="#collapse'+foodId+'" aria-expanded="'+ariaExpandAccord+'" aria-controls="collapse'+foodId+'">\n' +
            '           '+ foodTitle +'\n' +
            '       </button>\n' +
            '       <button type="button" class="btn btn-light btn-sm remove-chosen-food float-left" data-id="'+foodId+'" data-parent="'+panelId+'"><i class="fa fa-times"></i></button>\n' +
            '    </div>\n' +
            '    <div id="collapse'+foodId+'" class="collapse '+showAccord+'" aria-labelledby="chosen-accord-'+foodId+'" data-parent="#'+panelId+'-chosen-foods">\n' +
            '        <div class="card-body">\n' +
            '            <div class="form-group">\n' +
            '                   <label>' +deActive+
            ' فعال/غیرفعال کردن امکان رزرو این غذا                       '+
            '                   </label>\n' +
            '            </div><hr> \n' +
            '            <div class="form-group">\n' +
            '                   <label>' +commonChoose+
            ' انتخاب برای منو عادی                       '+
            '                   </label>\n' +
            '            </div><hr> \n' +
            '            <div class="form-group">\n' +
            '                   <label>' +halfRes+
            ' به صورت نیم پرس قابل رزرو باشد                       '+
            '                   </label>\n' +
            '            </div><hr> \n' +
            '            <div class="form-group">\n' +
            '                <label>انتخاب برای مراسمات</label> \n' +
            '                <div class="table-responsive"> \n' +
            '                   '+events+ '\n' +
            '                </div>' +eventWarning+' \n' +
            '            </div><hr> \n' +
            '            <div class="form-group">\n' +
            '                <label>تنظیمات گروه های کاربری</label> \n' +
            '                <div class="table-responsive"> \n' +
            '                   '+userGroups+ '\n' +
            '                </div> ' +groupWarning+' \n' +
            '            </div><hr>\n' +
            '            <div class="form-group">\n' +
            '                <label>سقف مجموع غذای قابل رزرو</label> \n' +
            '                <span class="small text-muted">برابر تعداد غذایی که اراءه می شود| 0=نامحدود</span> \n' +
            '                   '+maxResTotal+ '\n' +
            '                <span class="text-muted small">این محدودیت فقط برای غذا ها اعمال میشود.دسر آزاد محدودیت رزرو ندارد</span> \n' +
            '            </div><hr>\n' +
            '            <div class="form-group">\n' +
            '                <label>حداکثر رزرو هر کاربر</label> \n' +
            '                   '+maxResEveryOne+ '\n' +
            '                <span class="text-muted small">این محدودیت فقط برای غذا ها اعمال میشود.دسر آزاد محدودیت رزرو ندارد</span> \n' +
            '            </div><hr>\n' +
            '            <div class="form-group">\n' +
            '               '+ dessertSelectType + '\n'+
            '            </div><hr>\n' +
            '            <div class="row">\n' +
            '                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">\n' +
            '                    <div class="form-group">\n' +
            '                        <label>نوع دورچین <strong class="text-danger">*</strong></label> \n' +
            '                        '+hasGarnish+ '\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">\n' +
            '                    <div class="form-group">\n' +
            '                        <label>منو دسر</label> \n' +
            '                        <div class="table-responsive"> \n' +
            '                        '+desserts+ '\n' +
            '                        </div> \n' +
            '                    </div>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            <hr>\n' +
            '            <div class="form-group">\n' +
            '                <label>زمان غیر فعال سازی این منو</label>\n' +
            '            ' + closeAt + '\n' +
            '            </div><hr> \n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>');
    });

    $('#nextWeek').on('click',function () {
        $('#ddf_dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/define-day-food/next-week',
            data: {},
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                if (data.status === true) {
                    $('#week-box').empty().html(data.res);
                    /*$('.selectpicker').each(function () {
                        var $selectpicker = $(this);
                        $selectpicker.selectpicker();
                    });*/
                }
                else {
                    alert(data.res);
                }
                $('#ddf_dimmer').dimmer('hide');
            },
            error: function (error) {
                $('#ddf_dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
                //console.log(error)
            }
        });
    });
    $('#currWeek').on('click',function () {
        $('#ddf_dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/define-day-food/curr-week',
            data: {},
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                if (data.status === true) {
                    $('#week-box').empty().html(data.res);
                    /*$('.selectpicker').each(function () {
                        var $selectpicker = $(this);
                        $selectpicker.selectpicker();
                    });*/
                }
                else {
                    alert(data.res);
                }
                $('#ddf_dimmer').dimmer('hide');
            },
            error: function (data) {
                $('#ddf_dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
            }
        });
    });
    $('#prevWeek').on('click',function () {
        $('#ddf_dimmer').dimmer('show');
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/define-day-food/prev-week',
            data: {},
            dataType: 'json',
            dataContent: 'application/json',
            processData: false,
            success: function (data) {
                if (data.status === true) {
                    $('#week-box').empty().html(data.res);
                    /*$('.selectpicker').each(function () {
                        var $selectpicker = $(this);
                        $selectpicker.selectpicker();
                    });*/
                }
                else {
                    alert(data.res);
                }
                $('#ddf_dimmer').dimmer('hide');
            },
            error: function (data) {
                $('#ddf_dimmer').dimmer('hide');
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
            }
        });
    });
});
