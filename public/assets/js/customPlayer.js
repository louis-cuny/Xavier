var swfPath = 'assets/js/jPlayer/dist/s';

var playerHTML = "\
<div id='jp_container_1' class='jp-video lecteurVideo' role='application' aria-label='media player'>\
<div class='jp-type-single'>\
<div id='jquery_jplayer_1' class='jp-jplayer'></div>\
<div class='jp-gui'>\
<div class='loader' style='display:none'></div>\
<div class='jp-interface'>\
<input type='text' id='curseur' data-slider-id='curseurContainer' data-slider-min='0' data-slider-max='20' data-slider-step='1' data-slider-value='10' disabled>\
<div class='current-time' role='timer' aria-label='time'>&nbsp;</div>\
<div class='duration' role='timer' aria-label='duration'>&nbsp;</div>\
<div class='jp-controls-holder'>\
<div class='jp-volume-controls'>\
<button class='jp-mute' role='button' tabindex='0'>mute</button>\
<button class='jp-volume-max' role='button' tabindex='0'>max volume</button>\
<div class='jp-volume-bar'>\
<div class='jp-volume-bar-value'></div>\
</div>\
</div>\
<div class='jp-controls'>\
<button class='jp-play' role='button' tabindex='0' disabled style='cursor:not-allowed'>play</button>\
</div>\
</div>\
</div>\
</div>\
<div class='jp-no-solution'>\
<span>Mise à jour requise</span>\
Pour pouvoir lire cette vidéo, vous devez mettre à jour votre navigateur ou votre <a href='http://get.adobe.com/flashplayer/' target='_blank'>plugin Flash</a>.\
</div>\
</div>\
</div>\
";


/**
 * Création d'un lecteur de vidéo personnalisé, permettant de lire des vidéos seulement entre des bornes de temps spécifiées.
 * @param  {Object} div    la div jQuery où ajouter le lecteur
 * @param  {Function} ready  fonction appelée quand le lecteur est prêt
 * @param  {String} width  largeur du lecteur. Par défaut, 640
 * @param  {String} height hauteur du lecteur. Par défaut, 360
 * @return {Undefined}
 */
function CustomPlayer(div, ready, width, height){
  ready = ready || null;
  width = width || "640";
  height = height || "360";
  var that = this; //pour éviter les problèmes de portée dans les méthodes ci-dessous.
  //Ajout du code html gérant le lecteur
  div.append(playerHTML);
  this.player = $("#jquery_jplayer_1");
  this.debut = 0;
  this.fin = 0;
  this.curseur = new Curseur($("#curseur"));
  this.width = width;
  this.height = height;

  this.duree = 0;

  /**
   * Retourne la durée de l'extrait vidéo, en secondes.
   * @return {Number} la durée de l'extrait
   */
  this.duration = function(){
    return that.duree;
  };

  /**
   * Retourne la durée de l'extrait vidéo dans un format joli
   * @return {String} la durée de l'extrait dans un format mm:ss
   */
  this.prettyDuration = function(){
    return $.jPlayer.convertTime(that.duree);
  };

  /**
   * Retourne la durée de la vidéo parent
   * @return {Number} la durée de la vidéo
   */
  this.trueDuration = function(){
    return that.player.data("jPlayer").status.duration;
  };

  /**
   * Retourne la durée de la vidéo dans un format affichable (HH:MM:SS)
   * @return {String} La durée affichable
   */
  this.prettyTrueDuration = function(){
    return $.jPlayer.convertTime(that.trueDuration());
  };

  /**
   * Retourne le temps de lecture courant de la vidéo.
   * @return {Number} le temps de lecture
   */
  this.currentTime = function(){
    return that.player.data("jPlayer").status.currentTime;
  };

  /**
   * Retourle le temps de lecture dans un format affichable (HH:MM:SS)
   * @return {String} le temps de lecture joli
   */
  this.prettyCT = function(){
    return $.jPlayer.convertTime(that.currentTime() - that.debut);
  };

  /**
   * Démarre la lecture de la vidéo à un temps donné
   * @param  {Number} time le temps de début
   * @return {Undefind}
   */
  this.play = function(time){
    that.player.jPlayer("play",parseInt(time));
  };

  /**
   * Met en pause la vidéo à un temps donné
   * @param  {Number} time le temps où mettre en pause
   * @return {Undefined}
   */
  this.pause = function(time){
    that.player.jPlayer("pause",parseInt(time));
  };

  /**
   * Change le temps de lecture pour celui passé en paramètre. Il faut passer le temps de l'extrait. Si la vidéo était en pause, elle y reste. Si elle était en train d'être lue, elle continue d'être lue à partir du nouveau temps.
   * @param  {Number} time le tems où se déplacer
   * @return {Undefined}
   */
  this.goTo = function(time){
    time = +time + +that.debut; //on cast tout en nombre
    if(that.player.data('jPlayer').status.paused){
      that.pause(time);
    }else{
      that.play(time);
    }
  }

  /**
   * Indique si une vidéo source a été définie ou non
   * @return {Boolean} l'état de status.srcSet
   */
  this.srcSet = function(){
    return that.player.data("jPlayer").status.srcSet;
  }

  /**
   * Assigne une vidéo au lecteur
   * @param {String} name  le nom de la vidéo
   * @param {String} url   l'url où peut être trouvée la vidéo.
   * @param {Number} debut Le temps où commence l'extrait
   * @param {Number} fin   Le temps où s'arrête l'extrait
   */
  this.setVideo = function(name, url, debut, fin){
    debut = debut || 0;
    fin = fin || 0;
    that.debut = debut;
    that.fin = fin;
    that.player.jPlayer("setMedia", {
      title: name,
      m4v: url, //"http://podcast.univ-lorraine.fr/591300e2507f8.mp4",
      //rtmpv: url,
      poster: ""
    });
  }

  /**
   * Définit le début de l'extrait vidéo. Si aucun paramètre n'est passé, initialise à 0.
   * @param {Number} debut le temps de début de la vidéo
   */
  this.setDebut = function(debut){
    debut = debut || 0;
    this.debut = debut;
  }

  /**
   * Définit le temps de fin de la vidéo. Si aucun paramètre n'est passé, le temps de fin est la fin de la vidéo.
   * @param {Number} fin le temps de fin de la vidéo
   */
  this.setFin = function(fin){
    fin = fin || null;
    if(fin == null){
      fin = that.trueDuration();
    }
  }


  /**
   * Initialise le lecteur en mettant le curseur à la bonne position et en s'assurant que la lecture ne se fasse qu'entre les bornes désignées.
   * @param  {Function} callback fonction appelée à la fin de l'initialisation. Elle prend en paramètre true si tout s'est bien passé, false sinon.
   * @param  {Number}   time     Combien de temps ça fait qu'on essaie d'initialiser. Faut pas toucher, c'est pour les appels récursifs.
   * @return {Undefined}
   */
  this.initialize = function(callback, time){
    time = time || 0;
    $(".loader").show();
    $(".jp-play").css("cursor","not-allowed");
    $(".jp-play").prop("disabled", true);
    that.curseur.disable();
    //On boucle tant que la vidéo n'est pas chargée (et donc que sa durée est de 0)
    if(that.trueDuration() == 0){
      if(time >= 10000){
        if(callback != null){
          $(".loader").hide();
          callback(false);
        }
      }else{
        time += 500;
        setTimeout(function(){
          that.initialize(callback,time);
        }, 500);
      }
    }else{
      //Une fois que la vidéo est chargée
      if(that.fin == 0){
        that.fin = that.trueDuration();
      }
      that.duree = that.fin - that.debut;
      that.pause(that.debut);
      that.curseur.max(that.fin - that.debut);
      that.curseur.min(0);
      that.curseur.val(0);
      $(".jp-play").css("cursor","pointer");
      $(".jp-play").prop("disabled", false);
      that.curseur.enable();
      $(".loader").hide();
      $(".duration").text($.jPlayer.convertTime(that.duree));
      if(callback != null){
        callback(true);
      }
    }
  };

  //Création du jPlayer
  this.player.jPlayer({
    ready: ready,
    swfPath: swfPath,
    solution: "html",
    supplied: "m4v",
    //supplied: "rtmpv",
    preload: "auto",
    size: {
      width: that.width+"px",
      height: that.height+"px",
      cssClass: "jp-video-"+that.height+"p"
    },
    useStateClassSkin: true,
    autoBlur: true,
  });


  /*
  A chaque fois que le temps de la vidéo change (on avance dans la lecture), on met à jour la position du curseur et le temps affiché.
  */
  this.player.on($.jPlayer.event.timeupdate,function(){
    var currentTime = that.currentTime();
    if(currentTime < Math.floor(that.debut)){
      currentTime = Math.ceil(that.debut);
      that.play(currentTime);
    }else if(currentTime > Math.ceil(that.fin)){
      currentTime = Math.floor(that.fin);
      that.pause(currentTime);
    }
    $(".current-time").text(that.prettyCT());
    that.curseur.val(Math.floor(currentTime) - that.debut);
  });


  /*
  A chaque fois qu'on déplace manuellement le curseur, on navigue dans la vidéo.
  */
  that.curseur.curseur.on("slide slideStop",function(){
    that.goTo(that.curseur.val());
  });

}





