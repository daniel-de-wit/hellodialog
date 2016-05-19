<?php
namespace Czim\HelloDialog\Test;

class HelloDialogApiTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_an_array_for_received_json_on_a_get_request()
    {
        $api = $this->makeHelloDialogApi();

        $this->server->enqueue([
            $this->makeJsonResponse([ 'test' => 'value' ])
        ]);

        $result = $api->get();

        $this->assertEquals([ 'test' => 'value' ], $result);
    }
    

    /**
     * @test
     * @expectedException \Czim\HelloDialog\Exceptions\ConnectionException
     */
    function it_throws_an_exception_if_it_does_not_receive_json()
    {
        $api = $this->makeHelloDialogApi();

        $this->server->enqueue([
            $this->makeResponse('no json!')
        ]);

        $api->get();
    }

    /**
     * @test
     * @expectedException \Czim\HelloDialog\Exceptions\ConnectionException
     */
    function it_throws_an_exception_if_it_receives_a_non_200_status_code()
    {
        $api = $this->makeHelloDialogApi();

        $this->server->enqueue([
            $this->makeResponse(json_encode([ 'test' => 'json' ]), 400)
        ]);

        $api->get();
    }

}
