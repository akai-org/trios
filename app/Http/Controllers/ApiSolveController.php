<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trio;
use App\WrongAnswer;
use App\UserTrioAttempt;
use Illuminate\Support\Facades\Auth;

class ApiSolveController extends Controller
{
    function getTrio(Request $request, Trio $trio) {
        if($trio->active == false) {
            abort(404);
        }
        $solved = false;
        $answer = null;
        if(Auth::check()) {
            $solved = $request->user()->checkIfSolvedTrio($trio);
            if($solved === true) $answer = $trio->answer;
        }
        return ['trio' => $trio, 'solved' => $solved, 'answer' => $answer];
    }

    function getTrioAnswer(Trio $trio) {
        $return = [
            "id" => $trio->id,
            "correctAnswer" => $trio->answer
        ];

        return $return;
    }

    function getRandomTrio(Request $request) {
        if(Auth::check()) {
            $solvedIds = $request->user()->solvedTriosIds()->toArray();
            $trio = Trio::where('active', true)->inRandomOrder()->whereNotIn('id', $solvedIds)->first();
        } else {
            $trio = Trio::where('active', true)->inRandomOrder()->first();
        }

        return ['trio' => $trio, 'solved' => false];
    }

    function postCheck(Request $request, Trio $trio) {
        if($trio->active == false) {
            abort(404);
        }
        //JSON response
        $response = [
            'answer' =>[
                'id' => $trio->id,
                'attemptedAnswer' => '',
                'isCorrect' => false
            ]
        ];

        $answer = $request->input('answer');

        //Sprawdzamy czy użytkownik kliknął I don't know

        if ($answer == "IDK@@") {
            $this->updateStats($request->user() ? $request->user()->id : 0, $trio->id, false);
        } else {
            // Czyścimy input
            // Usuwamy spacje i tabulatory z początku i końca
            $answer = trim($answer);
            // Zamieniamy na małe litery
            $answer = mb_strtolower($answer);

            //Zwracamy obrobioną odpowiedź od użytkownika
            $response['answer']['attemptedAnswer'] = $answer;
            $this->updateStats($request->user() ? $request->user()->id : 0, $trio->id, $answer == $trio->answer);

            // Sprawdzamy odpowiedź
            if($answer == $trio->answer) {
                // Poprawna
                $response['answer']['isCorrect'] = true;

            } else {
                // Błędna
                // Zapisujemy błędną odpowiedź
                $this->saveWrongAnswer($trio->id, $answer);
            }
        }

        // Dodaj statystyki do json jeśli użytkownik jest zalogowany
        if(Auth::check()) {
            $response['stats'] = [
                'solved' => $request->user()->solvedTriosCount(),
                'attempted' => $request->user()->attemptedTriosCount()
            ];
        }

        //Zwracamy JSON

        return $response;

    }

    private function updateStats($user_id, $trio_id, $correct) {

        $trioAttempts = UserTrioAttempt::where('trio_id', $trio_id)
            ->where('user_id', $user_id)->first();

        // Sprawdzamy czy użytk. próbował rozwiącać to trio
        if($trioAttempts === null) {
            // Jeśli nie to tworzymy statystykę dla trio
            $trioAttempts = new UserTrioAttempt;
            $trioAttempts->trio_id = $trio_id;
            $trioAttempts->user_id = $user_id;
            $trioAttempts->attempts = 1;
            $trioAttempts->solved = false;
        } else {
            $trioAttempts->attempts++;
        }

        if($correct) {
            $trioAttempts->solved = true;
        }

        $trioAttempts->save();

    }

    private function saveWrongAnswer($trio_id, $answer) {
        $wrongAnswer = new WrongAnswer;
        $wrongAnswer->trio_id = $trio_id;
        $wrongAnswer->answer = $answer;
        $wrongAnswer->save();
    }

    private function saveIDontKnowClick($trio_id) {
        $this->saveWrongAnswer($trio_id, 'IDK');
    }
}
