<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MiguelAlcaino\MindbodyPaymentsBundle\Repository\CustomSettingRepository")
 */
class CustomSetting
{
    public const FORM_TYPE_TEXT     = 'text';
    public const FORM_TYPE_TEXTAREA = 'textarea';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="json")
     */
    private $value = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $formType = self::FORM_TYPE_TEXT;

    /**
     * @ORM\Column(type="integer", options={"DEFAULT" = 1})
     */
    private $order_position = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(array $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getFormType(): ?string
    {
        return $this->formType;
    }

    public function setFormType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
    }

    public function getOrderPosition(): ?int
    {
        return $this->order_position;
    }

    public function setOrderPosition(int $order_position): self
    {
        $this->order_position = $order_position;

        return $this;
    }
}
