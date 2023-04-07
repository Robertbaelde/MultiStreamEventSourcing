<?php

namespace EventStream;

class DomainIdentifiers
{
    /**
     * @var DomainIdentifier[]
     */
    private array $domainIdentifiers;

    public function __construct(
        DomainIdentifier ...$domainIdentifiers
    )
    {
        $this->domainIdentifiers = $domainIdentifiers;
    }

    public function contains(DomainIdentifier $domainIdentifier): bool
    {
        foreach ($this->domainIdentifiers as $domainIdentifierInList) {
            if ($domainIdentifierInList instanceof $domainIdentifier && $domainIdentifierInList->equals($domainIdentifier)) {
                return true;
            }
        }

        return false;
    }
}
