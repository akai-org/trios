<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IndexTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testIndexWorks()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Trios are simple exercises to test your English skills.');
        });
    }

    public function testCanGoToSolvingScreen()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('Play')
                ->assertPathIs('/solve')
                ->assertSee('Trio')
                ->assertSee('Check');
        });
    }
}
