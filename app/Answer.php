<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model {
	/**
	 * Get votes associated with an answer
	 */
	public function votes() {
		return $this->hasMany( 'App\Vote' );
	}

	public function test() {
		$question_ids = Answer::select( 'question_id' )->where( 'must_update', 1 )->where( 'is_deleted', 0 )
		                      ->distinct( 'question_id' )->get();
		$outer_array=[];
		foreach ( $question_ids as $question_id ) {
			$answers = $this->getAnswers( $question_id->question_id);
			$array=[];
			foreach ($answers as $answer){
				$array[$answer->id]=[
					'upvote'=>$this->getUpvotes($answer->id),
					'downvote'=>$this->getDownvotes($answer->id),
					'total_votes'=>$this->getVotes($answer->id),
					'rating'=>$this->getRating($answer->id),
				];
			}
			$outer_array[$question_id->question_id]=$array;
		}

		return $outer_array;
	}

	/**
	 * @return string
	 */
	public function triggerRanking() {
		$question_ids = Answer::select( 'question_id' )->where( 'must_update', 1 )->where( 'is_deleted', 0 )
		                      ->distinct( 'question_id' )->get();
		foreach ( $question_ids as $question_id ) {
				$answers = $this->getAnswers( $question_id->question_id);
				if ( $answers ) {
					$this->getBayesianRating( $answers );
				}
		}

		return "success";
	}

	/**
	 * Get Bayesian rating
	 *
	 * @param $answers object
	 *
	 * @return array
	 */
	public function getBayesianRating( $answers ) {
		foreach ( $answers as $answer ) {
			$votes                   = $this->getVotes( $answer->id );
			$average_votes           = $this->getAverageVotes( $answers );
			$numerator               = ( $average_votes * $this->getAverageRating( $answers ) ) +
			                           ( $votes * $this->getRating( $answer->id ) );
			$denominator             = $average_votes + $votes;
			$bayesian_rating         = ( $denominator > 0 ) ? $numerator / $denominator : 0;
			$answer->bayesian_rating = $bayesian_rating;
			$answer->must_update     = 0;
			$answer->save();
		}
		if ( $answers ) {
			$count         = 1;
			$answers_1 = $this->getAnswers( $answers[0]->question_id, 'bayesian_rating', 'desc' );
			foreach ( $answers_1 as $value ) {
				$value->rank = $count;
				$value->save();
				$count ++;
			}
		}
	}

	/**
	 * Sum of up votes and down votes
	 *
	 * @param $id
	 *
	 * @return float|int
	 */
	public function getVotes( $id ) {
		$total = $this->getUpvotes( $id ) + $this->getDownvotes( $id );

		return $total;
	}

	/**
	 * Total up votes
	 *
	 * @param $id integer question_id
	 *
	 * @return mixed
	 */
	public function getUpvotes( $id = null ) {
		$data    = 0;
		$id      = isset( $this->id ) ? $this->id : $id;
		$answers = Answer::find( $id );
		if ( $answers && count( $answers->votes ) ) {
			$data = $answers->votes()->where( 'is_deleted', 0 )->where( 'vote', 1 )->count();
		}

		return $data;
	}

	/**
	 * Total down votes
	 *
	 * @param $id integer question_id
	 *
	 * @return mixed
	 */
	public function getDownvotes( $id = null ) {
		$data    = 0;
		$id      = isset( $this->id ) ? $this->id : $id;
		$answers = Answer::find( $id );
		if ( $answers && count( $answers->votes ) ) {
			$data = $answers->votes()->where( 'is_deleted', 0 )->where( 'vote', 0 )->count();
		}

		return $data;
	}

	/**
	 * Average total votes
	 *
	 * @param $answers object
	 *
	 * @return float|int
	 */
	public function getAverageVotes( $answers ) {
		$average = 0;
		$sum     = 0;
		$count   = 0;
		foreach ( $answers as $answer ) {
			$sum += $this->getVotes( $answer->id );
			$count ++;
		}
		if ( $sum > 0 ) {
			$average = $sum / $count;
		}

		return $average;
	}

	/**
	 * Average rating
	 *
	 * @param $answers object
	 *
	 * @return float|int
	 */
	public function getAverageRating( $answers ) {
		$average = 0;
		$sum     = 0;
		$count   = 0;
		foreach ( $answers as $answer ) {
			$sum += $this->getRating( $answer->id );
			$count ++;
		}
		if ( $sum > 0 ) {
			$average = $sum / $count;
		}

		return $average;
	}

	/**
	 * Upvotes divided by sum of upvotes and downvotes
	 *
	 * @param $id
	 *
	 * @return float|int
	 */
	public function getRating( $id ) {
		$value = 0;
		$total = $this->getVotes( $id );
		if ( $total > 0 ) {
			$value = $this->getUpvotes( $id ) / $total;
		}

		return $value;
	}

	/**
	 * Used in calculating Bayesian rating
	 *
	 * @param $question_id
	 * @param $order_by
	 * @param $order
	 *
	 * @return mixed
	 */
	public function getAnswers( $question_id, $order_by = "id", $order = "asc" ) {

		$answers = Question::find( $question_id )->answers()->where( 'is_deleted', 0 )
		                   ->orderBy( $order_by, $order )->get();

		return $answers;
	}

}
