$(document).ready(function () {
    /**
     * Indique si l'on a envoyé le formulaire ou non
     * (pour savoir s'il faut afficher un message de confirmation en quittant la page.)
     * @type {Boolean}
     */
    var submitted = false;
    /**
     * Dictionnaire contenant en clé les IDs des réactions dont le bouton est enfoncé, et en valeur l'id de l'item sur la frise correspondant.
     * @type {Object}
     */
    var actives = {};

    /**
     * Liste des annotations libres déjà proposées
     * @type {Array}
     */
    var propositions = [];


    $('.reactionButton').on('click', function () {
        console.log('BLEU');
        // return false;
        $(this).toggleClass('active');
        var id = $(this).attr('id');
        var color = id.split(';')[1];
        var nomReaction = id.split(';')[2];
        var idReaction = id.split(';')[3];
        //Si on vient d'activer le bouton
        if ($(this).hasClass('active')) {
            var ct = player.prettyCT();
            var uniqid = new Date().getTime();
            //On ajoute la réaction à la liste de celles qui doivent grandir
            actives[idReaction] = uniqid;
            var time = vis.moment(ct, 'mm:ss');
            //On crée la réaction dans la timeline
            if ($(this).hasClass('reactionLibreButton')) {
                items.add({
                    id: uniqid,
                    start: time,
                    end: time,
                    content: "Cliquez pour annoter",
                    className: 'reacLibre reac' + idReaction + ' id' + uniqid,
                    title: 'Réaction libre <small>(' + ct + ' - ' + ct + ') </small>',
                    idReac: idReaction
                });
                $('.id' + uniqid).popover({
                    animation: false,
                    placement: 'top',
                    title: 'Réaction libre',
                    trigger: 'manual',
                    html: true,
                    content: '<input id="input' + uniqid + '" type="text" list="propositions" class="form-control">',
                    container: 'body'
                });
                $('.id' + uniqid).on('shown.bs.popover', function () {
                    var val = items.get(uniqid).val;
                    if (val != null) {
                        $('#input' + uniqid).val(val);
                    }
                    $('#input' + uniqid).focus();
                });
                $('.id' + uniqid).on('hide.bs.popover', function () {
                    $('#input' + uniqid).blur();
                });
                $(document).on('keypress', '#input' + uniqid, function (e) {
                    if (e.keyCode == 27 || e.keyCode == 13) {
                        $('.id' + uniqid).popover('hide');
                    }
                });
                $(document).on('change', '#input' + uniqid, function () {
                    var val = $('#input' + uniqid).val();
                    item = items.get(uniqid);
                    var oldVal = item.val;
                    item.content = val;
                    item.title = 'Réaction libre';
                    item.val = val;
                    if (val) {
                        item.title += ' "' + val + '"';
                        if (!propositions.includes(val)) {
                            propositions.push(val);
                            var option = $(document.createElement('option')).attr('value', val).text(val);
                            $('#propositions').append(option);
                        }
                    } else {
                        item.content = "Cliquez pour annoter";
                    }
                    item.title += ' <small>(' + vis.moment(item.start).format('mm:ss') + ' - ' + vis.moment(item.end).format('mm:ss') + ')</small>';
                    items.update(item);
                    if (oldVal) {
                        console.log(oldVal);
                        console.log(propositions);
                        var index = propositions.indexOf(oldVal);
                        console.log(index);
                        if (index != -1) {
                            propositions.splice(index, 1);
                            $('#propositions').children('option').eq(index).remove();
                            console.log(propositions);
                        }
                    }
                });


            } else {
                items.add({
                    id: uniqid,
                    start: time,
                    end: time,
                    className: "reac" + idReaction,
                    title: nomReaction + ' <small>(' + ct + ' - ' + ct + ')</small>',
                    idReac: idReaction
                });
            }
            //Si on désactive le bouton
        } else {
            delete actives[idReaction];
        }
        //Parce que ça me stresse que le bouton garde le focus
        $(this).blur();
    });


    /*
    Quand on sélectionne un item de la timeline
    */
    timeline.on('select', function (properties) {
        for (var i = 0; i < properties.items.length; i++) {
            //On récupère l'item qui a été sélectionné
            var item = items.get(properties.items[i]);
            //Si il correspond à une réaction libre (sinon on s'en fiche)
            if (item.className.substring(0, 9) === 'reacLibre') {
                var $div = $('.id' + item.id);
                $div.popover('show');
            }
        }
    });

    /*
    Quand on clique sur la timeline, on cache les popovers
    */
    timeline.on('click', function (e) {
        $("[aria-describedby^='popover']").popover('hide');
    });

    $(document).on('keypress', function (e) {
        if (e.keyCode == 46) {
            var selected = $('.vis-selected').not("[aria-describedby^='popover']");
            if (selected.length > 0) {
                var div = selected[0];
                var classes = $(div).attr('class');
                var split = classes.split(' ');
                var id = '';
                for (var i = 0; i < split.length; i++) {
                    if (split[i].substring(0, 2) == "id") {
                        id = split[i].substring(2);
                    }
                }
                items.remove(id);
            }
        }
    });


    /*
    A chaque fois que le temps du player change, on met à jour le temps de fin de toutes les réactions dont les boutons sont enclenchées.
    */
    player.player.on($.jPlayer.event.timeupdate, function () {
        var ct = vis.moment(player.prettyCT(), 'mm:ss');
        for (var key in actives) {
            //update end time
            item = items.get(actives[key]);
            if (vis.moment(item.start).isBefore(ct)) {
                item.end = ct;
            } else {
                item.end = item.start;
            }
            //update title
            var index = item.title.indexOf('<small>');
            var title = item.title.slice(0, index);
            title += "<small>(" + vis.moment(item.start).format('mm:ss') + ' - ' + vis.moment(item.end).format('mm:ss') + ")</small>";
            item.title = title;

            items.update(item);
        }
    });


    /*
    Quand on valide, on récupère toutes les annotations et on les exporte sous forme de chaîne.
    */
    $('#boutonValider').on('click', function (e) {
        console.log("bouton valider");
        return false;
        player.pause();
        submitted = true;
        $('.form-control').blur();
        //On ajoute chaque item (sauf le background) au formulaire avant de le submit
        items.forEach(function (item) {
            //On récupère le temps en secondes.
            var debut = item.start.unix() - vis.moment("00:00", "mm:ss").unix();
            var val = $("#reactionsTextArea").val() + item.idReac;
            val += ' ' + debut;
            if (item.end != null) {
                var fin = item.end.unix() - vis.moment('00:00', 'mm:ss').unix();
                val += ' ' + fin;
            } else {
                val += ' ' + debut;
            }
            if (item.className.substring(0, 9) === 'reacLibre') {
                var reac = item.val;
                if (reac) {
                    reac = reac.replace(/\s+|\;/g, '_');
                    val += ' ' + reac;
                }
            }
            val += ';';
            $('#reactionsTextArea').val(val);
        }, {
            filter: function (item) {
                return item.id != 'background';
            }
        });
        $('#formValidation').submit();
        /*
        Les données sont envoyées dans le textArea sous la forme :
        idReac debut fin [reac_libre];[idReac debut fin [reac_libre];]...
        Si il n'y a pas de fin, elle vaut la même valeur que le début.
        */
    });

    //
    // function leaving (e) {
    //     if (!submitted && items.length > 1) {
    //         if (!e) {
    //             e = window.event;
    //         }
    //         e.cancelBubble = true;
    //         e.returnValue = 'Voulez-vous vraiment quitter ? Vos réponses ne seront pas enregistrées.';
    //         if (e.stopPropagation) {
    //             e.stopPropagation();
    //             e.preventDefault();
    //         }
    //     }
    // }
    //
    // function goodbye () {
    //     if (!submitted && items.length > 1) {
    //         $.ajax({
    //             url: PATH + 'leaveExperience',
    //             type: 'GET',
    //         });
    //     }
    // }
    //
    // window.onbeforeunload = leaving;
    // window.onunload = goodbye;

});// End of document.ready



