<?php

namespace NodaSoft\Operation\Params;

use NodaSoft\Request\Request;

class NotifyComplaintNewParams implements Params
{
    /** @var ?int */
    private $resellerId = null;

    /** @var ?int */
    private $clientId = null;

    /** @var ?int */
    private $creatorId = null;

    /** @var ?int */
    private $expertId = null;

    /** @var ?int */
    private $notificationType = null;

    /** @var ?int */
    private $complaintId = null;

    /** @var ?string */
    private $complaintNumber = null;

    /** @var ?int */
    private $consumptionId = null;

    /** @var ?string */
    private $consumptionNumber = null;

    /** @var ?string */
    private $agreementNumber = null;

    /** @var ?string */
    private $date = null;

    public function setRequest(Request $request): void
    {
        foreach ($this as $key => $value) {
            $setter = 'set' . $key;
            if (method_exists($this, $setter)) {
                $this->$setter($request->get($key));
            }
        }
    }

    public function isValid(): bool
    {
        if (empty($this->resellerId)) {
            return false;
        }

        if (empty($this->notificationType)) {
            return false;
        }

        if (empty($this->creatorId)) {
            return false;
        }

        if (empty($this->expertId)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    public function getResellerId(): ?int
    {
        return $this->resellerId;
    }

    public function setResellerId(int $resellerId): void
    {
        $this->resellerId = $resellerId;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getCreatorId(): ?int
    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): void
    {
        $this->creatorId = $creatorId;
    }

    public function getExpertId(): ?int
    {
        return $this->expertId;
    }

    public function setExpertId(int $expertId): void
    {
        $this->expertId = $expertId;
    }

    public function getNotificationType(): ?int
    {
        return $this->notificationType;
    }

    public function setNotificationType(int $notificationType): void
    {
        $this->notificationType = $notificationType;
    }

    public function getComplaintId(): ?int
    {
        return $this->complaintId;
    }

    public function setComplaintId(int $complaintId): void
    {
        $this->complaintId = $complaintId;
    }

    public function getComplaintNumber(): ?string
    {
        return $this->complaintNumber;
    }

    public function setComplaintNumber(string $complaintNumber): void
    {
        $this->complaintNumber = $complaintNumber;
    }

    public function getConsumptionId(): ?int
    {
        return $this->consumptionId;
    }

    public function setConsumptionId(int $consumptionId): void
    {
        $this->consumptionId = $consumptionId;
    }

    public function getConsumptionNumber(): ?string
    {
        return $this->consumptionNumber;
    }

    public function setConsumptionNumber(string $consumptionNumber): void
    {
        $this->consumptionNumber = $consumptionNumber;
    }

    public function getAgreementNumber(): ?string
    {
        return $this->agreementNumber;
    }

    public function setAgreementNumber(string $agreementNumber): void
    {
        $this->agreementNumber = $agreementNumber;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}
