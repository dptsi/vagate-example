<?php

namespace App;
class VaGate
{
    private const ENDPOINT = '';
    private const REUSE_ENDPOINT = '';

    private $endPoint;
    private $unitId;
    private $amuKey;

    public function __construct(int $unitId, string $amuKey, bool $isReuse = false)
    {
        $this->endPoint = $isReuse ? self::REUSE_ENDPOINT : self::ENDPOINT;
        $this->unitId = $unitId;
        $this->amuKey = $amuKey;
    }

    /**
     * @return string
     */
    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    /**
     * @return int
     */
    public function getUnitId(): int
    {
        return $this->unitId;
    }

    /**
     * @return string
     */
    public function getAmuKey(): string
    {
        return $this->amuKey;
    }

    /**
     * @param int $unitId
     * @return bool
     */
    public function unitIdEqualTo(int $unitId): bool
    {
        return $this->unitId === $unitId;
    }

    /**
     * @param string $amuKey
     * @return bool
     */
    public function amuKeyEqualTo(string $amuKey): bool
    {
        return $this->amuKey === $amuKey;
    }
}
