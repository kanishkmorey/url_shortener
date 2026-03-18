<?php

namespace App\Services;

class SnowflakeService
{
    private int $machineId;

    private int $sequence = 0;

    private int $lastTimestamp = -1;

    private int $epoch = 1704067200000; // Jan 1 2024

    private int $machineIdBits = 10;

    private int $sequenceBits = 12;

    private int $machineIdShift;

    private int $timestampShift;

    private int $sequenceMask;

    public function __construct(int $machineId)
    {
        $maxMachineId = -1 ^ (-1 << $this->machineIdBits);

        if ($machineId > $maxMachineId || $machineId < 0) {
            throw new \InvalidArgumentException("Machine ID must be between 0 and $maxMachineId");
        }

        $this->machineId = $machineId;

        $this->machineIdShift = $this->sequenceBits;
        $this->timestampShift = $this->sequenceBits + $this->machineIdBits;
        $this->sequenceMask = -1 ^ (-1 << $this->sequenceBits);
    }

    public function nextId(): int
    {
        $timestamp = $this->currentTime();

        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException('Clock moved backwards.');
        }

        if ($timestamp === $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;

            if ($this->sequence === 0) {
                $timestamp = $this->waitNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        return
            (($timestamp - $this->epoch) << $this->timestampShift) |
            ($this->machineId << $this->machineIdShift) |
            $this->sequence;
    }

    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->currentTime();

        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->currentTime();
        }

        return $timestamp;
    }

    private function currentTime(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
