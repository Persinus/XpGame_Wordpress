window.onload = spice_social_share_links;

function spice_social_share_links() {
  var pageUrl = encodeURIComponent(document.URL);
  var pageTitle = encodeURIComponent(document.title);
  var pageMedia = jQuery('#spice_social_share_pin_link').val();
  var tweetUser = jQuery('#spice_social_share_tweetuser').val();
  document.addEventListener('click', function (event) {  
    let url = null;
    
    if (event.target.classList.contains('spice_social_share_link_facebook')) {
      url = "https://www.facebook.com/sharer.php?u=" + pageUrl;
      spice_social_share_window(url, 570, 570);
    }

    if (event.target.classList.contains('spice_social_share_link_twitter')) {
      url = "https://twitter.com/intent/tweet?url=" + pageUrl + "&text=" + pageTitle + "&via=" + tweetUser;
      spice_social_share_window(url, 570, 300);
    }

    if (event.target.classList.contains('spice_social_share_link_linkedin')) {
      url = "https://www.linkedin.com/shareArticle?mini=true&url=" + pageUrl;
      spice_social_share_window(url, 570, 570);
    }

    if (event.target.classList.contains('spice_social_share_link_pinterest')) {
     if( pageMedia !='')
        {
         url = "https://www.pinterest.com/pin/create/button/?url=" + pageUrl+ "&media=" + pageMedia;
        }
      else
        {
         url = "https://www.pinterest.com/pin/create/button/?url=" + pageUrl
        }
      spice_social_share_window(url, 570, 450);
    }

    if (event.target.classList.contains('spice_social_share_link_mail')) {
      url = "mailto:?subject=%22" + pageTitle + "%22&body=Read%20the%20article%20%22" + pageTitle + "%22%20on%20" + pageUrl;
      spice_social_share_window(url, 570, 450);
    }
	
  if (event.target.classList.contains('spice_social_share_link_whatsapp')) {
	  url = " https://api.whatsapp.com/send?text=" + pageUrl;
      spice_social_share_window(url, 570, 570);
    }

  }, false);
}

function spice_social_share_window(url, width, height) {
  var left = (screen.width - width) / 2;
  var top = (screen.height - height) / 2;
  var params = "menubar=no,toolbar=no,status=no,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left;
  window.open(url,"",params);
}
