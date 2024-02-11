<?php

/*
  simple wrappers to inject javascript & css
*/


function inject_trumbowyg(string $field = "footer")
{
  return ($field == "header") ?
    '
    <link href="'. base_url('node_modules/trumbowyg/dist/ui/trumbowyg.min.css').'" rel="stylesheet">
    '
  :
    '
      <script src="'. base_url('node_modules/trumbowyg/dist/trumbowyg.min.js') .'"></script>
      <script src="'. base_url('node_modules/trumbowyg/dist/plugins/template/trumbowyg.template.min.js') .'"></script>
    '
  ;
}
