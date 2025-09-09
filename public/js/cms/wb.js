$(function () {
    "use strict";
    $('.req-refer').on('click', function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#week-box')
        .on('change', 'select[name="collections"]', function (e) {
            const colId = $(this).val();
            if(!jQuery.isNumeric(colId))
                return false;

            const data = JSON.stringify({'colId' : colId,'type' : 'col'});
            self_change(data);
        })
        .on('change', 'select[name="rests"]', function (e) {
            const restId = $(this).val();
            if(!jQuery.isNumeric(restId))
                return false;

            const data = JSON.stringify({'restId' : restId,'type' : 'rest'});
            self_change(data);
        })
        .on('click','.edit-modal',function () {
            const btn   = $(this);
            const date  = btn.attr('data-date');
            const dataC = btn.attr('data-c');
            const dataR = btn.attr('data-r');
            const dataM = btn.attr('data-m');

            const modalDimmer = $('#modal-dimmer');
            const editModal   = '#editModal';

            $(editModal).find('.modal-header #modal-date').text(date);

            modalDimmer.dimmer('show');
            $(editModal).modal();
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reservation/make-order-modal',
                data: JSON.stringify({
                    'date'  : date,
                    'data_c': dataC,
                    'data_r': dataR,
                    'data_m': dataM,
                }),
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                success: function (data) {
                    modalDimmer.dimmer('hide');
                    if (data.status === 200) {
                        $(editModal).find('#modal-body-wrapper').html(data.res);
                        fire_tooltip_popover();
                    }
                    else
                        $(editModal).find('#modal-body-wrapper').html('<strong class="text-danger">'+data.res+'</strong>');
                },
                error: function (error) {
                    modalDimmer.dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    location.reload();
                }
            });
        })
        .on('click','.week-play-btn',function () {
            const wbDimmer = $('#week-box-dimmer');
            const btn = $(this);
            const id = btn.attr('id');
            wbDimmer.dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reservation/week-play',
                data: JSON.stringify({id: id}),
                dataType: 'json',
                dataContent: 'application/json',
                processData: false,
                success: function (data) {
                    wbDimmer.dimmer('hide');
                    if (data.status === 200)
                        $('#week-box').html(data.res);
                    else
                        alert(data.res);

                },
                error: function (err) {
                    wbDimmer.dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    location.reload();
                }
            });
        });

    $('#editModal')
        .on('click','table#menu-table button.add-to-order',function () {
            const btn  = $(this);
            const dataMN = btn.attr('data-mu');
            const dataRS = btn.attr('data-rs');

            const menuRow = btn.closest('tr.menu-row');
            const menuRowTitle = $(menuRow)[0].cells[0].innerText;
            const dataPS       = $(menuRow)[0].cells[2].children[0].attributes['data-ps'].value;
            const menuRowPrice = dataPS.split('|')[0];
            const menuRowDisAmount = dataPS.split('|')[1];
            const menuRowDisCount  = parseInt($(menuRow)[0].cells[2].children[1].innerText);

            const orderForm      = $('#editModal form#order-form');
            const orderTable     = $(orderForm.find('table#order-table'));
            const sumRow         = orderTable.find('tbody tr#sum-row');
            const menuTable      = $('#editModal table#menu-table');
            const menuTableTbody = menuTable.find('tbody');


            const sumRowCount = $(sumRow).find('#sum-count');
            const sumRowCountValue = parseInt($(sumRowCount).text());
            $(sumRowCount).text(sumRowCountValue + 1);

            const orderInput = "<input type='hidden' name='order[menu]["+ dataMN +"][id]' value='"+ dataMN +"' class='must-be-remove'>\n" +
                               "<input type='hidden' name='order[menu]["+ dataMN +"][count]' value='1' class='must-be-remove'>";
            const dataPrice  = menuRowPrice + "|" + menuRowDisAmount;

            $(orderTable).find('tr.non-order-row').remove();

            const existsOrderRow = $(orderTable).find('tr#order-row-'+ dataMN);

            const disCount = menuRowDisCount > 0 ? 1 : 0;
            const disRemains = $(menuTableTbody).find('.dis-remain');
            disRemains.map((k,disRemain) => {
                let disCountRemain = parseInt($(disRemain).text());
                if(disCountRemain > 0)
                    $(disRemain).text(disCountRemain - 1);
            });

            make_bill(parseInt(menuRowPrice),parseInt(menuRowDisAmount),1,disCount,'add');

            if(existsOrderRow.length === 1) {
                $(existsOrderRow)[0].cells[2].children[0].innerText = parseInt($(existsOrderRow)[0].cells[2].innerText) + 1;
                const countInputExists = $(existsOrderRow)[0].cells[0].children;
                if(countInputExists.length >= 1) {
                    const inputExists = $(existsOrderRow)[0].cells[0].children[1];
                    $(inputExists)[0].attributes['value'].value = parseInt(inputExists.attributes['value'].value) + 1;
                }
                else {
                    let currentText = $(existsOrderRow)[0].cells[0].innerText;
                    $(existsOrderRow)[0].cells[0].innerHTML = currentText + orderInput;
                }

                $(existsOrderRow)[0].cells[1].children[1].innerText = parseInt($(existsOrderRow)[0].cells[1].children[1].innerText) + disCount;
            }
            else {
                const orderRow = "<tr class='order-row' id='order-row-" + dataMN + "'>\n" +
                    "    <td class='align-middle'>\n" +
                    menuRowTitle + "\n" +
                    orderInput + "\n" +
                    "    </td>\n" +
                    "    <td class='align-middle'> " +
                    "        <button type='button' class='btn remove-order' data-ps='" + dataPrice + "' data-rs='" + dataRS + "' data-mu='" + dataMN + "'><i class='fa fa-2x text-danger fa-trash'></i></button>\n" +
                    "        <span class='dis-count d-none'>"+ disCount +"</span>\n" +
                    "    </td>\n" +
                    "    <td class='align-middle'>" +
                    "        <a class='btn btn-dribbble'>1</a>\n" +
                    "    </td>\n" +
                    "    <td class='align-middle'>\n" +
                    "        <a class='btn w-100'>" + menuRowPrice + "</a>\n" +
                    "    </td>\n" +
                    "</tr>";
                $(sumRow).before(orderRow);
            }
        })
        .on('click','table#order-table button.remove-order',function () {
            const btn  = $(this);
            const dataMN = btn.attr('data-mu');
            const dataRS = btn.attr('data-rs');
            const dataPS = btn.attr('data-ps');

            const orderForm        = $('#editModal form#order-form');
            const orderTable       = $(orderForm.find('table#order-table'));
            const sumRow           = orderTable.find('tbody tr#sum-row');
            const menuTable        = $('#editModal table#menu-table');
            const menuTableTbody   = menuTable.find('tbody');
            const orderRow         = btn.closest('tr.order-row');
            const orderRowPrice    = dataPS.split('|')[0];
            const orderRowDis      = dataPS.split('|')[1];
            const orderRowCount    = $(orderRow)[0].cells[2].innerText;
            const orderRowDisCount = parseInt($(orderRow)[0].cells[1].children[1].innerText);

            const disRemains = $(menuTableTbody).find('.dis-remain');
            disRemains.map((k,disRemain) => {
                let disCountRemain = parseInt($(disRemain).text());
                $(disRemain).text(disCountRemain + orderRowDisCount);
            });

            const sumRowCount = $(sumRow).find('#sum-count');
            const sumRowCountValue = parseInt($(sumRowCount).text());
            $(sumRowCount).text(sumRowCountValue - parseInt(orderRowCount));
            console.log(dataRS);
            if(dataRS !== "undefined") {
                const modalDimmer = $('#modal-dimmer');
                modalDimmer.dimmer('show');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/reservation/cancel',
                    data: JSON.stringify({'rsId': dataRS}),
                    dataType: 'json',
                    contentType: 'application/json',
                    processData: false,
                    success: function (data) {
                        modalDimmer.dimmer('hide');
                        if (data.status === 200) {
                            make_bill(parseInt(orderRowPrice),parseInt(orderRowDis),parseInt(orderRowCount), orderRowDisCount, 'sub');
                            $(orderRow).remove();
                            wallet_amount(data.wallet,data.walletOwner);
                            alert(data.res);
                        }
                        else if(data.status === 101){
                            data.res.map((err,k) => {
                                alert(err);
                            })
                        }
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        modalDimmer.dimmer('hide');
                        alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                        location.reload();
                    }
                });
            }
            else {
                make_bill(parseInt(orderRowPrice),parseInt(orderRowDis),parseInt(orderRowCount), orderRowDisCount, 'sub');
                $(orderRow).remove();
            }
        })
        .on('submit','form#order-form',function (e) {
            e.preventDefault();
            const form = $(this);
            const data = new FormData(this);
            const modalDimmer = $('#modal-dimmer');

            modalDimmer.dimmer('show');

            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reservation/set',
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    modalDimmer.dimmer('hide');
                    if (data.status === 200) {
                        const orderTable = $('#editModal table#order-table');
                        orderTable.find('input.must-be-remove').remove();
                        data.reserveIds.map((reserveId,k) => {
                            let removeBtn = orderTable.find('button.remove-order[data-mu='+reserveId.menu_id+']');
                            $(removeBtn).attr('data-rs',reserveId.res_id);
                        });
                        wallet_amount(data.wallet,data.walletOwner);
                        alert(data.res);
                    }
                    else if(data.status === 101){
                        data.res.map((err,k) =>{
                            alert(err);
                        })
                    }
                    else
                        alert(data.res);

                },
                error: function (error) {
                    modalDimmer.dimmer('hide');
                    alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                    location.reload();
                }
            });
        });

    $('#change-user-form').on('submit',function (e) {
        e.preventDefault();
        const form = $(this);
        const data = new FormData(this);

        const validFeed = form.find('.valid-feedback');
        const inValidFeed = form.find('.invalid-feedback');
        $(validFeed).html('');
        $(inValidFeed).html('');

        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/reservation/change-user',
            data: data,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.status === 200) {
                    $(validFeed).html(data.res + "<strong> موجودی کاربر:<span id='wallet-other'>"+data.wallet+"</span> ریال</strong>");
                    $('.week-play-btn#currWeek').trigger('click');
                }
                else
                    $(inValidFeed).html(data.res);
            },
            error: function (error) {
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
            }
        });
    });

    const make_bill = (price,discount,count,C1,operator) => {
        // C1 = تعداد قابل تخفبف باقیمانده یا تعداد تخفیف داده شده
        const orderForm   = $('#editModal form#order-form');
        const orderTable  = $(orderForm.find('table#order-table'));

        const sumRow      = orderTable.find('tbody tr#sum-row');
        const disRow      = orderTable.find('tbody tr#discount-row');
        const totRow      = orderTable.find('tbody tr#total-row');
        const sumRowBtn   = $(sumRow).find('button#sum-value');
        const disRowBtn   = $(disRow).find('button#discount-value');
        const totRowBtn   = $(totRow).find('button#total-value');
        const sumRowValue = $(sumRowBtn).text();
        const disRowValue = $(disRowBtn).text();
        const totRowValue = $(totRowBtn).text();
        let newSumValue   = '';
        let newDisValue   = '';
        let newTotValue   = '';

        if(operator === 'add') {
            newSumValue = parseInt(sumRowValue) + (price * count);
            newDisValue = parseInt(disRowValue) + (discount * C1);
            newTotValue = parseInt(totRowValue) + ((price * count) - (discount * C1));
        }
        else {
            newSumValue = parseInt(sumRowValue) - (price * count);
            newDisValue = parseInt(disRowValue) - (discount * C1);
            newTotValue = parseInt(totRowValue) - ((price * count) - (discount * C1));
            newTotValue = newTotValue < 0 ? 0 : newTotValue;
        }

        $(sumRowBtn).text(newSumValue);
        $(disRowBtn).text(newDisValue);
        $(totRowBtn).text(newTotValue);
    };
    const wallet_amount = (amount, owner) => {
        if(owner === 'himself') {
            $('#wallet-himself').text(amount);
        }
        else
            $('#change-user-form #wallet-other').text(amount);
    };
    const self_change = (data) => {
        $.ajax({
            cache: false,
            type: 'POST',
            url: '/home/reservation/self-change',
            data: data,
            dataType: 'json',
            contentType: 'application/json',
            processData: false,
            success: function (data) {
                if (data.status === 200)
                    $('.week-play-btn#currWeek').trigger('click');
                else
                    alert(data.res);
            },
            error: function (error) {
                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                location.reload();
                //console.log(error)
            }
        });
    };
    const fire_tooltip_popover = () => {
        $(document).find('[data-toggle="tooltip"]').tooltip();
        $(document).find('[data-toggle="popover"]').popover();
    }
});
