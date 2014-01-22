function chartbuilderIframeLoaded(){
  jQuery(function($){

    $('#__wp-uploader-id-0 .media-iframe iframe').contents().find('#downloadImageLink').on('click', function(e){
      e.preventDefault();

      var imageBase64Data = $(this).attr('href').match(/^(data:image\/png;base64,)(.+$)/),
      imageTitle = $('#__wp-uploader-id-0 .media-iframe iframe').contents().find('#chart_title').val(),
      postID = $('#post_ID').val();

      $.post(
        ajaxurl,
        {
          'action': 'chartbuilder_upload',
          'imageBase64Data': imageBase64Data[2],
          'imageTitle': imageTitle,
          'postID': postID
        },
        function(response){
          if(response.success){
            console.log('success');
//            wp.media.editor.get(wpActiveEditor).close();
//            wp.media.editor.get(wpActiveEditor).open();
          }else{
            console.log(response.data);
          }
        }
      );
    });
  });
}
