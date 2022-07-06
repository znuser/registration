<?php

namespace ZnUser\Registration\Domain\Services;

use App\Common\Enums\Rbac\ApplicationRoleEnum;
use ZnCore\Validation\Helpers\UnprocessableHelper;
use ZnUser\Rbac\Domain\Enums\Rbac\SystemRoleEnum;
use ZnUser\Registration\Domain\Forms\CreateAccountForm;
use ZnUser\Registration\Domain\Forms\RequestActivationCodeForm;
use ZnUser\Registration\Domain\Interfaces\Services\RegistrationServiceInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use ZnBundle\Notify\Domain\Entities\EmailEntity;
use ZnBundle\Notify\Domain\Entities\SmsEntity;
use ZnBundle\Notify\Domain\Interfaces\Services\EmailServiceInterface;
use ZnBundle\Notify\Domain\Interfaces\Services\SmsServiceInterface;
use ZnUser\Confirm\Domain\Entities\ConfirmEntity;
use ZnUser\Authentication\Domain\Entities\CredentialEntity;
use ZnUser\Confirm\Domain\Enums\ConfirmActionEnum;
use ZnCore\Contract\User\Interfaces\Entities\IdentityEntityInterface;
use ZnUser\Confirm\Domain\Interfaces\Services\ConfirmServiceInterface;
use ZnUser\Authentication\Domain\Interfaces\Services\CredentialServiceInterface;
use ZnLib\Components\Time\Enums\TimeEnum;
use ZnCore\Entity\Exceptions\AlreadyExistsException;
use ZnCore\Entity\Exceptions\NotFoundException;
use ZnLib\I18Next\Facades\I18Next;
use ZnCore\Service\Base\BaseService;
use ZnCore\Validation\Exceptions\UnprocessibleEntityException;
use ZnCore\Validation\Helpers\ValidationHelper;
use ZnCore\EntityManager\Interfaces\EntityManagerInterface;
use ZnUser\Rbac\Domain\Entities\AssignmentEntity;

class RegistrationService extends BaseService implements RegistrationServiceInterface
{

    private $confirmService;
    private $passwordHasher;
    private $emailService;
    private $smsService;
    private $credentialService;

    public function __construct(
        EntityManagerInterface $em,
        ConfirmServiceInterface $confirmService,
        PasswordHasherInterface $passwordHasher,
        EmailServiceInterface $emailService,
        SmsServiceInterface $smsService,
        CredentialServiceInterface $credentialService
    )
    {
        $this->setEntityManager($em);
        $this->confirmService = $confirmService;
        $this->passwordHasher = $passwordHasher;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
        $this->credentialService = $credentialService;
    }

    public function requestActivationCode(RequestActivationCodeForm $requestActivationCodeForm)
    {
        ValidationHelper::validateEntity($requestActivationCodeForm);

        $this->checkCredentialExists($requestActivationCodeForm);

        /*if ($requestActivationCodeForm->getEmail()) {
            try {
                $credentialEntity = $this->credentialService->findOneByCredentialValue($requestActivationCodeForm->getEmail());
                $e = new UnprocessibleEntityException();
                $e->add('email', I18Next::t('user.registration', 'registration.message.credential_already_exists'));
                throw $e;
            } catch (NotFoundException $e) {
            }
        }
        if ($requestActivationCodeForm->getPhone()) {
            try {
                $credentialEntity = $this->credentialService->findOneByCredentialValue($requestActivationCodeForm->getPhone());
                $e = new UnprocessibleEntityException();
                $e->add('phone', I18Next::t('user.registration', 'registration.message.credential_already_exists'));
                throw $e;
            } catch (NotFoundException $e) {
            }
        }*/

        $confirmEntity = new ConfirmEntity;
        $confirmEntity->setLogin($requestActivationCodeForm->getEmail());
        $confirmEntity->setAction(ConfirmActionEnum::REGISTRATION);
        $confirmEntity->setExpire(time() + TimeEnum::SECOND_PER_MINUTE * 5);

        /*try {
            $this->confirmService->add($confirmEntity);
        } catch (AlreadyExistsException $e) {
            $message = I18Next::t('summary', 'attempt.message.attempts_have_been_exhausted_time', ['seconds' => $e->getMessage()]);
            throw new AlreadyExistsException($message);
        }*/

        try {
            $this->confirmService->add($confirmEntity);
            if ($requestActivationCodeForm->getEmail()) {
                $emailEntity = new EmailEntity();
                $emailEntity->setTo($requestActivationCodeForm->getEmail());
                $subject = I18Next::t('user.registration', 'registration.notify.activation_code.subject');
                $emailEntity->setSubject($subject);
                $content = I18Next::t('user.registration', 'registration.notify.activation_code.content', [
                    'code' => $confirmEntity->getCode(),
                ]);
                $emailEntity->setBody($content);
                $this->emailService->push($emailEntity);
            } elseif ($requestActivationCodeForm->getPhone()) {
                $smsEntity = new SmsEntity();
                $smsEntity->setPhone($requestActivationCodeForm->getPhone());
                $content = I18Next::t('user.registration', 'registration.notify.activation_code.shortContent', [
                    'code' => $confirmEntity->getCode(),
                ]);
                $smsEntity->setMessage($content);
                $this->smsService->push($smsEntity);
            }
            //$this->confirmService->sendConfirmBySms($confirmEntity, ['user.registration', 'registration.activate_account_sms']);
        } catch (AlreadyExistsException $e) {
            $message = I18Next::t('user.registration', 'registration.user_already_exists_but_not_activation_time_left', ['timeLeft' => $e->getMessage()]);
            throw new AlreadyExistsException($message);
        }

        /*$this->notifyService->sendNotifyByTypeName(UserRegistrationNotifyTypeEnum::REGISTRATION, $credentialEntity->getIdentityId(), [
            'code' => $confirmEntity->getCode(),
        ]);*/


        /*$confirmEntity = new ConfirmEntity();

        $this->confirmService->add($confirmEntity);
        dd($registrationForm);*/
    }

