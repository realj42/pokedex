<?php

namespace App\Http\Controllers;

use App\PokeDictionary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class pokemonController extends Controller
{

    /**
     * @param $char - single character for alphabetic listing of pokemon names
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index($char = '') {
        Log::info('Index: Entry : ', ["routingChar" => $char, "searchString" => \request('searchString')]);
        $pokeApi = new PokeDictionary();
        $dictionary = $pokeApi->dictionary();
        if (empty($char)) {
            $this->validate(request(), [
                'searchString' => 'nullable | alpha | max:32',
            ]);
            $searchString = strtolower(\request('searchString'));
            if (empty($searchString)) {
                // no search supplied - show 'a' names by default
                $searchString = 'a';
            }
        } else {
            // routing based search - only single characters from dictionary allowed.
            if ((strlen($char) > 1) || (!in_array($char, $dictionary))) {
                // route based search string not valid
                $char = 'a';
            }
            $searchString = $char;
        }
        $result = $pokeApi->matchNames($searchString);
        Log::debug("Index: Search result ", [ 'Result' => substr(print_r($result, true), 0, 250)]);
        if (empty($result) || (!is_array($result))) {
            $result = "Your search returned no results - please try again later or try a different search";
        } else if (count($result) == 1) {
            // Search found one result - redirect to single
            $resultObj = array_pop($result);
            return redirect('/pokemon/' . $resultObj->ID .'/');
        } else {
            // multiple names found - display names and link to single view for each
        }

        return view('pokemon.directory', compact('result', 'dictionary'));
    }

    /**
     * @param $index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function single($index) {
        Log::info('Single: Entry : ', ["Index" => $index, "searchString" => \request('searchString')]);
        if (!empty(\request('searchString'))) {
            // user entered a search value - redirect to index
            return redirect('/')->withInput();
        }
        $pokeApi = new PokeDictionary();
        $dictionary = $pokeApi->dictionary();
        $index = intval($index);        // ensure index is an integer
        if ($index > 0) {
            $result = $pokeApi->getPokemonDetails($index);
            if (! $result instanceof \stdClass) {
                // no result - too many searches?
                $pokedetails = "Your search returned no result - please try later or try a different search";
            } else {
                // Got a Pokemon - extract required details
                $abilities = [];
                foreach ($result->abilities as $ability) {
                    $abilities[] = $ability->ability->name;
                }
                $pokedetails = [
                    'name' => $result->name,
                    'ID' => $index,
                    'height' => $result->height,
                    'weight' => $result->weight,
                    'species' => $result->species->name,
                    'abilities' => $abilities,
                    'frontView' => $result->sprites->front_default ?? '',
                    'backView' => $result->sprites->back_default ?? ''
                ];
            }
            return view('pokemon.single', compact('pokedetails', 'dictionary'));
        }
        // user f'ing about
        return redirect('/');
    }
}