/**
 * Représente un curseur stylisé. La zone à gauche du curseur est coloriée, celle à droite est laissée blanche.
 * @param {object} div La div jQuery de classe ".curseur" représentant le curseur.
 */
function Curseur(div){
  var that = this; //pour éviter les problèmes de portée dans les méthodes ci-dessous.
  div.slider({
    formatter: function(value){
      var min = Math.floor(value/60);
      min = ('0'+min).slice(-2);
      var sec = value % 60;
      sec = ('0'+sec).slice(-2);
      return min +':'+sec;
    }
  })
  this.curseur = div;
  this.curseur.slider('setValue',0);

  this.enable = function(){
    that.curseur.slider('enable');
  }

  this.disable = function(){
    that.curseur.slider('disable');
  }

  /**
   * Permet de lire ou de mettre à jour la valeur minimale du curseur. Si aucun paramètre n'est passé, on lit la valeur minimale. Si un paramètre est passé, il remplace l'ancienne valeur.
   * @param  {number} min le nouveau minimum
   * @return {number}     la minimum du curseur
   */
  this.min = function(min){
    if(min == null){
      return that.curseur.slider('getAttribute','min');
    }else{
      that.curseur.slider('setAttribute','min', min);
    }

  };

  /**
   * Permet de lire ou de mettre à jour la valeur du curseur. Si aucun paramètre n'est passé, on lit la valeur. Sinon, on la remplace par celle passée.
   * @param  {number} val la nouvelle valeur
   * @return {number}     la valeur du curseur
   */
  this.val = function(val){
    if(val == null){
      return that.curseur.slider('getValue');
    }else{
      that.curseur.slider('setValue',val);
    }

  };


  /**
   * Permet de lire ou de mettre à jour la valeur maximale du curseur. Si aucun paramètre n'est passé, on lit la valeur maximale. Si un paramètre est passé, il remplace l'ancienne valeur.
   * @param  {number} max le nouveau maximum
   * @return {number}     la maximum du curseur
   */
  this.max = function(max){
    if(max == null){
      return that.curseur.slider('getAttribute','max');
    }else{
      that.curseur.slider('setAttribute','max',max);
    }

  };

}
