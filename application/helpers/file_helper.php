<?php 

/*
*	function: move_file
*	move a file from one location to another
*/
function move_file(string $path, string $to): bool {
	if(copy($path, $to)){
		unlink($path);
		return true;
	} else {
		return false;
	}
}