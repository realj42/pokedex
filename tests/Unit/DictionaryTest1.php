<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\PokeDictionary;

class DictionaryTest1 extends TestCase
{
    /**
     * Instantiate the API and get resource List.
     *
     * @return void
     */
    public function test1_1()
    {
        Log::info("Test1_1: Entry");
        $api = new PokeDictionary();
        $this->assertTrue(empty($api->getLastError()));
        Log::info("Test1_1: Succeeded");
    }

    /**
     * Test dictionary list of all names
     */
    public function test1_2() {
        Log::info("Test1_2: Entry");

        // resource list obtained from cache - check logs
        $api = new PokeDictionary();
        $dictionary = $api->dictionary();
        $this->assertTrue(empty($api->getLastError()));
        $this->assertTrue(is_array($dictionary));
        $this->assertTrue(in_array('b', $dictionary));
        Log::info("Test1_2: Succeeded");
    }

    /**
     * Test search for bulbasaur returns one match and correct ID
     */
    public function test1_3() {
        Log::info("Test1_3: Entry");
        $api = new PokeDictionary();
        $result = $api->matchNames('bulbasaur');
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result) === 1);
        $this->assertTrue($result[0]->name === 'bulbasaur');
        $this->assertTrue($result[0]->ID === '1');
        Log::info("Test1_3: Succeeded");
    }

    /**
     * Test search for psyduck returns one match and correct ID
     */
    public function test1_4() {
        Log::info("Test1_4: Entry");
        $api = new PokeDictionary();
        $result = $api->matchNames('psyduck');
        $this->assertTrue(is_array($result));
        $this->assertTrue(count($result) === 1);
        $resultObj = array_pop($result);
        $this->assertTrue($resultObj->name === 'psyduck');
        $this->assertTrue($resultObj->ID === '54');
        Log::info("Test1_4: Succeeded");
    }

    /**
     * Test search for psy42duck returns no matches
     */
    public function test1_5() {
        Log::info("Test1_5: Entry");
        $api = new PokeDictionary();
        $result = $api->matchNames('psy42duck');
        $this->assertNull($result);
        Log::info("Test1_5: Succeeded");
    }

    /**
     * Test search for 'saur' returns multiple matches
     */
    public function test1_6() {
        Log::info("Test1_6: Entry");
        $api = new PokeDictionary();
        $result = $api->matchNames('saur');
        $this->assertTrue(count($result) > 1);
        Log::info("Test1_6: Succeeded");
    }

    /**
     * Test search for 'sa' returns names
     */
    public function test1_7() {
        Log::info("Test1_7: Entry");
        $api = new PokeDictionary();
        $result = $api->matchNames('sa');
        $this->assertTrue(count($result) > 1);
        Log::info("Test1_7: Succeeded");
    }

    /**
     * Test getPokemonDetails for ID 1 returns 'bulbasaur'
     */
    public function test1_8() {
        Log::info('Test1_8: Entry');
        $api = new PokeDictionary();
        $result = $api->getPokemonDetails('1');
        $this->assertNotNull($result);
        $this->assertEquals('bulbasaur', $result->name);
        $this->assertEquals(64, $result->base_experience);
        Log::info("Test1_8: Succeeded");
    }

    /**
     * Test getPokemonDetails for ID 54 returns 'psyduck'
     */
    public function test1_9() {
        Log::info('Test1_9: Entry');
        $api = new PokeDictionary();
        $result = $api->getPokemonDetails(54);
        $this->assertNotNull($result);
        $this->assertEquals('psyduck', $result->name);
        $this->assertEquals(8, $result->height);
        Log::info("Test1_9: Succeeded");
    }

    /**
     * Test getPokemonDetails for ID 98989898 returns null
     */
    public function test1_10() {
        Log::info('Test1_10: Entry');
        $api = new PokeDictionary();
        $result = $api->getPokemonDetails('98989898');
        $this->assertNull($result);
        Log::info("Test1_10: Succeeded");
    }


}
