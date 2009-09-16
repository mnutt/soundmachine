var edCanvas = jQuery('#editor');

function edInsertContent(element, h) {
  songPath = jQuery(h).attr('href');
  songTitle = jQuery(h).text();
  var songs = getSongs();
  songs.unshift({title: songTitle, path: songPath, buy_link: '', album_cover: ''});
  writeSongsToForm(songs);
  tb_remove();
}

function writeSongsToForm(songs) {
  fields = jQuery("#wpbody-content form div.song-field");
  fields.each(function(i) {
    self = jQuery(this);
    if(songs[i]) {
      self.find(".song-title").val(songs[i].title);
      self.find(".song-path").val(songs[i].path);
      self.find(".song-buy_link").val(songs[i].buy_link);
      self.find(".song-album_cover").val(songs[i].album_cover);
      self.find(".song-show_download").get(0).checked = (songs[i].show_download);
      self.find(".album_cover_preview div").html("<img src='"+songs[i].album_cover+"'/>");
    } else {
      self.find(".song-title").val('');
      self.find(".song-path").val('');
      self.find(".song-buy_link").val('');
      self.find(".song-album_cover").val('');
      self.find(".song-show_download").val('');
      self.find(".album_cover_preview div").html('');
    }
  });
}

function condenseSongs() {
  var songs = getSongs();
  writeSongsToForm(songs);
}

function getSongs() {
  var songs = [];
  jQuery("#wpbody-content form div.song-field").each(function(row) {
    self = jQuery(this);
    var songTitle = self.find(".song-title").val();
    var songPath = self.find(".song-path").val();
    var buyLink = self.find(".song-buy_link").val();
    var albumCover = self.find(".song-album_cover").val();
    var showDownload = self.find(".song-show_download").get(0).checked;
    if(songTitle || songPath || buyLink || albumCover) {
      songs.push({title: songTitle, path: songPath, buy_link: buyLink, album_cover: albumCover, show_download: showDownload});
    }
  });
  return songs;
}

jQuery(document).ready(function() {
  jQuery("#wpbody-content form div.song-field button").click(function() {
    var field = jQuery(this).parent().parent().parent().parent().parent();
    var title = field.find(".song-title").val();
    var searchString = title.replace(/\s/ig, "%2B");

    var query = "select * from html";
    query += "    where url=\"http://www.amazon.com/s?ie=UTF8&tag=mozilla-20&index=blended&link_code=qs&field-keywords="+searchString+"&sourceid=Mozilla-search\"";
    query += "    and xpath='//div[@class=\"productImage\"]/a/img'";

    var params = {
      q: query,
      format: "json",
      callback: "?"
    };
    console.log(params);

    jQuery.getYQL(query, "json", function(result) {
      console.log(result);
      if(result && result.query && result.query.results && result.query.results.a) {
	var link = result.query.results.a[0];
        var albumName = link.span;
        var href = "http://www.store.limewire.com/store/app/" + link.href.replace(/[\/\.]+/, "").replace(/\;jsession.*/, "");
	field.find(".song-buy_link").val(href);
        var query = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D%22"+href+"%22%20and%0A%20%20%20%20%20%20xpath%3D'%2F%2Fdiv%5B%40class%3D%22album_big%22%5D%2F%2Fimg%5B%40class%3D%22hover_pic%22%5D'%0A%20%20%20%20&format=json&callback=?"
	jQuery.getJSON(query, function(result) {
          if(result && result.query && result.query.results && result.query.results.img) {
	    var img = result.query.results.img;
            var src = "http://store.limewire.com"+img.src;
	    field.find(".song-album_cover").val(src);
	    var imgHTML = "<img src='"+src+"' />";
	    var previewHTML = "<div id='song-preview-wrapper'><div id='song-preview-box'>"+imgHTML+"<p>"+albumName+"</p></div></div>";
	    // jQuery('body').append(previewHTML);
	    // tb_show("Preview", "#TB_inline?inlineId=song-preview-wrapper&modal=true");
            field.find(".album_cover_preview div").html(imgHTML);
	  }
	});
      }
    });

    return false;
  });

});