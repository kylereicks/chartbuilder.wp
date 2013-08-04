function blobFromBase64Image(imageBase64Data){
  var byteCharacters = atob(imageBase64Data);

  function charCodeFromCharacter(c){
    return c.charCodeAt(0);
  }

  var byteNumbers = Array.prototype.map.call(byteCharacters, charCodeFromCharacter);

  var unit8Data = new Uint8Array(byteNumbers);

  var imageBlob = new Blob([unit8Data], {type: 'image/png'});

  return imageBlob;
}

function chartbuilderIframeLoaded(){
  jQuery(function($){
    $('#__wp-uploader-id-0 .media-iframe iframe').contents().find('#downloadImageLink').click(function(e){
      var imageBase64Data = $(this).attr('href').match(/^(data:image\/png;base64,)(.+$)/);

      var imageBlob = blobFromBase64Image(imageBase64Data[2]);

      var imageAttributes = {
        file: imageBlob,
        uploading: true,
        date: new Date,
        filename: $(this).attr('download'),
        menuOrder: 0,
        uploadedTo: wp.media.model.settings.post.id || 0,
        type: 'image',
        subtype: 'png'
      }

      var attachment = wp.media.model.Attachment.create(imageAttributes);

      wp.Uploader.queue.add(attachment);

      e.preventDefault();
    });
  });
}
