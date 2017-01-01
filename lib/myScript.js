$('document').ready(function() {

    figureDailyBudget();
    getUnprocessedCount();

    $.post('lib/util/getTender.php', {'request': 'select'}, function(data) {
        $('#tender, #transferTender').html(data);
    });

    var today   = getToday();
    var transID = null;
    console.log(today);

    function figureLeftPosition(div) {
        var thisWidth = $(div).width();

        if(thisWidth != undefined) {
            var parentDiv   = $(div).parent().attr('id');
            var parentWidth = $(div).parent().width();
            console.log("div width being figured: " + div + " at " + thisWidth);
            console.log("parent div width being figured: " + parentDiv + " at " + parentWidth);
            var left = (parentWidth - thisWidth) / 2;
            console.log(div + " left: " + left);
            $(div).css({left: left + 'px'});
        }
    }

    function figureTopPosition(div) {

        var thisHeight = $(div).height();

        if(thisHeight != undefined) {
            console.log("div being figured: " + div + " at " + $(div).height());
            console.log("parent div being figured: " + $(div).parent().attr('id') + " at " + $(div).parent().height());
            var parentDiv    = $(div).parent().attr('id');
            var parentHeight = $(div).parent().height();
            var top          = (parentHeight - thisHeight) / 2;
            console.log(div + " top: " + top);
            $(div).css({'top': top + 'px'});
        }
    }

    function getUnprocessedCount() {

        $.getJSON('lib/util/getUnprocessedCount.php', function(data) {
            console.log(data.unprocessedCount);
            $('#numItemsUnprocessed').text(data.unprocessedCount);
        });
    }

    function positionTests(div) {
        var thisWidth    = $(div).width();
        var thisHeight   = $(div).height();
        var parentWidth  = $(div).parent().width();
        var parentHeight = $(div).parent().height();
        console.log('this width: ' + thisWidth + '; this height: ' + thisHeight + '; parent width: ' + parentWidth +
                    '; parent height: ' + parentHeight);

        var thisTop = (parentHeight - thisHeight);
        console.log(thisTop);
        $(div).css("top", thisTop);

    }

    function getToday() {
        var todaysDate = new Date();
        var dayOfWeek  = todaysDate.getDay();
        var day        = todaysDate.getDate();
        var month      = todaysDate.getMonth();
        var year       = todaysDate.getFullYear();

        weekday = findWeekDay(dayOfWeek);
        month   = findMonthName(month);

        todayDate = weekday + ", " + month + " " + day + ", " + year;

        return todayDate;
    }

    function findWeekDay(dayOfWeek) {
        switch(dayOfWeek) {
            case 0:
                dayOfWeek = 'Sunday';
                break;
            case 1:
                dayOfWeek = 'Monday';
                break;
            case 2:
                dayOfWeek = 'Tuesday';
                break;
            case 3:
                dayOfWeek = 'Wednesday';
                break;
            case 4:
                dayOfWeek = 'Thursday';
                break;
            case 5:
                dayOfWeek = 'Friday';
                break;
            case 6:
                dayOfWeek = 'Saturday';
                break;
            default:
                dayOfWeek = 'Blah';
        }

        return dayOfWeek;
    }

    function findMonthName(month) {
        switch(month) {
            case 0:
                month = 'Jan';
                break;
            case 1:
                month = 'Feb';
                break;
            case 2:
                month = 'Mar';
                break;
            case 3:
                month = 'Apr';
                break;
            case 4:
                month = 'May';
                break;
            case 5:
                month = 'Jun';
                break;
            case 6:
                month = 'Jul';
                break;
            case 7:
                month = 'Aug';
                break;
            case 8:
                month = 'Sep';
                break;
            case 9:
                month = 'Oct';
                break;
            case 10:
                month = 'Nov';
                break;
            case 11:
                month = 'Dec';
                break;
            default:
                month = 'blah';
        }

        return month;
    }

    function figureDailyBudget() {
        var send = $.post('lib/util/figureDailyBudget.php', '', null, 'json');
        send.done(function(data) {
            console.log('daily budget left: ' + data.dailyBudget);
            //console.log('dayInterval: ' + data.interval.d);
            console.log('formatted dayInterval: ' + data.difference);
            console.log('today date: ' + data.today);
            console.log('testDate: ' + data.testDate);
            console.log('budget changing trans: ' + data.test);
            $('#displayDailyBudget').text("$" + data.dailyBudget);
            // positionTests('#budgetLeft');

        });
        send.fail(function() {
            console.log('you fucked up');
        })
    }

    function loadUnprocessed() {

        $.post('lib/php/unprocessedTrans.php', function(data) {
            // console.log(data.html);
            $('#processScreen').html(data.html);
            $('.content').hide();

            $('#processScreen').fadeIn().show();

            var thisSource = '#source' + data.transID;
            console.log("thisSource: " + thisSource);

            //autoFill();

            figureLeftPosition('.processEntry');
            figureTopPosition('.processEntry');

            return (data.transID);

        }, 'json');

    }

    function loadPopup(transID) {
        $.post('lib/php/processPopup.php', {'transID': transID}, function(data) {
            $('.content').hide();
            $('#processPopup').html(data).show();
            $('#iSource').focus();
            loadItemBox(transID);
            autoFill();

        });
    }

    function autoFill(data) {

        $(".iSource").autocomplete({
                                       source: function(request, response) {
                                           console.log("data-name: " + $(this).data('id'));
                                           $.getJSON('lib/util/autofill.php', {
                                               'term'    : request.term,
                                               'request' : 'source',
                                               'function': 'complete'
                                           }, function(data) {
                                               response(data);
                                           })
                                       },
                                       select: function(event, ui) {
                                           console.log(
                                               'autocomplete event: ' + event.name + "; and ui: " + ui.item.value +
                                               "; as well as the val of input: " + $(this).data('name'));
                                       }
                                   });

        $(".iNumber").autocomplete({
                                       source: function(request, response) {
                                           console.log("data-name: " + $(this).data('id'));
                                           $.getJSON('lib/util/autofill.php', {
                                               'term'    : request.term,
                                               'request' : 'number',
                                               'function': 'complete'
                                           }, function(data) {
                                               response(data);
                                           })
                                       },
                                       select: function(event, ui) {
                                           var transID = $(this).data('id');

                                           $.getJSON('lib/util/autofill.php', {
                                               'term'    : ui.item.value,
                                               'request' : 'fill',
                                               'function': 'fillNumber',
                                               'transID' : transID
                                           }, function(data) {
                                               fillData(data);
                                           });

                                       }
                                   });

        $(".iName").autocomplete({
                                     source: function(request, response) {
                                         $.getJSON('lib/util/autofill.php', {
                                             'term'    : request.term,
                                             'request' : 'name',
                                             'function': 'complete'
                                         }, function(data) {
                                             response(data);
                                         })
                                     },
                                     select: function(event, ui) {
                                         var transID = $(this).data('id');

                                         $.getJSON('lib/util/autofill.php', {
                                             'term'    : ui.item.value,
                                             'request' : 'fill',
                                             'function': 'fillName',
                                             'transID' : transID

                                         }, function(data) {
                                             fillData(data);
                                         })
                                     }
                                 });

        $(".iCategory").autocomplete({
                                         source: function(request, response) {
                                             console.log("data-name: " + $(this).data('id'));
                                             $.getJSON('lib/util/autofill.php', {
                                                 'term'    : request.term,
                                                 'request' : 'category',
                                                 'function': 'complete'
                                             }, function(data) {
                                                 response(data);
                                             })
                                         },
                                         select: function(event, ui) {
                                             console.log(
                                                 'autocomplete event: ' + event.name + "; and ui: " + ui.item.value +
                                                 "; as well as the val of input: " + $(this).data('name'));
                                         }
                                     });

    }

    function fillData(data) {
        var iSource   = '#iSource';
        var iName     = '#iName';
        var iNumber   = '#iNumber';
        var iPrice    = '#iPrice';
        var iSize     = '#iSize';
        var iPack     = '#iPack';
        var iCategory = '#iCategory';

        // console.log(iSource + iName + iNumber + iPrice + iSize + iPack);

        $(iName).val(data.iName);
        $(iNumber).val(data.iNumber);
        $(iPrice).val(data.iPrice);
        $(iSize).val(data.iSize);
        $(iPack).val(data.iPack);
        $(iCategory).val(data.iCategory);

    }

    function loadItemBox(transID) {

        transID != null ? sendData = {'transID': transID} : sendData = null;

        $.post('lib/util/retrieveItems.php', sendData, function(data) {

            if(! data.errors) {
                $('#itemBoxItems').html(data.lineItems);
                $('#boxItemTotal').html(data.boxItemTotal);
                $('#iSource').val(data.source);

            }

        }, 'json');

    }

    function processEntry(transID) {
        $.getJSON('lib/util/processEntry.php', {'transID': transID}, function(data) {
            if(! data.errors) {
                console.log(data.savedItems + " : " + data.checkAmount);
                $('#lineItemDiv' + data.transID).remove();
                $('.content').hide();
                $('#qeScreen').fadeIn().show();
                getUnprocessedCount();
                updateBalances();

            } else {
                console.log("data-transID: " + $('.differenceOption:first').parent().data('id'));
                console.log("data-source: " + $('.differenceOption:first').parent().data('source'));
                $('.differenceOption:first').parent().data('id', data.transID);
                $('.differenceOption:first').parent().data('source', data.source);
                console.log("data-transID: " + $('.differenceOption:first').parent().data('id'));
                console.log("data-source: " + $('.differenceOption:first').parent().data('source'));
                $('#difference').text(data.difference);
                $('.content').hide();
                $('#differenceDiv').fadeIn().show();
                figureTopPosition('.difference p');
            }
        });

    }

    function updateBalances() {
        $.ajax('lib/util/updateBalances.php');
    }

    function getReconcileScreen() {
        $('.content, .popup').hide();
        $.post('lib/util/getTender.php', {'request': 'balance'}, function(data) {
            $('#reconcileBalances').html(data).show();
        });
    }

    function setReportPeriods(period) {
        $.post('lib/util/setReportPeriods.php', {'period': period}, function() {
            generateReport();
        });
    }

    function generateReport() {
        $.post('lib/php/report.php', function(data) {
            $('#reportScreen').html(data.html);
            $('#miscPopup').html(data.hiddenHTML);
        }, 'json');
        $('.content').hide();
        $('#reportScreen').show();
    }

    // sets datePicker options and resets checkboxes
    $('#transDate').datepicker();

    $("#transDate").datepicker("option", "dateFormat", "DD, M dd, yy");
    $('#transDate').val(today);
    $('#amount').val('').focus();

    // submits quickEntry Form
    $('body').on('submit', '#qeForm', function(event) {
        event.preventDefault();
        var sendData = $('#qeForm').serialize();
        console.log(sendData);
        $.post("lib/util/processQuickEntry.php", sendData, function(data) {
            transID  = data.transID;
            tips     = data.tips;
            transfer = data.transfer;
            amount   = data.amount;
            savings  = amount * .1;
            cash     = amount - savings;

            switch(true) {
                case tips:
                    sendData = {
                        'iSource'  : 'CRACKER BARREL',
                        'iTransID' : transID,
                        'iNumber'  : 2010,
                        'iName'    : 'TIPS',
                        'iCategory': 'TIPS',
                        'iPrice'   : amount * - 1,
                        'iQty'     : 1,
                        'iPack'    : null,
                        'iSize'    : null
                    };

                    $processTips = confirm(
                        "Tips Received = " + amount * - 1 + "; To Cash = " + cash * - 1 + "; To Savings = " +
                        savings * - 1);

                    $processTips ? $.post('lib/util/storeItem.php', sendData, function(data) {
                        console.log('transID from tips: ' + data.transID);
                        processEntry(data.transID);
                    }, 'json') : $('#amount').val('').focus();
                    break;
                case transfer:
                    $('.content').hide();
                    $('#transferDiv').fadeIn().show();
                    break;
                default:
                    var process = confirm("Process This Transaction? Press Cancel To Enter Another Quick Entry!");
                    if(! process) {
                        figureDailyBudget();
                        getUnprocessedCount();
                    } else {
                        loadPopup(transID);
                        console.log('transID after tip income' + transID);
                    }
            }

            $('#amount').val('').focus();

        }, 'json');

    });

    // shows process form for line items
    $('body').on('click', '.processEntry', function() {
        var transID   = $(this).data('id');
        var parentDiv = $(this).parent().parent().parent();

        loadPopup(transID);
    });

    // buttons in process form to save, delete or process
    $('body').on('click', '.lineItemBtn', function() {
        var button   = $(this).val();
        var sendData = $(this).attr('name');
        var transID  = $(this).data('transid');
        console.log(transID);
        console.log(button);
        console.log($(this).attr('id'));
        var receiptID = $(this).attr('id');
        var lineItem  = $(this).parent().parent().parent().attr('id');

        // close, delete, process buttons
        switch(button) {
            case 'process':
                processEntry(transID);
                break;
            case 'delete':
                $.post('lib/util/removeQE.php', {'transID': transID}, function(data) {
                    $('#lineItemDiv' + data.transID).remove();
                    $('.content').hide();
                    $('#qeScreen').show();
                    figureDailyBudget();
                    getUnprocessedCount();
                }, 'json');

                break;
            case 'save':
                var formData = $('#lineItemForm' + transID).serialize();
                console.log(formData);
                $('.content').hide();
                $('#qeScreen').show();
                break;
            default:
                console.log("nothing switched");
        }

    });

    // saves line items from line item form
    $('body').on('submit', '.lineItemForm', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var processItem = false;
        var dataID      = $(this).data('id');
        console.log('dataID: ' + dataID);
        var formID = '#lineItemForm';

        var source   = '#iSource';
        var name     = '#iName';
        var number   = '#iNumber';
        var category = '#iCategory';
        var price    = '#iPrice';
        var size     = '#iSize';
        var pack     = '#iPack';
        var qty      = '#iQty';

        var formData = {
            'iSource'   : $(source).val(),
            'iNumber'   : $(number).val(),
            'iName'     : $(name).val(),
            'iCategory' : $(category).val(),
            'iSize'     : $(size).val(),
            'iPack'     : $(pack).val(),
            'iQty'      : $(qty).val(),
            'iPrice'    : $(price).val(),
            'iTransID'  : dataID,
            'quickEntry': false
        };

        console.log("dataID: " + dataID);
        console.log("formID: " + formID);
        console.log("formData to storeItem.php: " + formData);

        $.post('lib/util/storeItem.php', formData, function(data) {
            transID = data.transID;
            console.log('item stored: ' + data.source);

            loadItemBox(data.transID);
            $('.qty').val(1);
            $('.reset').val('');
            $('#iNumber').focus();

            if(data.transfer == 'select') {
                $('.content').hide();
                $('#transferDiv').fadeIn().show();
            } else {
                if(data.difference == 0 && ! data.quickEntry) {
                    processEntry(transID);
                    $('#processPopup').hide();
                    $('#qeScreen').show();
                }
            }

        }, 'json');

    });

    // selects main footer functions
    $('body').on('click', '.footerMenu', function() {
        $('.footerMenu').removeClass("activeFooter");
        $(this).addClass("activeFooter");
        var footerBtn = $(this).attr('id');
        console.log('footerBtn: ' + footerBtn);

        switch(footerBtn) {
            case 'qeOption':
                $('.content').hide();
                $('#qeScreen').fadeIn().show();
                break;
            case 'processOption':
                loadUnprocessed();
                break;
            case 'cfgOption':
                alert("Feature Coming Soon");
                break;
            case 'balanceOption':
                getReconcileScreen();
                break;
            case 'reportOption':
                setReportPeriods('current');
                break;
        }
    });

    // determines what to do with difference amount
    $('body').on('click', '.differenceOption', function() {
        var button   = $(this).attr('id');
        var iTransID = $(this).parent().data('id');
        var iSource  = $(this).parent().data('source');
        var amount   = $('#difference').text();
        var iNumber  = null;
        console.log(iTransID + " : " + amount);

        switch(button) {
            case 'misc':
                console.log('buttonID: ' + button);
                iName     = 'MISC';
                iCategory = 'MISC';
                break;
            case 'tax':
                console.log('buttonID: ' + button);
                iName     = 'SALES TAX';
                iCategory = 'TAXES';
                break;
            case 'update':
                console.log('buttonID: ' + button);
                alert('feature coming soon');
                break;
            case 'edit':
                console.log('buttonID: ' + button);
                $('#differenceDiv').hide();
                $('.content').css('opacity', 'initial');
                $('.content').hide();
                $('#processPopup').show();
                break;
            default:
                console.log('error in button switching');
                break;
        }

        if(button == 'tax' || button == 'misc') {
            var sendData = {
                'iName'    : iName,
                'iNumber'  : iNumber,
                'iSource'  : iSource,
                'iPrice'   : amount,
                'iCategory': iCategory,
                'iTransID' : iTransID,
                'iQty'     : 1,
                'iSize'    : null,
                'iPack'    : null
            };

            $.post('lib/util/storeItem.php', sendData, function(data) {
                console.log('item stored from difference option');
                console.log('lineItem from difference option: ' + data.lineItem);
                $(data.lineItem).appendTo('#leftToProcessDiv' + data.transID);
                $('#differenceDiv, .content').hide();
                $('.content').css('opacity', 'initial');
                $('#qeScreen').show();
                processEntry(data.transID);
            }, 'json');

        }

    });

    //selects where to transfer to
    $('body').on('submit', '#transferForm', function(event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $.post('lib/util/setTenderCode.php', formData, function(data) {

            switch(data.type) {
                case 'transfer':
                    sendData = {
                        'iSource'   : 'PERSONAL',
                        'iTransID'  : data.transID,
                        'iNumber'   : data.iNumber,
                        'iName'     : data.iName,
                        'iCategory' : 'QUICK TRANSFER',
                        'iPrice'    : data.amount,
                        'iQty'      : 1,
                        'iPack'     : null,
                        'iSize'     : null,
                        'quickEntry': true
                    };
                    $.post('lib/util/storeItem.php', sendData, function(data) {
                        console.log('transID from quick transfer: ' + data.transID);
                        processEntry(data.transID);
                    }, 'json');
                    processEntry(data.transID);
                    $('.popup').hide();
                    $('#qeScreen').show();
                    break;
                default:
                    $('.popup').hide();
                    $('.qty').val(1);
                    $('.reset').val('');
                    $('#iNumber').focus();
                    $('#processPopup').fadeIn().show();
                    break;
            }

        }, 'json');

    });

    //update balances from reconcile screen
    $('body').on('click', '.reconcileBtn', function() {
        var code    = $(this).data('code');
        var balance = $('#' + code).val();
        console.log("data code: " + code + "; balance: " + balance);

        var sendData = {'code': code, 'balance': balance};

        $.post('lib/util/setUpdateType.php', {'type': 'reconcile'}, function() {
            $.post('lib/util/updateBalances.php', sendData, function() {
                getReconcileScreen();
            });
        });

    });

    //displays category totals from catPopBtn
    $('body').on('click', '.catPopBtn', function() {
        var family = $(this).data('family');
        var period = $(this).data('period');
        var divID  = '#' + period + family;

        //figureTopPosition(divID);

        $('.content').hide();

        $('#miscPopup, ' + divID).fadeIn().show();
    });

    //brings popup to edit items
    $('body').on('click', '.lineItems', function() {
        var lineID = $(this).attr('id');
        console.log("line ID: " + lineID);

        $.post('lib/util/getLineItem.php', {'lineID': lineID}, function(data) {
            $('#miscPopup').html(data.form);
            $('.content').hide();
            $('#miscPopup').fadeIn().show();
        }, 'json')
    });

    //submits line item edit buttons
    $('body').on('click', '.editLineItem', function() {

        var formData = $('#updateItemForm').serialize();
        var button   = $(this).data('type');
        var lineID   = $('#updateItemForm').data('id');

        console.log('edit line item form button ' + button);
        console.log('line id ' + lineID);
        console.log('form data ' + formData);

        switch(button) {
            case 'update':
                $.post('lib/util/editLineItem.php', formData);
                break;
            case 'delete':
                $.post('lib/util/deleteLineItem.php', {'lineID': lineID});
        }

        loadItemBox(null);

        $('.content').hide();
        $('#processPopup').show();

    });

    //gets category lines from report screen
    $('body').on('click', '.catLine', function() {
        var period   = $(this).data('period');
        var category = $(this).data('category');
        console.log('period: ' + period);
        console.log('category: ' + category);
        var sendData = {'period': period, 'category': category};

        $.post('lib/util/getCatItems.php', sendData, function(data) {
            $('#miscPopup').html(data.html);
            $('#catDetail').fadeIn().show();
        }, 'json');

    });

    //gets screen to change period
    $('body').on('click', '.timePeriod', function() {
        var period = $(this).data('period');
            //console.log('period: ' + period);
        $.post('lib/util/changePeriod.php', {'period': period}, function(data) {
                $('#miscPopup').html(data.html);
            $('.content').hide();
            $('#miscPopup').fadeIn().show();
        }, 'json')

    });

    //sends period change to setReportPeriod
    $('body').on('click', '.changePeriod', function() {
        var period = $(this).val();

        setReportPeriods(period);
    })

});

