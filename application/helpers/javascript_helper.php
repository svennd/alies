<?php 

/*
    cache busting technique if we create a new file
*/
function javascript(string $asset) : string
{
    if (file_exists(FCPATH . $asset)) {
        return base_url($asset . '?v=' . filemtime(FCPATH . $asset));
    }
    return "";
}
