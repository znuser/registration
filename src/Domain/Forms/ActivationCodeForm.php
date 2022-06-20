<?php

namespace ZnUser\Registration\Domain\Forms;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ZnCore\Base\Libs\I18Next\Facades\I18Next;
use ZnCore\Base\Libs\Entity\Interfaces\ValidateEntityByMetadataInterface;
use ZnLib\Web\Symfony4\MicroApp\Interfaces\BuildFormInterface;

class ActivationCodeForm implements ValidateEntityByMetadataInterface, BuildFormInterface
{

    private $code;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('code', new Assert\NotBlank());
    }

    public function buildForm(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('code', TextType::class, [
                'label' => 'Code'
            ])
            ->add('save', SubmitType::class, [
                'label' => I18Next::t('core', 'action.send')
            ]);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code): void
    {
        $this->code = $code;
    }
}
