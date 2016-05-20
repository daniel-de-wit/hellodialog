<?php
namespace Czim\HelloDialog;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Czim\HelloDialog\Exceptions\ConnectionException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

/**
 * Class HelloDialogApi
 *
 * Formerly known as KBApi. To use this outside of laravel,
 * be sure to pass in all constructor parameters
 */
class HelloDialogApi implements HelloDialogApiInterface
{

    /**
     * API Token
     *
     * @var string
     */
    protected $token = null;

    /**
     * API Location
     *
     * @var string
     */
    protected $url = null;

    /**
     * API sub-path / action
     *
     * @var string
     */
    protected $path = null;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Which condition keys are valid
     * @var array
     */
    protected $validConditions = [
        'equals',
        'equals-any',
        'not-equals',
        'greater-than',
        'less-than',
        'contains',
        'not-contains',
        'starts-with',
        'ends-with',
        'before',
        'after',
        'contains-any',
        'contains-all',
        'contains-exactly',
        'not-contains-any',
        'not-contains-all',
    ];

    /**
     * Guzzle client
     *
     * @var null|Client
     */
    protected $client;

    /**
     * @var null|Exceptions\HelloDialogErrorException
     */
    protected $lastError;

    /**
     * Constructor
     *
     * @param string      $path  url path, such as 'transactional'
     * @param string      $token if set, override the config-defined token
     * @param string      $url   base url to the API itself (requires trailing slash)
     * @param null|Client $client
     */
    public function __construct($path, $token = null, $url = null, $client = null)
    {
        $this->path   = $path;
        $this->token  = $token ?: config('hellodialog.token');
        $this->url    = $url ?: config('hellodialog.url');
        $this->client = $client ?: $this->buildGuzzleClient();

        return $this;
    }

    /**
     * @return Client
     */
    protected function buildGuzzleClient()
    {
        $client = app(
            Client::class,
            [
                [
                'base_uri' => $this->url . '/' . ltrim($this->path, '/'),
                ]
            ]
        );

        return $client;
    }

    /**
     * Clears conditions and data
     *
     * @return $this
     */
    public function clear()
    {
        $this->conditions = [];
        $this->data       = [];

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets a key value pair with a given condition
     *
     * @param string $key
     * @param mixed  $value
     * @param string $condition
     * @return $this
     * @throws Exception
     */
    public function condition($key, $value, $condition = 'equals')
    {
        if ( ! in_array($condition, $this->validConditions)) {
            throw new Exception("'{$condition}' is not a valid condition");
        }

        $this->conditions[$key] = [
            'value'     => $value,
            'condition' => $condition,
        ];

        return $this;
    }

    /**
     * Perfoms a request for the PUT method
     *
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function put($id = null)
    {
        return $this->request('PUT', $id);
    }

    /**
     * Perfoms a request for the DELETE method
     *
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        return $this->request('DELETE', $id);
    }

    /**
     * Perfoms a request for the GET method
     *
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function get($id = null)
    {
        return $this->request('GET', $id);
    }

    /**
     * Perfoms a request for the POST method
     *
     * @return mixed
     * @throws Exception
     */
    public function post()
    {
        return $this->request('POST');
    }

    /**
     * Perfoms a request to the API
     *
     * @param string $method
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    protected function request($method, $id = null)
    {
        $this->lastError = null;
        
        $this->checkBeforeRequest();

        try {
            $response = $this->client->request($method, $id ?: null, $this->buildGuzzleOptions($method));

        } catch (ClientException $e) {

            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->handleResponse($response);

        //    $requestUrl .= '&condition[' . $key . ']='
        //                 . $data['condition'] . '&values[' . $key . ']='
        //                 . urlencode($data['value']);
    }

    /**
     * Checks conditions and properties before performing a request
     *
     * @throws Exception
     */
    protected function checkBeforeRequest()
    {
        if (empty($this->path)) {
            throw new Exception("No url specified");
        }

        if (empty($this->token)) {
            throw new Exception("API token is required");
        }
    }

    /**
     * Builds and returns the guzzle options to send with a request
     *
     * @param string $method
     * @return array
     */
    protected function buildGuzzleOptions($method = 'GET')
    {
        $options = [
            'verify' => false, // do not verify SSL certificate
        ];

        // set data in body for PUT, POST or PATCH
        if (in_array($method, ['PATCH','POST', 'PUT'])) {
            $options['body'] = json_encode($this->data);
        }

        // always set request parameters
        $requestParameters = [
            'token' => $this->token,
        ];

        foreach ($this->conditions as $key => $data) {
            $requestParameters[ 'condition[' . $key . ']' ] = $data['condition'];
            $requestParameters[ 'values[' . $key . ']' ]    = $data['value'];
        }

        $options['query'] = $requestParameters;

        return $options;
    }

    /**
     * @param Response $response
     * @return array
     * @throws Exceptions\ConnectionException
     * @throws Exceptions\HelloDialogErrorException
     */
    protected function handleResponse(Response $response)
    {
        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new Exceptions\ConnectionException("Received unexpected status code: {$response->getStatusCode()}");
        }

        $responseArray = json_decode($response->getBody()->getContents(), true);

        if ( ! is_array($responseArray)) {
            throw new Exceptions\ConnectionException("Received unexpected body content, invalid json.");
        }

        // detect error response
        if (strtolower(array_get($responseArray, 'result.status', '')) == 'error') {
            throw new Exceptions\HelloDialogErrorException(
                array_get($responseArray, 'result.message', null),
                array_get($responseArray, 'result.code', 0)
            );
        }

        return $responseArray;
    }

}
