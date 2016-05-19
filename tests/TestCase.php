<?php
namespace Czim\HelloDialog\Test;

use Czim\HelloDialog\HelloDialogApi;
use Guzzle\Http\Message\Response;
use Guzzle\Tests\Http\Server;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * @var Server
     */
    protected $server;

    /**
     * @param \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->server = new Server();
        $this->server->start();

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('hellodialog', [
            'url'   => $this->server->getUrl(),
            'token' => 'abcdef0123456789',
            'sender' => [
                'email' => 'no-reply@test-hellodialog.com',
                'name'  => 'Test',
            ],
            'default_template' => 'transactional',
            'templates' => [
                'transactional' => [
                    'id' => 1,
                ],
            ],
            'queue' => false,
        ]);
    }

    /**
     * @param null|string $path
     * @return HelloDialogApi
     */
    protected function makeHelloDialogApi($path = 'transactional')
    {
        return new HelloDialogApi($path);
    }

    /**
     * @param mixed $content
     * @param int   $statusCode
     * @param array $headers
     * @return Response
     */
    protected function makeResponse(
        $content,
        $statusCode = 200,
        $headers = [ 'Content-Type' => 'application/json' ]
    ) {
        return new Response($statusCode, $headers, $content);
    }

    /**
     * @param string $content
     * @return Response
     */
    protected function makeJsonResponse($content)
    {
        return $this->makeResponse(json_encode($content));
    }

    /**
     * @param int         $code
     * @param null|string $message
     * @return Response
     */
    protected function makeErrorResponse($code, $message = null)
    {
        return $this->makeJsonResponse([
            'result' => [
                'status'  => 'ERROR',
                'code'    => $code,
                'message' => $message,
            ]
        ]);
    }

}
