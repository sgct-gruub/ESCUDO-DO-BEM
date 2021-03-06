<?php

namespace Jetimob\Juno\Lib\Http\Account;

use Jetimob\Juno\Lib\Http\Method;
use Jetimob\Juno\Lib\Model\Address;
use Jetimob\Juno\Lib\Model\BankAccount;
use Jetimob\Juno\Lib\Model\LegalRepresentative;

/**
 * Class AccountCreationRequest
 * @package Jetimob\Juno\Lib\Http\Account
 * @see https://dev.juno.com.br/api/v2#operation/createDigitalAccount
 */
class AccountCreationRequest extends AccountRequest
{
    /**
     * @var string PAYMENT_ACCOUNT_TYPE
     * - A fully functional payment digital account
     * - All features available
     */
    public const PAYMENT_ACCOUNT_TYPE = 'PAYMENT';

    /**
     * @var string RECEIVING_ACCOUNT_TYPE
     * - A specific digital account only used for receiving
     * - Just receiving features available
     */
    public const RECEIVING_ACCOUNT_TYPE = 'RECEIVING';

    /** @var string $companyType MANDATORY FOR COMPANIES */
    public string $companyType;

    /** @var string $name [0 .. 80] chars */
    public string $name;

    /** @var string CPF 11 chars || CNPJ 14 chars */
    public string $document;

    /** @var string $email [0 .. 80] chars */
    public string $email;

    /** @var string $phone [10 .. 16] chars */
    public string $phone;

    /** @var int $businessArea business area id */
    public int $businessArea;

    /** @var string $tradingName [0 .. 80] chars */
    public string $tradingName;

    /** @var string $birthDate MANDATORY FOR INDIVIDUALS <date> YYYY-MM-DD */
    public string $birthDate;

    public Address $address;

    public BankAccount $bankAccount;

    /** @var LegalRepresentative $legalRepresentative MANDATORY FOR COMPANIES */
    public LegalRepresentative $legalRepresentative;

    public string $type = self::PAYMENT_ACCOUNT_TYPE;

    /** @var string $linesOfBusiness [0 .. 100 chars] free description */
    public string $linesOfBusiness;

    // the bool default values are specified in Juno's documentation
    // all bool options defined below are marked as ADVANCED and should require additional permissions

    // any value different than false will send the param with the request. Use it ONLY if your is authorized to do so.

    /** @var bool $emailOptOut enables transparent checkout */
    public bool $emailOptOut;

    /** @var bool boolean  Define se as transfer??ncias da conta ser??o feitas automaticamente.
     * Caso haja saldo na conta digital em quest??o, a transfer??ncia ser?? feita todos os dias.
     * Requer permiss??o avan??ada.
     */
    public bool $autoTransfer = false;

    /** @var bool Define se o atributo name poder?? ou n??o receber o nome social. V??lido apenas para PF. */
    public bool $socialName = false;

    /** @var float Renda mensal ou receita. Obrigat??rio para PF e PJ. */
    public float $monthlyIncomeOrRevenue;

    /** @var string Campo destinado ao CNAE(Classifica????o Nacional de Atividades Econ??micas) da empresa.
     * Obrigat??rio para PJ.
     */
    public string $cnae;

    /** @var string Data de abertura da empresa. Obrigat??rio para PJ. */
    public string $establishmentDate;

    /** @var bool Define se o cadastro pertence a uma pessoa politicamente exposta. */
    public bool $pep = false;

    /** @var LegalRepresentative[] Quadro societ??rio da empresa. Obrigat??rio para contas PJ de companyType SA e LTDA */
    public ?array $companyMembers;

    /** @var array $bodySchema defines the body schema common for both types of account */
    protected array $bodySchema = [
        'type',
        'name',
        'document',
        'email',
        'birthDate',
        'phone',
        'businessArea',
        'companyType',
        'tradingName',
        'legalRepresentative',
        'address',
        'bankAccount',
        'emailOptOut',
        'autoTransfer',
        'socialName',
        'monthlyIncomeOrRevenue',
        'cnae',
        'establishmentDate',
        'pep',
        'companyMembers',
    ];

    public function getBodySchema(): array
    {
        $schema = [...$this->bodySchema];
        $setIfModified = function ($advancedOpt) use (&$schema) {
            if (isset($this->{$advancedOpt})) {
                $schema[] = $advancedOpt;
            }
        };

        $setIfModified('emailOptOut');
        $setIfModified('autoTransfer');

        if ($this->type === self::PAYMENT_ACCOUNT_TYPE) {
            return [...$this->bodySchema, 'linesOfBusiness', 'companyMembers'];
        }

        return $this->bodySchema;
    }

    /**
     * @inheritDoc
     */
    protected function method(): string
    {
        return Method::POST;
    }
}
