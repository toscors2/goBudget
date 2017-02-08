$('document').ready(function() {

    figureDailyBudget();
    getUnprocessedCount();

    $.post('lib/util/getTender.php', {'request': 'select'}, function(data) {
        $('#tender, #transferTender').html(data);
    });

    $.post('lib/util/getType.php', function(data) {
        $('#qeType').html(data);
    });

    $.post('lib/util/getFamily.php', function(data) {
        $('#qeCategory').html(data);
    });

    var today   = getToday();
    var transID = null;

    function figureLeftPosition(div) {
        var thisWidth = $(div).width();

        if(thisWidth != undefined) {
            var parentDiv   = $(div).parent().attr('id');
            var parentWidth = $(div).parent().width();
            var left        = (parentWidth - thisWidth) / 2;
            $(div).css({left: left + 'px'});
        }
    }

    function figureTopPosition(div) {

        var thisHeight = $(div).height();

        if(thisHeight != undefined) {
            var parentDiv    = $(div).parent().attr('id');
            var parentHeight = $(div).parent().height();
            var top          = (parentHeight - thisHeight) / 2;
            $(div).css({'top': top + 'px'});
        }
    }

    function getUnprocessedCount() {

        $.getJSON('lib/util/getUnprocessedCount.php', function(data) {
            $('#numItemsUnprocessed').text(data.unprocessedCount);
        });
    }

    function positionTests(div) {
        var thisWidth    = $(div).width();
        var thisHeight   = $(div).height();
        var parentWidth  = $(div).parent().width();
        var parentHeight = $(div).parent().height();
        var thisTop      = (parentHeight - thisHeight);
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

            $('#displayDailyBudget').text("$" + data.dailyBudget);

        });
        send.fail(function() {
            console.log('you fucked up');
        })
    }

    function loadUnprocessed() {

        $.post('lib/php/unprocessedTrans.php', function(data) {
            $('#processScreen').html(data.html);
            $('.content').hide();

            $('#processScreen').fadeIn().show();

            var thisSource = '#source' + data.transID;

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

        $(".iSource, #recurSource").autocomplete({
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
                                                             'autocomplete event: ' + event.name + "; and ui: " +
                                                             ui.item.value +
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

    function processEntry(transID, location) {

        console.log(location);

        if(location != 'recurringTrans') {
            $.getJSON('lib/util/processEntry.php', {'transID': transID}, function(data) {
                if(! data.errors) {
                    $('#lineItemDiv' + data.transID).remove();
                    $('.content').hide();
                    $('#qeScreen').fadeIn().show();
                    getUnprocessedCount();
                    updateBalances();

                } else {
                    $('.differenceOption:first').parent().data('id', data.transID);
                    $('.differenceOption:first').parent().data('source', data.source);
                    $('#difference').text(data.difference);
                    $('.content').hide();
                    $('#differenceDiv').fadeIn().show();
                    figureTopPosition('.difference p');
                }
            });
        } else {
            $.getJSON('lib/util/processEntry.php', {'transID': transID}, function(data) {
                if(! data.errors) {
                    console.log('entry processed');
                    updateBalances();

                } else {
                    console.log('errors processing entry')
                }
            });
        }

    }

    function updateBalances() {
        $.ajax('lib/util/updateBalances.php');
    }

    function getBalanceScreen() {
        $('.content, .popup').hide();

        $.post('lib/php/balanceScreen.php', function(data) {
            $('#miscPopup').html(data).show();
            $.post('lib/util/getTender.php', {'request': 'balance'}, function(data) {
                $('#balance').html(data).show();
            });
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

    function getRecurringScreen() {
        $.post('lib/php/recurringScreen.php', function(data) {
            $('.content').hide();
            $('#miscPopup').html(data).show();
            getRecurringForms();
        });
    }

    function getRecurringForms() {

        $.post('lib/util/getUpcomingX.php', function(data) {
            $('#newTrans').html(data);
            $('.dateInput').datepicker();
        });

        $.post('lib/util/getAddSourceForm.php', function(data) {
            $('#addTrans').html(data);
            autoFill();
            $('#recurStart').datepicker();
            getDueOnOption($('#frequency').val());
        });

        $.post('lib/util/getUnpaidX.php', function(data) {
           $('#payTrans').html(data);
            $('.payInfo').datepicker();
            $.post('lib/util/getTender.php', {'request':'select'}, function(data) {
                $('.recurTender').html(data);
            });
        });
    }

    function getDueOnOption(frequency) {

        $.post('lib/util/getDueOn.php', {'frequency': frequency}, function(data) {
            $('#dueOn').html(data);
        });

    }

    function addToPay(sendData) {
        $.post('lib/util/processNewX.php', sendData, function(data) {

            errors = data.error.status;
            divID = data.divID;

            if(!errors) {
                $('#' + divID).remove();
            } else {
                errorMessage = data.error.message;
            }

        }, 'json');
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
        $.post("lib/util/processQuickEntry.php", sendData, function(data) {
            transID  = data.transID;
            tips     = data.tips;
            transfer = data.transfer;
            amount   = data.amount;
            savings  = Math.floor(amount * .1);
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

                    $processTips ? $.post('lib/util/processLineItem.php', sendData, function(data) {
                        processEntry(data.transID, 'tips');
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

        var receiptID = $(this).attr('id');
        var lineItem  = $(this).parent().parent().parent().attr('id');

        // close, delete, process buttons
        switch(button) {
            case 'process':
                processEntry(transID, 'button');
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
        var formID      = '#lineItemForm';

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

        $.post('lib/util/processLineItem.php', formData, function(data) {
            transID = data.transID;

            loadItemBox(data.transID);
            $('.qty').val(1);
            $('.reset').val('');
            $('#iNumber').focus();

            if(data.transfer == 'select') {
                $('.content').hide();
                $('#transferDiv').fadeIn().show();
            } else {
                if(data.difference == 0 && ! data.quickEntry) {
                    processEntry(transID, 'processLineItem');
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

        switch(footerBtn) {
            case 'quickEntry':
                $('.content').hide();
                $('#qeScreen').fadeIn().show();
                break;
            case 'processTrans':
                loadUnprocessed();
                break;
            case 'recurring':
                getRecurringScreen();
                break;
            case 'balances':
                getBalanceScreen();
                break;
            case 'reports':
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

        switch(button) {
            case 'misc':
                iName     = 'MISC';
                iCategory = 'MISC';
                break;
            case 'tax':
                iName     = 'SALES TAX';
                iCategory = 'TAXES';
                break;
            case 'update':
                alert('feature coming soon');
                break;
            case 'edit':
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

            $.post('lib/util/processLineItem.php', sendData, function(data) {
                $(data.lineItem).appendTo('#leftToProcessDiv' + data.transID);
                $('#differenceDiv, .content').hide();
                $('.content').css('opacity', 'initial');
                $('#qeScreen').show();
                processEntry(data.transID, 'processLineItem');
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
                    $.post('lib/util/processLineItem.php', sendData, function(data) {
                        processEntry(data.transID, 'processLineItem');
                    }, 'json');
                    processEntry(data.transID, 'type');
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

        var sendData = {'period': period, 'category': category};

        $.post('lib/util/getCatItems.php', sendData, function(data) {
            $('#miscPopup').html(data.html);
            $('#catDetail').fadeIn().show();
        }, 'json');

    });

    //gets screen to change period
    $('body').on('click', '.timePeriod', function() {
        var period = $(this).data('period');
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
    });

    //gets period income summary
    $('body').on('click', '.periodSummary', function() {
        type   = $(this).data('type');
        period = $(this).data('period');

        console.log("period Summary Type: " + type);
        console.log("period Summary Period: " + period);

        if(type = 'inc') {
            $.post('lib/util/getIncSummary.php', {'period': period}, function(data) {
                $('#miscPopup').html(data);
                $('#reportScreen').hide();
                $('#miscPopup, #incSummary').show();
            })
        }

    });

    //gets period income type detail
    $('body').on('click', '.incSummary', function() {
        type   = $(this).data('type');
        period = $(this).data('period');

        sendData = {'type': type, 'period': period};

        $.post('lib/util/getIncDetails.php', sendData, function(data) {
            $('#miscPopup').html(data);
            $('#incDetail').show();
        })
    });

    //changes dueOn options on frequency changes
    $('body').on('change', '#frequency', function() {
        getDueOnOption($('#frequency').val());
    });

    //gets recurring transaction from addTransForm
    $('body').on('click', '.Xctrl', function() {

        button = $(this).attr('id');

        switch(button) {
            case 'resetX':
                alert("Feature Coming Soon");
                break;
            case 'addX':
                sendData = $('#addTransForm').serialize();
                console.log(sendData);
                $.post('lib/util/processNewRecur.php', sendData, function(data) {

                    errors = data.status;

                    console.log(errors);

                    if(! errors) {
                        $('.reset').val('');
                        $('.recurSource').focus();
                    } else {
                        alert("Error Saving Transaction");
                    }
                }, 'json');
                break;
            case 'cancelX':
                $('#upcomingLnk').click();
                break;
            default:

        }

    });

    //processes new upcoming recurring transactions
    $('body').on('click', '.upcomingXCtrl', function() {

        button   = $(this).data('switch');
        recurID  = $(this).data('recurid');
        xid      = $(this).data('xid');
        formID   = "#form-" + xid;
        amountID = "#amount-" + xid;
        formData = $(formID).serialize();
        amount   = $(amountID).val();

        if(amount > 0) {
            sendData = formData + "&recurID=" + recurID + "&xid=" + xid +"&button=" + button;
        } else {
            alert("Please Enter An Amount Before Adding to Pay Screen");
            sendData = null;
        }

        console.log(button);

        if(sendData != null) {
            addToPay(sendData);
        }

    });

    //process recurring payment
    $('body').on('click', '.recurPayBtn', function() {

        transID = $(this).data('id');
        status = $(this).data('status');

        sendData = $('#recurPaymentForm-' + transID).serialize();

        console.log(sendData);

        $.post('lib/util/updateUpcomingX.php', {'transID':transID}, function() {
            console.log('upcomingX.php updated');
            $('#' + transID).parent().remove();
        });

        if(status == 'payNow') {
            $.post('lib/util/processQuickEntry.php', sendData, function(data) {
                $.post('lib/util/processLineItem.php', function(data) {

                    transID = data.transID;
                    processEntry(transID, 'recurringTrans');
                    console.log('recurring transaction completed');
                    //$('.content').hide();
                    //$('#miscPop').show();
                    //$('#payTransLnk').click();
                }, 'json');
            }, 'json');
        }

    });

});

