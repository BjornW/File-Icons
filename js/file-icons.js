jQuery(document).ready(function() {

  jQuery('#upload_css_button').click(function() {
    formfield = jQuery('#upload_css').attr('name');
    tb_show('','media-upload.php?type=file&amp;TB_iframe=true');
    return false;
  });


  jQuery('#upload_sprite_button').click(function() {
    formfield = jQuery('#upload_sprite').attr('name');
    tb_show('','media-upload.php?type=image&amp;TB_iframe=true');
    return false;
  });

  window.send_to_editor = function(html) {
   alert(formfield);
   
   if(formfield == 'upload_css') {
    url = jQuery( html ).attr('href');
    jQuery('#upload_css').val(url);
   } else if(formfield == 'upload_sprite') {
    url = jQuery('img', html ).attr('src');
    jQuery('#upload_sprite').val(url);
   } 
   tb_remove();
  }

});