    protected function checkCredentialExists($registrationForm) {
        $hasByEmail = $registrationForm->getEmail() && $this->credentialService->hasByCredentialValue($registrationForm->getEmail());
        $hasByPhone = $registrationForm->getPhone() && $this->credentialService->hasByCredentialValue($registrationForm->getPhone());

        if ($hasByEmail) {
            $message = I18Next::t('user.registration', 'registration.user_already_exists_and_activated');
            UnprocessableHelper::throwItems(['email' => $message]);
        }

        if ($hasByPhone) {
            $message = I18Next::t('user.registration', 'registration.user_already_exists_and_activated');
            UnprocessableHelper::throwItems(['phone' => $message]);
        }
    }

    public function createAccount(CreateAccountForm $registrationForm): IdentityEntityInterface
    {
        $this->checkCredentialExists($registrationForm);

        ValidationHelper::validateEntity($registrationForm);
        try {
            $isVerify = $this->confirmService->isVerify($registrationForm->getEmail(), ConfirmActionEnum::REGISTRATION, $registrationForm->getCode());
            if (!$isVerify) {
                $message = I18Next::t('user.registration', 'registration.invalid_activation_code');
                UnprocessableHelper::throwItems(['activation_code' => $message]);
            }
        } catch (NotFoundException $e) {
            $message = I18Next::t('user.registration', 'registration.temp_user_not_found');
            UnprocessableHelper::throwItems(['phone' => $message]);
        }
        /** @var IdentityEntityInterface $identityEntity */
        $identityEntity = $this->getEntityManager()->createEntity(IdentityEntityInterface::class);
        $identityEntity->setUsername($registrationForm->getEmail());
        $this->getEntityManager()->persist($identityEntity);

        $assignmentEntity = new AssignmentEntity();
        $assignmentEntity->setIdentityId($identityEntity->getId());
        $assignmentEntity->setItemName(SystemRoleEnum::USER);
        $this->getEntityManager()->persist($assignmentEntity);

        $passwordHash = $this->passwordHasher->hash($registrationForm->getPassword());

        if ($registrationForm->getEmail()) {
            $credentialEntity = new CredentialEntity();
            $credentialEntity->setIdentityId($identityEntity->getId());
            $credentialEntity->setType('email');
            $credentialEntity->setCredential($registrationForm->getEmail());
            $credentialEntity->setValidation($passwordHash);
            $this->getEntityManager()->persist($credentialEntity);
        }
        if ($registrationForm->getPhone()) {
            $credentialEntity = new CredentialEntity();
            $credentialEntity->setIdentityId($identityEntity->getId());
            $credentialEntity->setType('phone');
            $credentialEntity->setCredential($registrationForm->getPhone());
            $credentialEntity->setValidation($passwordHash);
            $this->getEntityManager()->persist($credentialEntity);
        }

        return $identityEntity;
    }
}
