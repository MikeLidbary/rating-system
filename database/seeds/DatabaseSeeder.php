<?php

use App\Answer;
use App\Question;
use App\User;
use App\Vote;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run() {
		// use the faker library to generate fake data
		$faker = Faker::create();

		// create 20 users
		for ( $i = 0; $i < 20; $i ++ ) {
			User::create( [
				'name'              => $faker->name,
				'email'             => $faker->unique()->safeEmail,
				'email_verified_at' => now(),
				'password'          => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
				'remember_token'    => Str::random( 10 ),
			] );
		}
		$users = User::all()->pluck( 'id' )->toArray();

		// create 5 questions
		for ( $i = 0; $i < 5; $i ++ ) {
			$question_value = $faker->sentence( 6 );
			$question       = Question::create( [
				'question' => $question_value,
				'slug'     => str_slug( $question_value ),
			] );

		}
		$questions = Question::all()->pluck( 'id' )->toArray();
		// each question to have 8 answers
		foreach ($questions as $question){
			for ( $i = 0; $i < 8; $i ++ ) {
				$answer = Answer::create( [
					'answer'      => $faker->paragraph( 6 ),
					'question_id' => $faker->randomElement( $questions ),
					'user_id'     => $question,
				] );
			}
		}
		// a question to have either 6 votes or 12 votes or 18 votes.
		// A vote can either be a upvote or a downvote i.e 0 or 1
		$votes       = $faker->randomElement( [ 6, 12, 18 ] );
		$users_clone = $users;
		$answers = Answer::all()->pluck( 'id' )->toArray();
		foreach ($answers as $answer){
			for ( $i = 0; $i < $votes; $i ++ ) {
				$del_val = $faker->randomElement( $users );
				Vote::create( [
					'vote'      => $faker->randomElement( [ 0, 1 ] ),
					'answer_id' => $answer,
					'user_id'   => $del_val,
				] );
				if ( ( $key = array_search( $del_val, $users_clone ) ) !== false ) {
					unset( $users_clone[ $key ] );
				}
			}
		}
		for ( $i = 0; $i < $votes; $i ++ ) {
			$del_val = $faker->randomElement( $users );
			Vote::create( [
				'vote'      => $faker->randomElement( [ 0, 1 ] ),
				'answer_id' => $faker->randomElement( $answers ),
				'user_id'   => $del_val,
			] );
			if ( ( $key = array_search( $del_val, $users_clone ) ) !== false ) {
				unset( $users_clone[ $key ] );
			}
		}


	}
}
