<script type="text/javascript">
function createUploader() {
  var thumbnailuploader = new qq.FileUploader({
    element: document.getElementById("[[+id]]"),
    action: "[[+actionUrl]]",
    multiple: [[+multiple]],
    params: [[+actionParams]],
    allowedExtensions: [[+allowedExtensionsArray]],
    sizeLimit: [[+sizeLimit]],
    maxConnections: [[+maxConnections]],
    uploadButtonText: "[[%ajaxfileupload.uploadButtonText]]",
    dragText: "[[%ajaxfileupload.dragText]]",
    cancelButtonText: "[[%ajaxfileupload.cancelButtonText]]",
    failUploadText: "[[%ajaxfileupload.failUploadText]]",
    messages: {
      typeError: "[[%ajaxfileupload.typeError]]",
      sizeError: "[[%ajaxfileupload.sizeError]]",
      minSizeError: "[[%ajaxfileupload.minSizeError]]",
      emptyError: "[[%ajaxfileupload.emptyError]]",
      noFilesError: "[[%ajaxfileupload.noFilesError]]",
      onLeave: "[[%ajaxfileupload.onLeave]]"
    },
    extraMessages: {
      formatProgress: "[[%ajaxfileupload.formatProgress]]",
      tooManyFilesError: "[[%ajaxfileupload.tooManyFilesError]]"
    }
  });
}

window.onload = createUploader;
</script>