<?php
header("Content-type: text/plain");

if (
	isset($data["err"])
	&& $data["err"]
){

	$err = $data["err"];

	if ( is_array($err) ){
		if (!count($err)){
			$err = null;
		} else {
			$err = implode("; ", $err);
		}
	}

	if ( is_string($err) ){
		echo json_encode(
			Array(
				"err" => $err
			)
		);
	}

	return;

} elseif ( isset($data["res"]) ) {

	echo json_encode($data["res"]);

}