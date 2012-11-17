<script type="text/javascript">
$(document).ready(function() {
  $("#[[+id]]").fineUploader({
    request: {
      endpoint: "[[+endpointUrl]]",
      params: [[+requestParams]]
    },
    multiple: [[+multiple]],
    maxConnections: [[+maxConnections]],
    validation: {
      allowedExtensions: [[+allowedExtensionsArray]],
      sizeLimit: [[+sizeLimit]]
    },
    text: {
      uploadButton: "[[%ajaxfileupload.uploadButton]]",
      dragZone: "[[%ajaxfileupload.dragZone]]",
      cancelButton: "[[%ajaxfileupload.cancelButton]]",
      retryButton: "[[%ajaxfileupload.retryButton]]",
      failUpload: "[[%ajaxfileupload.failUpload]]",
      formatProgress: "[[%ajaxfileupload.formatProgress]]",
      waitingForResponse: "[[%ajaxfileupload.processing]]"
    },
    messages: {
      typeError: "[[%ajaxfileupload.typeError]]",
      sizeError: "[[%ajaxfileupload.sizeError]]",
      minSizeError: "[[%ajaxfileupload.minSizeError]]",
      emptyError: "[[%ajaxfileupload.emptyError]]",
      noFilesError: "[[%ajaxfileupload.noFilesError]]",
      onLeave: "[[%ajaxfileupload.onLeave]]",
      tooManyFilesError: "[[%ajaxfileupload.tooManyFilesError]]"
    }
  });
});
</script>