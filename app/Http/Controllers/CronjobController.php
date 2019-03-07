<?php

namespace App\Http\Controllers;

use App\Answer;
use Illuminate\Http\Request;

class CronjobController extends Controller
{
	/**
	 * cron job to update answers ranking
	 * should be run after every few seconds
	 */
    public function rank(){

		$model=new Answer();
		dd($model->test());
		return $model->triggerRanking();
	}
}
