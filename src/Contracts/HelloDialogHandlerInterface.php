<?php
namespace Czim\HelloDialog\Contracts;

use Czim\HelloDialog\Exceptions\HelloDialogErrorException;
use Czim\HelloDialog\Exceptions\HelloDialogGeneralException;
use Czim\HelloDialog\Enums\ContactType;
use Exception;

interface HelloDialogHandlerInterface
{

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
    public function transactional($to, $subject, $template = null, array $from = null, array $replaces = []);

    /**
     * @param array  $fields
     * @param string $state
     * @return bool
     */
    public function saveContact(array $fields, $state = ContactType::OPT_IN);

    /**
     * @param array $fields
     * @return string|int   ID of generated contact
     * @throws Exception
     */
    public function createContact(array $fields);

    /**
     * Fetches the contents of a template, optionally performing placeholder replaces.
     *
     * @param int   $templateId
     * @param array $replaces
     * @return string
     */
    public function getTemplateContents($templateId, array $replaces = []);

}
