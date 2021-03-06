function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function updateProvisionLog(provisioningLog) {
    var id = provisioningLog.attr('data-id');
    var uuid = provisioningLog.attr('data-uuid');

    $.getJSON("/provision/log/" + id + "/" + uuid, function(data) {
        if ('error' in data) {
            provisioningLog.html('Awaiting SSH connection...');
        } else if ('status' in data && data['status'] == 'ready') {
            window.location.replace("/provision/ready/" + id + "/" + uuid);
            return true;
        } else if ('status' in data && data['status'] == 'errored') {
            $('#erroredInfo').removeClass('hidden'); // TODO: Use a nice JS framework to handle this better.  This coupling is out of hand
            $('h2:first').hide();
            return true; // Don't set another timeout
        } else {
            $.each(data.lines, function (key, logInfo) {
                if (logInfo.length > 1) {
                    $('<div />').addClass('text-muted').addClass('logRow').text(logInfo).appendTo(provisioningLog);
                }
            });

            provisioningLog.animate({
                scrollTop: provisioningLog.prop("scrollHeight")
            }, 1000);
        }

        setTimeout(updateProvisionLog, 2000, provisioningLog);
    });
}

function updateWaitingProgress(waitingProgress) {
    var id = waitingProgress.attr('data-id'); // TODO: Replace with React or something?
    var uuid = waitingProgress.attr('data-uuid');
    var width = parseInt(waitingProgress[0].style.width.replace(/%/, ''));

    $.getJSON("/provision/waiting/" + id + "/" + uuid + ".json", function(data) {
        if (data['status'] == 'active') { // It's finished creating
            waitingProgress.css('width', '100%');
            return location.reload();
        }

        waitingProgress.css('width', (width + getRandomInt(4, 8)) + '%');
        setTimeout(updateWaitingProgress, getRandomInt(1500, 2500), waitingProgress);
    });
}

$(document).ready(function() {
    new Clipboard('.btn-copy');

    $('[data-toggle="tooltip"]').tooltip();
    $('.btn-copy').tooltip({
        title: 'Click to copy'
    });

    $('#view-provisionerScript').click(function(event) {
        event.preventDefault();
        $('#provisionerScript').toggleClass('hidden');
    });

    $('#view-fodorJson').click(function(event) {
        event.preventDefault();
        $('#fodorJson').toggleClass('hidden');
    });

    var waitingProgress = $('#waitingProgress');
    if (waitingProgress.length > 0) {
        setTimeout(updateWaitingProgress, getRandomInt(3000, 4500), waitingProgress);
    }

    var provisioningLog = $('#provisioningLog');
    if (provisioningLog.length > 0) {
        setTimeout(updateProvisionLog, 1500, provisioningLog);
    }

    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=radio]").addClass("visuallyhidden");
    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=radio]:checked").parent().addClass("success");

    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=radio]").change(function() {
        if( $(this).attr("name") ) {
            $(this).parent().addClass("success").siblings().removeClass("success")
        } else {
            $(this).parent().toggleClass("success");
        }
    });

    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=checkbox]").addClass("visuallyhidden");
    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=checkbox]:checked").parent().addClass("success");
    $(".toggle-btn:not('.noscript'):not('.disabled') input[type=checkbox]").change(function() {
        $(this).parent().toggleClass("success");
    });
});
