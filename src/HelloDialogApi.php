<?php
namespace Czim\HelloDialog;

use Exception;

/**
 * Class HelloDialogApi
 *
 * Formerly known as KBApi.
 * To use this outside of laravel, be sure to pass in all constructor parameters
 */
class HelloDialogApi
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
     * Constructor
     *
     * @param string $path   url path, such as 'transactional'
     * @param string $token  if set, override the config-defined token
     * @param string $url    base url to the API itself (requires trailing slash)
     */
    public function __construct($path, $token = null, $url = null)
    {
        $this->path = $path;


        if (is_null($token)) {
            $token = config('hellodialog.token');
        }
        $this->token = $token;


        if (is_null($url)) {
            $url = config('hellodialog.url');
        }
        $this->url = $url;

        return $this;
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
     * Perfoms a cURL request to the API
     *
     * @param string $method
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    protected function request($method, $id = null)
    {
        if (is_null($this->path)) {
            throw new Exception("No url specified");
        }

        if (is_null($this->token)) {
            throw new Exception("API token is required");
        }

        $curl = curl_init();

        $requestUrl = $this->url . $this->path;

        if ( ! is_null($id)) {
            $requestUrl .= '/' . $id;
        }

        $requestUrl .= '?token=' . $this->token;

        foreach ($this->conditions as $key => $data) {

            $requestUrl .= '&condition[' . $key . ']='
                         . $data['condition'] . '&values[' . $key . ']='
                         . urlencode($data['value']);
        }

        if ( ! empty($this->data)) {

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->data));
        }

        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

}
