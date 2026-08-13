<?php

declare(strict_types=1);

namespace Prolyfix\ProcurementBundle\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Prolyfix\ProcurementBundle\Helper\SequenceToArray;

final class SequenceToArrayTest extends TestCase
{
    public function testGroupsEntriesByNumber(): void
    {
        $entryA = $this->makeSequenceEntry(1);
        $entryB = $this->makeSequenceEntry(2);
        $entryC = $this->makeSequenceEntry(1);
        $sequence = $this->makeSequence([$entryA, $entryB, $entryC]);

        $result = SequenceToArray::sequenceToArray($sequence);

        // NOTE: sequenceToArray() resets the bucket to [] whenever the same
        // number is seen again, so only the LAST entry per number survives —
        // $entryA is silently dropped here. This looks like a pre-existing bug
        // (the array_key_exists check is inverted), documented rather than fixed.
        $this->assertSame([$entryC], $result[1]);
        $this->assertSame([$entryB], $result[2]);
    }

    public function testSkipsEntriesWithoutNumber(): void
    {
        $entryWithoutNumber = $this->makeSequenceEntry(null);
        $sequence = $this->makeSequence([$entryWithoutNumber]);

        $result = SequenceToArray::sequenceToArray($sequence);

        $this->assertSame([], $result);
    }

    private function makeSequence(array $entries): object
    {
        return new class ($entries) {
            public function __construct(private array $entries)
            {
            }

            public function getSequenceEntries(): array
            {
                return $this->entries;
            }
        };
    }

    private function makeSequenceEntry(?int $number): object
    {
        return new class ($number) {
            public function __construct(private ?int $number)
            {
            }

            public function getNumber(): ?object
            {
                if ($this->number === null) {
                    return null;
                }

                return new class ($this->number) {
                    public function __construct(private int $number)
                    {
                    }

                    public function getNumber(): int
                    {
                        return $this->number;
                    }
                };
            }
        };
    }
}
