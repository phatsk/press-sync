<?php

namespace Press_Sync\handlers;

class Static_Handler {
	public static function MakeHandler( $object_type ) {
		return subtr( $object_type, array(
			'_' => '-',
		) );
	}
}
