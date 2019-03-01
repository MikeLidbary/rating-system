<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {
	/**
	 * Get answers associated with a question
	 */
	public function answers() {
		return $this->hasMany( 'App\Answer' );
	}

}
