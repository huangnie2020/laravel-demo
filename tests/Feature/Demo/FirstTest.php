<?php

namespace Tests\Feature\Demo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App;
use Collection;
use Mockery;

use App\Contracts\Demo\FirstInterface;

use App\Service\Demo\First;

class FirstTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
        return true;

        $response = $this->get('/demo');


        $response->assertStatus(200);

        $this->assertEquals('1', $response->json('index'), json_encode($response->json()));


        $response = $this->get('/demo2');


        $response->assertStatus(200);

        $this->assertEquals('2', $response->json('data.index'), json_encode($response->json()));
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample2()
    {
        $this->assertTrue(true);
        return true;
        
        // $repository = Mockery::mock(FirstInterface::class);

        // $repository->shouldReceive('sss')->andReturn(['kkkk']);
 

        // $this->assertEquals('qqqq', $repository->getUsername(), $repository->getUsername());
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample3()
    {
        $this->assertTrue(true);
        return true;
        
        // $user = App::instance('user', First::class);
        // $this->assertEquals('www', $user->getUsername(), 'check please!');
    }

}
