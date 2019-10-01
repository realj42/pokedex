<?php


namespace App;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PokePHP\PokeApi;

class PokeDictionary
{

    private $resourceList = null;
    private $pokeAPI;
    /**
     * @var string
     */
    private $lastErrorMsg = null;

    /**
     * @return mixed|null - Resource List for Pokemon resource - list of Pokemon names and urls
     */
    private function getResourceList ()
    {
        Log::info('getResourceList:Entry');
        try {
            if (!empty($this->resourceList)) {
                Log::debug('getResourceList: Resource list already created', ['resourceList' => substr(print_r($this->resourceList, true), 0, 250)]);
                return $this->resourceList;
                //           } else if (Cache::has('ResourceList')) {
            } else if ((Cache::has('ResourceList'))) {
                Log::debug('getResourceList: Cache has resource List');
                $this->resourceList = unserialize(Cache::get('ResourceList'));
                Log::debug('getResourceList Resource List returned: ', ['resourceList' => substr(print_r($this->resourceList, true), 0, 250)]);
                return $this->resourceList;
            } else {
                // create resource list of Pokemon from API
                // 1000 Limit good to get all for now
                // Future - inspect returned count and then further request to get all Pokemon names
                $this->resourceList = null;
                Log::debug('getResourceList: Getting resource list from API');
                $resourceList = json_decode($this->pokeAPI->resourceList('pokemon', '1000'));
                // UH what kind of error response is this? - spelling!!
                if ($resourceList == 'An error has occured.') {
                    Log::error('getResourceList: API: An error has occured - great!');
                    // try later
                    $this->lastErrorMsg = "Api-Error";
                } else {
                    if (!empty($resourceList)) {
                        // Got a good resource list - save locally and in Cache for 1 day
                        Log::debug('getResourceList: got a good resource list ');
                        Log::debug('getResourceList Resource List returned: ', ['resourceList' => substr(print_r($resourceList, true), 0, 250)]);
                        $this->resourceList = $resourceList;
                        // cache resource list -
                        $result = Cache::put('ResourceList', serialize($this->resourceList), now()->addMinutes(30));
                        if (!$result) {
                            Log::error('getResourceList: Cache Put failed');
                            $this->lastErrorMsg = "Caching failed";
                        }
                    }
                }
                return $this->resourceList;
            }
        } catch (\Exception $e) {
            // Something broke
            Log::error("getResourceList: An exception occurred: ", ['Exception' => $e->getMessage()]);
            $this->lastErrorMsg = $e->getMessage();
            $this->resourceList = null;
            return null;
        }
    }

    /**
     * PokeDictionary constructor.
     */
    public function __construct()
    {
        // init the API
        Log::info('New PokeDictionary');
        $this->pokeAPI = new PokeApi();
        // load complete PokeMon resource list from API or Cache
        $this->getResourceList();
    }

    /**
     * return dictionary letters of first character for Pokemon names - filters out letters with no Pokemon name
     * @return array|null
     */
    public function dictionary() {
        Log::info('dictionary: entry');
        if (empty($this->resourceList)) {
            // Resource list not yet filled in - try now
            Log::debug('dictionary: ResourceList not available');
            return null;
        }
        // Extract Pokemon names
        $names = array_column($this->resourceList->results, 'name');
        // Extract first letter of Pokemon name - names are all lowercase
        $dictionary = array_map( array($this, 'getFirstLetter'), $names);
        $dictionary = array_unique($dictionary);
        sort($dictionary);
        Log::debug('dictionary: Returning : ', ['dictionary' => $dictionary]);
        return $dictionary;
    }

    protected function getFirstLetter($name) {
        //Log::debug('getFirstLetter: Entry', ['name' => $name]);
        $firstLetter = substr($name, 0, 1 );
        //Log::debug('getFirstLetter: Return : ', ['firstLetter' => $firstLetter]);
        return  $firstLetter;
    }


    /**
     * Return an array of Pokemon names and IDs which match or start with the search string
     * @param string $searchString
     * @return array|null
     */
    public function matchNames(string $searchString) {
        // find names which start with input string
        Log::info('matchNames: search for : ', ['searchString' => $searchString]);
        if (empty($this->resourceList)) {
            Log::debug('matchNames: ResourceList not available');
            return null;
        }
        if (empty($searchString)) {
            Log::debug('matchNames: Empty/too small searchString');
            return null;
        }
        $this->searchString = $searchString;
        $matches = array_filter($this->resourceList->results, array ($this, 'filterNames'));
        if (is_array($matches)  && (count($matches) > 0)) {
            $matches = array_map(array ($this, 'extractIDs'), $matches);
            if (count($matches) > 1) {
                // sort results by name
                usort($matches, array($this, 'pokeSort'));
            }
            Log::debug('matchNames: Returning : ', ['matches' => $matches]);
            return $matches;
        }
        Log::debug('matchNames: No Match');
        return null;
    }

    private $searchString;

    /**
     * @param \stdClass $resource
     * @return bool
     */
    protected function filterNames(\stdClass $resource) {
        // resource is a stdClass with properties $name & $url
        // Check if name starts with or matches $searchString
        // Log::info('filterNames: Entry  : ', ['searchString' => $this->searchString, 'resource' => $resource]);
        if (strlen($this->searchString) < 3) {
            // Short string search - only match start of name
            if (!substr_compare($resource->name, $this->searchString, 0, strlen($this->searchString)))  {
                Log::debug('filterNames: Match found at start of name - Return true');
                return true;
            }
        } else {
            // match search string as sub-string of whole name
            if (stristr($resource->name, $this->searchString)) {
                Log::debug('filterNames: Match found - Return true');
                return true;
            }
        }
        //Log::debug('filterNames: Return false');
        return false;
    }

    /**
     * @param \stdClass $resource
     * @return \stdClass|null
     */
    protected function extractIDs(\stdClass $resource) {
        // extract PokeMon id from URL then return object with properties name & ID
        // URL ends in ../{id}/ where id is numeric
        Log::info('extractIDs: Entry : ', ['resource' => $resource]);
        $matchCount = preg_match('/([0-9]*)\/$/', $resource->url, $matches);
        if ($matchCount > 0) {
            $result = new \stdClass();
            $result->name  =  $resource->name;
            $result->ID = $matches[1];
            Log::debug('extractIDs: ID found: ', ['ID' => $matches[1]]);
            return $result;
        }
        Log::warning('extractIDs: No Id found in URL', ['url' => $resource->url]);
        return null;
    }

    /**
     * @param \stdClass $name1
     * @param \stdClass $name2
     * @return int|\lt
     */
    protected function pokeSort(\stdClass $name1, \stdClass $name2) {
        // sort array of PokeAPI named resource objects (or any object with a name property)
        return strcmp($name1->name, $name2->name);
    }

    /**
     * @return string|null
     */
    public function getLastError() {
        if (!empty($this->lastErrorMsg)) {
            return $this->lastErrorMsg;
            $this->lastErrorMsg = null;
        } else {
            return null;
        }
    }

    /**
     * @param $ID
     * @return mixed|null
     */
    public function getPokemonDetails($ID) {
        Log::info('getPokemonDetails: For ID : ', ['ID' => $ID]);
        $result = json_decode($this->pokeAPI->pokemon($ID));
        if ($result == 'An error has occured.') {
            Log::error('getPokemonDetails: API: An error has occured - great!');
            // try later
            $this->lastErrorMsg = "Api-Error on Pokemon details";
        } else if (!empty($result)) {
            Log::debug('getPokeMonDetails: Details found: ', ['result' => substr(print_r($result, true), 0, 250)]);
            return $result;
        }
        return null;
    }



}
