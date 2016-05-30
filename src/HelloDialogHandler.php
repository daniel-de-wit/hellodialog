<?php
namespace Czim\HelloDialog;

use Czim\HelloDialog\Contracts\HelloDialogApiInterface;
use Czim\HelloDialog\Contracts\HelloDialogHandlerInterface;
use Czim\HelloDialog\Exceptions\HelloDialogErrorException;
use Czim\HelloDialog\Exceptions\HelloDialogGeneralException;
use Czim\Repository\Enums\ContactType;
use Exception;
use Log;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class HelloDialogHandler implements HelloDialogHandlerInterface
{

    /**
     * Logger. If not set, logs using the Log facade.
     *
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @var HelloDialogApiInterface[]
     */
    protected $apiInstances = [];


    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }


    /**
     * @param string     $to
     * @param string     $subject
     * @param int        $template
     * @param null|array $from associative, 'email', 'name' keys
     * @param array      $replaces
     * @return bool
     * @throws HelloDialogErrorException
     * @throws HelloDialogGeneralException
     */
    public function transactional($to, $subject, $template = null, array $from = null, array $replaces = [])
    {
        $from = $from ?: config('hellodialog.sender');

        $template = $template ?: config('hellodialog.default_template');
        $template = $this->normalizeTemplate($template);

        // Build normalized replaces array
        $normalizedReplaces = [];

        if ($replaces) {

            foreach ($replaces as $search => $replace) {
                $normalizedReplaces[] = [
                    'find'    => $search,
                    'replace' => $replace,
                ];
            }
        }

        $data = [
            'to'            => $to,
            'from'          => $from,
            'subject'       => $subject,
            'template'      => [
                'id'       => $template,
                'replaces' => $normalizedReplaces,
            ],
            'tag'           => $subject,
            'force_sending' => true,
        ];

        try {
            $result = $this->getApiInstance('transactional')
                ->data($data)
                ->post();
            
            if ( ! $result) {
                throw new HelloDialogGeneralException('No result given, configuration error?');
            }

            if (config('hellodialog.debug')) {
                $this->log('transactional', 'debug', [
                    'data'   => $data,
                    'result' => $result,
                ]);
            }

            $this->checkForHelloDialogError($result);

        } catch (Exception $e) {

            $this->logException($e);
            return false;
        }

        return array_get($result, 'result.data', []);
    }

    /**
     * @param array  $fields
     * @param string $state
     * @return bool
     */
    public function saveContact(array $fields, $state = ContactType::OPT_IN)
    {
        // Update parameters to include state of contact
        try {
            $fields['_state'] = (string) new ContactType($state);

        } catch (Exception $e) {
            // Contact type was invalid, continue gracefully with optin state
            $fields['_state'] = ContactType::OPT_IN;
            $this->logException($e);
        }

        // Check if contact exists first
        $contact = $this->checkIfEmailExists($fields['email']);

        if ( ! $contact) {
            // E-mail does not yet exist in HelloDialog
            // Let's create the contact in HelloDialog
            try {
                $contactId = $this->createContact($fields);

                if (config('hellodialog.debug')) {
                    $this->log('createContact', [
                        'contactId' => $contactId,
                        'state'     => $state,
                        'data'      => $fields,
                        'new'       => true,
                    ]);
                }

            } catch (Exception $e) {
                $this->logException($e);
                return false;
            }

            return $contactId;
        }

        // E-mail already exists
        // Let's update the contact in HelloDialog
        try {

            try {
                $fields['_state'] = (string) new ContactType($contact['_state']);

            } catch (Exception $e) {
                // Contact type was invalid, continue gracefully with contact state
                $fields['_state'] = ContactType::CONTACT;
            }

            $contact = $this->updateContact($contact['id'], $fields);

            if (config('hellodialog.debug')) {
                $this->log('createContact', 'debug', [
                    'state'   => $state,
                    'data'    => $fields,
                    'contact' => $contact,
                ]);
            }

        } catch (Exception $e) {
            $this->logException($e);
            return false;
        }

        return $contact;
    }

    /**
     * @param array $fields
     * @return string|int   ID of generated contact
     * @throws Exception
     */
    public function createContact(array $fields)
    {
        $result = $this->getApiInstance('contacts')
            ->data($fields)
            ->post();

        $this->checkForHelloDialogError($result);

        if (config('hellodialog.debug')) {
            $this->log('createContact', 'debug', [
                'data'   => $fields,
                'result' => $result,
            ]);
        }

        return array_get($result, 'result.data.id');
    }

    /**
     * @param string|int $contactId
     * @param array      $fields
     * @return string|int  ID of updated contact
     * @throws Exception
     */
    protected function updateContact($contactId, array $fields)
    {
        $result = $this->getApiInstance('contacts')
            ->data($fields)
            ->put($contactId);

        $this->checkForHelloDialogError($result);

        if (config('hellodialog.debug')) {
            $this->log('updateContact', 'debug', [
                'contactId' => $contactId,
                'data'      => $fields,
                'result'    => $result,
            ]);
        }

        return array_get($result, 'result.data.id');
    }

    /**
     * @param string $email
     * @return bool
     */
    protected function checkIfEmailExists($email)
    {
        $existance = $this->getApiInstance('contacts')
            ->condition('email', $email, 'equals')
            ->condition('_state', ContactType::CONTACT, 'equals')
            ->get();

        return $existance ? array_get($existance, '0') : false;
    }

    
    /**
     * @param string $type
     * @return HelloDialogApiInterface
     */
    protected function getApiInstance($type = 'transactional')
    {
        if ( ! isset($this->apiInstances[ $type ])) {
            $this->apiInstances[ $type ] = $this->buildApiInstance($type);
        }
        
        return $this->apiInstances[ $type ];
    }

    /**
     * @param string $type
     * @return HelloDialogApiInterface
     */
    protected function buildApiInstance($type)
    {
        return app(HelloDialogApiInterface::class, [ $type ]);
    }

    /**
     * Normalizes template to template ID
     *
     * @param mixed $template
     * @return int
     */
    protected function normalizeTemplate($template)
    {
        if (is_string($template)) {
            $template = config('hellodialog.templates.' . $template .'.id');

            if ( ! $template) {
                throw new UnexpectedValueException("Could not find template ID by name '{$template}'.");
            }
        }

        return (int) $template;
    }

    /**
     * Checks a result array returned from HelloDialog for an error.
     * Throws exception if found.
     *
     * @param array|mixed $result
     * @throws HelloDialogErrorException
     * @throws HelloDialogGeneralException
     */
    protected function checkForHelloDialogError($result)
    {
        if ( ! is_array($result)) {
            throw new HelloDialogGeneralException('Expected array result from HelloDialog');
        }

        $resultCode = (int) array_get($result, 'result.code', 0);

        if ($resultCode < 200 || $resultCode > 299) {
            throw new HelloDialogErrorException(array_get($result, 'result.message'), $resultCode);
        }
    }

    /**
     * Writes exception information to the log
     *
     * @param Exception $e
     */
    protected function logException(Exception $e)
    {
        $this->log($e->getMessage(), 'error', [ 'exception' => $e ]);
    }

    /**
     * @param       $type
     * @param array $extra
     */
    protected function logActivity($type, array $extra = [])
    {
        $this->log($type, 'debug', $extra);
    }

    /**
     * @param string $message
     * @param string $level
     * @param array  $extra
     */
    protected function log($message, $level = 'debug', array $extra = [])
    {
        if ($this->logger) {
            $this->logger->error($message, $extra);
            return;
        }

        Log::log($level, $message, $extra);
    }
}
