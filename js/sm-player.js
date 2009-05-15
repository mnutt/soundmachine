function LWMBPlayer() {
  var sm = soundManager;
  var songs = [];
  var self = this;
  var selectedId = 1;
  var position = 0;
  var resumeTimer = null;

  this.init = function() {
    jQuery("ul.playlist li a").each(function(id) {
      sm.createSound({
	id:  id + 1,
	whileplaying: self.whilePlaying,
	candownload: jQuery(this).hasClass('can-download'),
	title: jQuery(this).text(),
	cover: jQuery(this).attr('data-cover'),
	buyurl: jQuery(this).attr('data-link'),
	url: jQuery(this).attr('href')
      });
      jQuery("<img>").attr("src", jQuery(this).attr('data-cover'));
      jQuery(this).data('track_position', id + 1);
    });
    this.updateTitle();
    this.updateCover();
  };

  this.selectedId = function() {
    return selectedId;
  };

  this.player = jQuery(".lwmb-player");

  this.updateTitle = function() {
    var sound = sm.sounds[selectedId];
    var name = sound.options.title.split(' - ').reverse();
    name[0] = "<div class='title'>"+name[0]+"</div>";
    name = name.join(' ');
    if(sound.options.candownload) {
      var downloadHtml = "<a class='lwmb-player-download' title='Download' href='"+sound.options.url+"'>Download</a>";
      jQuery(".lwmb-player-download-wrapper").html(downloadHtml);
    } else {
      jQuery(".lwmb-player-download-wrapper").html('');
    }
    jQuery(".lwmb-player-current").html(name);
  };

  this.updateCover = function() {
    var sound = sm.sounds[selectedId];
    if(sound.options.cover) {
      jQuery(".lwmb-player-cover").html("<a href='"+sound.options.buyurl+"'><img src='"+sound.options.cover+"'/></a>");
    } else {
      jQuery(".lwmb-player-cover").html("<a href='"+sound.options.buyurl+"'>&nbsp;</a>");
    }
  };

  this.play = function(playId) {
    if(playId) {
      selectedId = playId;
    }
    if(this.state() == "paused" || this.state() == "playing") {
      if(this.state() == "paused") {
	jQuery(".widget_flash_mp3player .lwmb-player-play").addClass("playing");
	jQuery(".widget_flash_mp3player .lwmb-player-play").removeClass("paused");
      } else {
	jQuery(".widget_flash_mp3player .lwmb-player-play").addClass("paused");
      }
      sm.togglePause(selectedId);
    } else {
      jQuery(".widget_flash_mp3player .lwmb-player-play").addClass("playing");
      this.selectTrack(selectedId);
      this.resetProgress();
      this.updateTitle();
      this.updateCover();
      sm.play(selectedId);
    }
  };

  this.stop = function() {
    jQuery(".lwmb-player-play").removeClass("playing");
    jQuery(".lwmb-player-play").removeClass("paused");
    sm.stopAll();
    this.resetProgress();
  };

  this.selectTrack = function(pos) {
    jQuery(".lwmb-playlist ul.playlist li").removeClass('selected');
    jQuery(".lwmb-playlist ul.playlist li:nth-child("+pos+")").addClass('selected');
  };

  this.state = function() {
    if(sm.sounds[selectedId].paused === true) {
      return "paused";
    } else if(sm.sounds[selectedId].playState == 1) {
      return "playing";
    } else {
      return "stopped";
    }
  };

  this.whilePlaying = function() {
    if(this.durationEstimate > 0) {
      var percentComplete = Math.round(this.position / this.durationEstimate * 100);
    } else {
      var percentComplete = 0;
    }
    lwmbPlayer.position = this.position;
    jQuery(".widget_flash_mp3player .lwmb-player-progress-inner").css({width: percentComplete+"%"});
  };

  this.resetProgress = function() {
    jQuery(".widget_flash_mp3player .lwmb-player-progress-inner").css({width: 0});
  };

  this.next = function() {
    if(sm.sounds[selectedId + 1]) {
      sm.stopAll();
      selectedId = selectedId + 1;
      this.updateTitle();
      this.updateCover();
      this.selectTrack(selectedId);
      sm.play(selectedId);
    }
  };

  this.prev = function() {
    sm.stopAll();
    if(sm.sounds[selectedId - 1]) {
      selectedId = selectedId - 1;
    }
    this.updateTitle();
    this.updateCover();
    this.selectTrack(selectedId);
    sm.play(selectedId);
  };

  this.setPosition = function(pos) {
    sm.setPosition(selectedId, pos);
  };

  this.resume = function(id, pos, state) {
    if(state == "playing") {
      var setPositionWhenLoaded = function() {
        if(soundManager.sounds[id].position == null && soundManager.sounds[id].readyState <= 1) {
	  lwmbPlayer.setPosition(pos);
        } else {
	  lwmbPlayer.play(id);
	  lwmbPlayer.setPosition(pos);
	  clearInterval(lwmbPlayer.resumeTimer);
        }
      };
      lwmbPlayer.resumeTimer = setInterval(setPositionWhenLoaded, 500);
      soundManager.load(id);
    }
  };

  this.activateInline = function() {
    jQuery(".lwmb-player-play.inline").each(
      function() {
	playButton = jQuery(this);

	// Set up soundManager
	var track_position = sm.soundIDs.length + 1;
	sm.createSound({ id:  track_position,
			 url: playButton.data('url') });
	playButton.data('track_position', track_position);
      }
    );
  };

}

var lwmbPlayer = new LWMBPlayer();

jQuery(document).ready(function() {
  jQuery(".widget_flash_mp3player .lwmb-player-play").click(function() {
    lwmbPlayer.play();
  });

  jQuery(".widget_flash_mp3player .lwmb-player-next").click(function() {
    lwmbPlayer.next();
  });

  jQuery(".widget_flash_mp3player .lwmb-player-prev").click(function() {
    lwmbPlayer.prev();
  });

  jQuery(".widget_flash_mp3player .lwmb-player-stop").click(function() {
    lwmbPlayer.stop();
  });

  jQuery(".lwmb-playlist ul.playlist li").click(function() {
    var track_position = jQuery(this).find('a').data('track_position');
    lwmbPlayer.stop();
    lwmbPlayer.play(track_position);
    return false;
  });
});

soundManager.onload = function() {
  try {
    lwmbPlayer.init();
    if(eval('typeof(popout_player_songId)') != "undefined") {
      lwmbPlayer.resume(popout_player_songId,
                        popout_player_position,
                        popout_player_state);
    } else {
      lwmbPlayer.activateInline();
    }
  } catch(error) {
    // console.log(error);
  }
};