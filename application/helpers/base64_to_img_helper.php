<?php

/*
  will store a base64 image in a fixed location
*/
function base64_to_image(string $data, string $location, string $name) {
  // input:
  // $data = 'data:image/png;base64,AAAFBfj42Pj4';

  // load instance
  $ci =& get_instance();
  $ci->load->helper('file');

  if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
      $data = substr($data, strpos($data, ',') + 1);
      $type = strtolower($type[1]); // jpg, png, gif

      if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
          throw new \Exception('invalid image type');
      }
      $data = str_replace( ' ', '+', $data );
      $data = base64_decode($data);

      if ($data === false) {
        throw new \Exception('base64_decode failed');
      }
  } else {
      throw new \Exception('did not match data URI with image data');
  }

  if(write_file($location . $name . "." . $type, $data))
  {
    return array($name. "." . $type, "image/".$type, strlen($data));
  }
  else
  {
    throw new \Exception('Could not write file.');
  }
}
