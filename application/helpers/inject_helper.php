<?php

function inject_trumbowyg(string $field = "footer")
{
  return ($field == "header") ?
    '
    <link href="'. base_url() .'assets/css/trumbowyg.min.css" rel="stylesheet">
    '
  :
    '
      <script src="'. base_url() .'assets/js/trumbowyg.min.js"></script>
      <script src="'. base_url() .'assets/js/plugins/cleanpaste/trumbowyg.cleanpaste.min.js"></script>
      <script src="'. base_url() .'assets/js/plugins/fontsize/trumbowyg.fontsize.min.js"></script>
      <script src="'. base_url() .'assets/js/plugins/template/trumbowyg.template.min.js"></script>
    '
  ;
}
