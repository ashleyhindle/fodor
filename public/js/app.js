function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function updateProvisionLog(provisioningLog) {
    var id = provisioningLog.attr('data-id');
    var uuid = provisioningLog.attr('data-uuid');

    $.getJSON("/provision/log/" + id + "/" + uuid, function(data) {
        if ('error' in data) {
            provisioningLog.html('Awaiting connection...');
        } else if ('status' in data && data['status'] == 'ready') {
            window.location.replace("/provision/ready/" + id + "/" + uuid);
        } else {
            $.each(data, function (key, logInfo) {
                if (logInfo.length > 1) {
                    $('<div />').text(logInfo).appendTo(provisioningLog);

                    $("#provisioningLog").animate({ scrollTop: $('#provisioningLog').prop("scrollHeight")}, 1000);
                }
            });
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
            location.reload();
        }

        waitingProgress.css('width', (width + getRandomInt(4, 8)) + '%');
        setTimeout(updateWaitingProgress, getRandomInt(3000, 4500), waitingProgress);
    });
}

$(document).ready(function() {
    var waitingProgress = $('#waitingProgress');
    if (waitingProgress.length > 0) {
        setTimeout(updateWaitingProgress, getRandomInt(3000, 4500), waitingProgress);
    }

    var provisioningLog = $('#provisioningLog');
    if (provisioningLog.length > 0) {
        setTimeout(updateProvisionLog, 2000, provisioningLog);
    }
});
