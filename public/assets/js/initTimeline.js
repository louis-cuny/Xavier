$(function () {
    //borne gauche de la timeline (00:00)
    min = vis.moment('00:00', 'mm:ss');
    // DOM element where the Timeline will be attached
    var container = document.getElementById('timeline');
    // Create a DataSet (allows two way data-binding)
    items = new vis.DataSet([
        {id: 'background', start: min, end: min, type: 'background'}
    ]);


    // Configuration de base de la timeline
    options = {
        minHeight: "100px",
        showCurrentTime: false,
        format: {
            minorLabels: {
                millisecond: 'SSS',
                second: 'ss',
                minute: 'mm'
            },
            majorLabels: {
                millisecond: 'mm:ss',
                second: 'mm:ss',
                minute: 'HH'
            }
        },
        //On autorise le déplacement et la suppression des items
        editable: {
            updateTime: true,
            remove: true
        },
        //Pour empêcher de mettre des temps en dessous de la seconde
        snap: snap,
        zoomMin: 8000,
        onMove: function (item, callback) {
            var index = item.title.indexOf('<small>');
            var title = item.title.slice(0, index);
            title += "<small>(" + vis.moment(item.start).format('mm:ss') + ' - ' + vis.moment(item.end).format('mm:ss') + ")</small>";
            item.title = title;
            callback(item);
        }
    };

    // Create a Timeline
    timeline = new vis.Timeline(container, items, options);


    /**
     * Initialise la timeline en faisant le lien avec la vidéo. La vidéo doit donc êter initialisée auparavant.
     * @param  {Boolean}   playerOK indique si le player a été correctement initialisé ou non
     * @return {Undefined}
     */
    initializeTimeline = function (playerOK) {
        if (playerOK) {
            var duree = player.duration();
            var zoomMin = 8000;
            if (duree < 8) {
                zoomMin = duree * 1000;
            }
            //On définit la borne droite de la frise comme étant le temps max de la vidéo
            var max = vis.moment(player.prettyDuration(), 'mm:ss');
            var newOptions = {
                min: min,
                max: max,
                start: min,
                end: max,
                zoomMin: zoomMin,
                //Pour empêcher de déplacer les items en dehors de la frise
                onMoving: function (item, callback) {
                    var debut = vis.moment(item.start);
                    //ne pas dépasser à gauche
                    if (debut.isBefore(min)) {
                        item.start = min;
                    }
                    //ne pas dépasser à droite
                    if (debut.isAfter(max)) {
                        item.start = max;
                    }
                    //Si l'item est une plage, ne pas dépasser à droite
                    if (item.end != null) {
                        var fin = vis.moment(item.end);
                        if (fin.isAfter(max)) {
                            item.end = max;
                        }
                    }
                    callback(item);
                },
                //Pour corriger le bug des tooltips qui restent affichés quand on supprime un item
                onRemove: function (item, callback) {
                    $('.vis-tooltip').css('visibility', 'hidden');
                    //Pour supprimer le popover
                    $('.id' + item.id).popover('destroy');
                    callback(item);
                }
            };

            timeline.setOptions(newOptions);
            ;
            timeline.addCustomTime(min, 'ct');

            player.player.on($.jPlayer.event.timeupdate, function () {
                timeline.setCustomTime(vis.moment(player.prettyCT(), 'mm:ss'), 'ct');
                timeline.setCustomTimeTitle(player.prettyCT(), 'ct');
                var item = items.get('background');
                item.end = vis.moment(timeline.getCustomTime('ct'));
                items.update(item);
            });

            timeline.on('timechange', function (event) {
                var date = vis.moment(event.time);
                if (date.isBefore(min)) {
                    date = min;
                    timeline.setCustomTime(min, 'ct');
                } else if (date.isAfter(max)) {
                    date = max;
                    timeline.setCustomTime(max, 'ct');
                }
                var time = date.unix() - min.unix();
                player.goTo(time);
            });

            // $('#timeline').prepend('<div id="chevron-gauche"></div>');
            // $('#timeline').append('<div id="chevron-droit"></div>');
            // $('#chevron-droit').addClass('clickable')
            //     .css('position', 'absolute')
            //     .css('top', '50%')
            //     .css('right', '-5px')
            //     .css('z-index', '5')
            //     .append('<i class="glyphicon glyphicon-chevron-right"></i>')
            //     .hide();
            //
            // $('#chevron-gauche').addClass('clickable')
            //     .css('position', 'absolute')
            //     .css('top', '50%')
            //     .css('left', '-5px')
            //     .css('z-index', '5')
            //     .append('<i class="glyphicon glyphicon-chevron-left"></i>')
            //     .hide();
            //
            // $('.vis-timeline.vis-bottom').append('<div id="recentrer"></div>');
            // $('#recentrer').addClass('btn btn-info')
            //     .attr('title', 'Recentrer la frise')
            //     .css('position', 'absolute')
            //     .css('padding', '0px 5px 0px 5px')
            //     .css('right', '5px')
            //     .css('bottom', '5px')
            //     .append('<i class="glyphicon glyphicon-screenshot"></i>')
            //     .hide();
            //
            // $('#chevron-gauche').on('click', function () {
            //     var range = timeline.getWindow();
            //     var start = vis.moment(range.start);
            //     var end = vis.moment(range.end);
            //     //Différence en millisecondes
            //     var diff = end.diff(start);
            //     //On déplace d'1/5 de l'ancienne largeur
            //     var step = diff / 5;
            //     timeline.setWindow(start.subtract(step, 'ms'), end.subtract(step, 'ms'));
            // });
            //
            // $('#chevron-droit').on('click', function () {
            //     var range = timeline.getWindow();
            //     var start = vis.moment(range.start);
            //     var end = vis.moment(range.end);
            //     //Différence en millisecondes
            //     var diff = end.diff(start);
            //     //On déplace d'1/5 de l'ancienne largeur
            //     var step = diff / 5;
            //     timeline.setWindow(start.add(step, 'ms'), end.add(step, 'ms'));
            // });
            //
            // $('#recentrer').on('click', function () {
            //     timeline.moveTo(timeline.getCustomTime('ct'));
            // });

            // timeline.on('rangechange', function (event) {
            //     var caDepasse = false;
            //     var start = vis.moment(event.start);
            //     var end = vis.moment(event.end);
            //     if (start.isAfter(min)) {
            //         $('#chevron-gauche').show();
            //         caDepasse = true;
            //     } else {
            //         $('#chevron-gauche').hide();
            //     }
            //     if (end.isBefore(max)) {
            //         $('#chevron-droit').show();
            //         caDepasse = true;
            //     } else {
            //         $('#chevron-droit').hide();
            //     }
            //     if (caDepasse) {
            //         $('#recentrer').show();
            //     } else {
            //         $('#recentrer').hide();
            //     }
            // });

            $('.reactionButton').prop('disabled', false);
            $('#formValidation button').prop('disabled', false);

        }
    };
    //
    // secToMoment = function (sec) {
    //     var time = min.unix() + parseInt(sec);
    //     return vis.moment.unix(time);
    // }


    /**
     * Snap a date to a rounded value.
     * The snap intervals are dependent on the current scale and step.
     * On fait en sorte qu'au plus petit ça arrondisse à la seconde.
     * @param {Date} date    the date to be snapped.
     * @param {string} scale Current scale, can be 'millisecond', 'second',
     *                       'minute', 'hour', 'weekday, 'day', 'week', 'month', 'year'.
     * @param {number} step  Current step (1, 2, 4, 5, ...
     * @return {Date} snappedDate
     */
    function snap (date, scale, step) {
        var clone = vis.moment(date);

        if (scale == 'hour') {
            switch (step) {
                case 4:
                    clone.minutes(Math.round(clone.minutes() / 60) * 60);
                    break;
                default:
                    clone.minutes(Math.round(clone.minutes() / 30) * 30);
                    break;
            }
            clone.seconds(0);
            clone.milliseconds(0);
        } else if (scale == 'minute') {

            switch (step) {
                case 15:
                case 10:
                    clone.minutes(Math.round(clone.minutes() / 5) * 5);
                    clone.seconds(0);
                    break;
                case 5:
                    clone.seconds(Math.round(clone.seconds() / 60) * 60);
                    break;
                default:
                    clone.seconds(Math.round(clone.seconds() / 30) * 30);
                    break;
            }
            clone.milliseconds(0);
        } else if (scale == 'second') {

            switch (step) {
                case 15:
                case 10:
                    clone.seconds(Math.round(clone.seconds() / 5) * 5);
                    clone.milliseconds(0);
                    break;
                default:
                    clone.milliseconds(Math.round(clone.milliseconds() / 1000) * 1000);
                    break;
            }
        } else if (scale == 'millisecond') {
            clone.milliseconds(Math.round(clone.milliseconds() / 1000) * 1000);
        }

        return clone;
    };

});